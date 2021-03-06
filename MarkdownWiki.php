<?php
/**
 * Markdown extension converting markdown text to media wiki syntax to be able to parse it
 * with MediaWiki Parser.php
 *
 * @copyright Copyright (c) 2022 Pavel Dobes
 * @license https://github.com/PavelD/markdown-wiki/blob/main/LICENSE
 * @link https://github.com/PavelD/markdown-wiki#readme
 */

namespace paveld\markdownwiki;

use cebe\markdown\MarkdownExtra;

class MarkdownWiki extends MarkdownExtra
{

    protected $_disabledTypes = [];
    protected $_filteredBlockTypes = null;
    protected $_filteredInlineTypes = null;

    use block\TemplateTrait {
        // Check Hr before checking lists
        identifyTemplate as protected identifyAAATemplate;
        consumeTemplate as protected consumeAAATemplate;
    }

    use inline\MagicWordTrait;

    /**
     * trim output
     * remove multiple empty limes
     */
    public function parse($text)
    {
        $text = parent::parse($text);
        $text = trim($text);
        $text = preg_replace("/\r?\n\r?\n+/s", "\n\n", $text);

        return $text;
    }

    public function disableParsingRule($rule)
    {
        $this->_disabledTypes[$rule] = true;
        return $this;
    }

    protected function blockTypes()
    {
        if ($this->_filteredBlockTypes == null) {
            $this->_filteredBlockTypes = array_filter(array_map(function ($rule) {
                return array_key_exists($rule, $this->_disabledTypes) ? false : $rule;
            }, parent::blockTypes()));
            sort($this->_filteredBlockTypes);
        }
        return $this->_filteredBlockTypes;
    }

    protected function inlineMarkers()
    {
        if ($this->_filteredInlineTypes === null) {
            $this->_filteredInlineTypes = array_filter(array_map(function ($rule) {
                return array_key_exists($rule, $this->_disabledTypes) ? false : $rule;
            }, parent::inlineMarkers()));
        }
        return $this->_filteredInlineTypes;
    }

    protected function consumeHeadline($lines, $current)
    {
        if ($lines[$current][0] !== '#') {
            return parent::consumeHeadline($lines, $current);
        }
        // ATX headline
        $level = 1;
        while (isset($lines[$current][$level]) && $lines[$current][$level] === '#') {
            $level++;
        }
        $block = [
            'headline',
            'content' => $this->parseInline(trim(trim($lines[$current], "#"))),
            'level' => $level,
        ];
        return [$block, $current];
    }

    protected function consumeHtml($lines, $current)
    {
        $consumed = parent::consumeHtml($lines, $current);
        if (substr($consumed[0]['content'], 0, 5) === '<pre>') {
            $consumed[0][0] = 'pre';
        }
        return $consumed;
    }

    protected function consumeParagraph($lines, $current)
    {
        $content = [];
        for ($i = $current, $count = count($lines); $i < $count; $i++) {
            $line = $lines[$i];

            // a list may break a paragraph when it is inside of a list - ol
            if (isset($this->context[1]) && $this->context[1] === 'list' && !ctype_alpha($line[0]) &&
                $this->identifyOl($line) && !$this->isDisabledParsionRule('ol')) {
                break;
            }
            // a list may break a paragraph when it is inside of a list - ul
            if (isset($this->context[1]) && $this->context[1] === 'list' && !ctype_alpha($line[0]) &&
                $this->identifyUl($line) && !$this->isDisabledParsionRule('ul')) {
                break;
            }
            if ($line === '' || ltrim($line) === '') {
                break;
            }
            if ($this->identifyHeadline($line, $lines, $i) && (
                !$this->isDisabledParsionRule('headline'))) {
                break;
            } elseif ($line[0] === "\t" || $line[0] === " " && strncmp($line, '    ', 4) === 0) {
                // possible beginning of a code block
                // but check for continued inline HTML
                // e.g. <img src="file.jpg"
                //           alt="some alt aligned with src attribute" title="some text" />
                if (preg_match('~<\w+([^>]+)$~s', implode("\n", $content))) {
                    $content[] = ltrim($line);
                } else {
                    break;
                }
            } else {
                $content[] = ltrim($line);
            }
        }
        $block = [
            'paragraph',
            'content' => $this->parseInline(implode("\n", $content)),
        ];
        return [$block, --$i];
    }

