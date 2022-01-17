<?php
/**
 * @copyright Copyright (c) 2022 Pavel Dobes
 * @license https://github.com/PavelD/markdown-wiki/blob/main/LICENSE
 * @link https://github.com/PavelD/markdown-wiki#readme
 */

namespace paveld\markdownwiki\block;

/**
 * Adds the template blocks
 */
trait TemplateTrait
{

    /**
     * identify a line as the beginning of a template block.
     *
     * Custom element with following format
     * ---
     * template: Template_name
     * param1: myParam1
     * paramX: myParamX
     * ___
     *
     * crates {{Template_name|param1=myPara1|paramX=myParamX}}
     *
     * Attributes param1...paramX are optinal
     *
     * Block is identified by tirst 2 lines and consumed until : is on the line or it ends with ---
     */
    protected function identifyTemplate($line, $lines, $current)
    {
        return (array_key_exists($current + 1, $lines)
            && (strncmp($lines[$current + 1], 'template:', 9) === 0)
            && (strncmp($line, '---', 3) === 0));
    }

    /**
     * Consume lines for a template
     */
    protected function consumeTemplate($lines, $current)
    {
        // consume until newline / end of the tempalte

        $block = [
            'template',
            'attributes' => array(),
            'empty_line' => false,
        ];
        $line = rtrim($lines[$current]);

        // detect language and fence length (can be more than 3 dashes)
        $fence = substr($line, 0, $pos = strrpos($line, '-') + 1);
        list(, $template_name) = preg_split("/:[\s,]*/", $lines[$current + 1], 2);
        if (!empty($template_name)) {
            $block['template_name'] = $template_name;
        }

        // consume all lines until ---
        for ($i = $current + 2, $count = count($lines); $i < $count; $i++) {
            if (strpos($lines[$i], ':') !== false) {
                list($key, $value) = preg_split("/:[\s,]*/", $lines[$i], 2);
                $block['attributes'][strtolower($key)] = $value;
            } else {
                if ($lines[$i] == '' || $lines[$i + 1] == '') {
                    $block['empty_line'] = true;
                }
                break;
            }
            //if ((rtrim($line = $lines[$i]) !== $fence )
            //    && (strpos($line, ':') !== false)
            //) {
            //    list($key,$value) = preg_split("/:[\s,]*/", $line, 2);
            //    $block['attributes'][strtolower($key)] = $value;
            //} else {
            //    // stop consuming when code block is over
            //   break;
            //}
        }

        return [$block, $i];
    }

    /**
     * render a template block
     */
    protected function renderTemplate($block)
    {
        $template = $block['template_name'];
        $return = '{{' . $template . "\n";
        foreach ($block['attributes'] as $key => $value) {
            $return .= "|" . $key . "=" . $value . "\n";
        }
        $return .= "}}\n";
        if ($block['empty_line']) {
            $return .= "\n";
        }
        return $return;
    }
}
