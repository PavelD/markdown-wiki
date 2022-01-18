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

    protected function consumeParagraph($lines, $current)
    {
        $mylines = array_map('trim', $lines);
        return parent::consumeParagraph($mylines, $current);
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
            return '[' . $block['url'] . ' ' . $this->renderAbsy($block['text']) . ']';
        } else {
            return '[[' . $block['url'] . '|' . $this->renderAbsy($block['text']) . ']]';
        }
    }

    protected function renderParagraph($block)
    {
        return "\n\n" . $this->renderAbsy($block['content']) . "\n\n";
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

}
