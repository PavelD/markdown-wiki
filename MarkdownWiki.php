<?php
/*
 * Markdown extension converting markdown text to media wiki syntax to be able to parse it
 * with MediaWiki Parser.php
 */

namespace paveld\markdownwiki;

class MarkdownWiki extends \cebe\markdown\MarkdownExtra {

    use block\TemplateTrait {
        // Check Hr before checking lists
        identifyTemplate as protected identifyAAATemplate;
        consumeTemplate as protected consumeAAATemplate;
    }

    protected function renderLink($block) {
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

        $external = (bool)preg_match( '#^[a-z]+://#i', $block['url'] );
        if($external) {
            return '['.$block['url'].' '.$this->renderAbsy($block['text']).']';
        } else {
            return '[['.$block['url'].'|'.$this->renderAbsy($block['text']).']]';
        }
    }

    protected function renderTable($block) {
        $head = '';
        $body = '';
        $cols = $block['cols'];
        $first = true;
        foreach($block['rows'] as $row) {
            $cellTag = $first ? 'th' : 'td';
            $tds = '';
            foreach ($row as $c => $cell) {
                $align = empty($cols[$c]) ? '' : 'style="text-align:' . $cols[$c] . '" | ';
                $tds.= ($first ? "! " : "| ").$align.trim($this->renderAbsy($cell))."\n";
            }
            if ($first) {
                $head .= $tds;
            } else {
                $body .= "|-\n".$tds;
            }
            $first = false;
        }

        return $this->composeTable($head, $body);
    }

    protected function composeTable($head, $body) {
        return "{|\n".$head.$body."|}\n";
    }
}