    public function isDisabledParsionRule($rule)
    {
        return array_key_exists($rule, $this->_disabledTypes);
    }

    protected function renderCode($block)
    {
        $lang = '';
        if (isset($block['attributes'])) {
            $attribute = trim(substr($block['attributes'], 1));
            if (!empty($attribute)) {
                $lang = ' lang="' . $attribute . '"';
            }
        }

        return "<syntaxhighlight{$lang}>{$block['content']}\n</syntaxhighlight>\n\n";

    }

    protected function renderEmail($block)
    {
        $email = htmlspecialchars($block[1], ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8');
        return "[mailto:$email $email]";
    }

    protected function renderHeadline($block)
    {
        parent::renderHeadline($block);
        $tag = substr(str_repeat("=", $block['level'] + 1), 0, 6);
        $prefix = '';
        for ($i = 5; $i < $block['level']; $i++) {
            $prefix .= '#';
        }
        if ($prefix != '') {
            $prefix .= ' ';
        }

        return "\n\n$tag " . $prefix . $this->renderAbsy($block['content']) . " $tag\n\n";
    }

    protected function renderHr($block)
    {
        return "\n\n----\n\n";
    }

    protected function renderLink($block)
    {
        if (isset($block['refkey'])) {
            if (($ref = $this->lookupReference($block['refkey'])) !== false) {
                $block = array_merge($block, $ref);
            } else {
                if (strncmp($block['orig'], '[', 1) === 0) {
                    return '[' . $this->renderAbsy($this->parseInline(substr($block['orig'], 1)));
                }
                return $block['orig'];
            }
        }

        $external = (bool)preg_match('#^[a-z]+://#i', $block['url']);
        if ($external) {
            $url = htmlspecialchars($block['url'], ENT_COMPAT | ENT_HTML401, 'UTF-8');
            return '[' . $url . ' ' . $this->renderAbsy($block['text']) . ']';
        } else {
            return '[[' . $block['url'] . '|' . $this->renderAbsy($block['text']) . ']]';
        }
    }

    protected function renderParagraph($block)
    {
        return "\n\n" . $this->renderAbsy($block['content']) . "\n\n";
    }

    protected function renderPre($block)
    {
        $content = substr($block['content'], 5, -6);
        $content = htmlspecialchars($content, ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $lines = explode("\n", $content);
        if ($lines[0] == "") {
            array_shift($lines);
        }
        if (end($lines) == "") {
            array_pop($lines);
        }
        foreach ($lines as $k => $v) {
            $lines[$k] = ' ' . $v;
        }

        return "\n\n" . implode("\n", $lines) . "\n\n";

    }

    protected function renderTable($block)
    {
        $head = '';
        $body = '';
        $cols = $block['cols'];
        $first = true;
        foreach ($block['rows'] as $row) {
            $tds = '';
            foreach ($row as $c => $cell) {
                $align = empty($cols[$c]) ? '' : 'style="text-align:' . $cols[$c] . '" | ';
                $tds .= ($first ? "! " : "| ") . $align . trim($this->renderAbsy($cell)) . "\n";
            }
            if ($first) {
                $head .= $tds;
            } else {
                $body .= "|-\n" . $tds;
            }
            $first = false;
        }

        return $this->composeTable($head, $body);
    }

    protected function composeTable($head, $body)
    {
        return "{|\n" . $head . $body . "|}\n\n";
    }

    protected function renderUrl($block)
    {
        $url = htmlspecialchars($block[1], ENT_COMPAT | ENT_HTML401, 'UTF-8');
        $decodedUrl = urldecode($block[1]);
        $secureUrlText = preg_match('//u', $decodedUrl) ? $decodedUrl : $block[1];
        $text = htmlspecialchars($secureUrlText, ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8');
        return "[$url $text]";
    }

}
