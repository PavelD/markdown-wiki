<?php
/**
 * @copyright Copyright (c) 2022 Pavel Dobes
 * @license https://github.com/PavelD/markdown-wiki/blob/main/LICENSE
 * @link hhttps://github.com/PavelD/markdown-wiki#readme
 */

namespace paveld\markdownwiki\inline;

/**
 * Adds inline magic word elements
 *
 * Because standard magic wrods are idetifyed as bold text, new syntax is introduced.
 * Example: {{__NOTOC__}} isntead of __NOTOC__.
 */
trait MagicWordTrait
{
    /**
     * Parses the magic word elements.
     * @marker {{__
     */
    protected function parseMagicWord($markdown)
    {
        if (preg_match('/^{{(__[A-Z]+__)}}/', $markdown, $matches)) {
            return [
                [
                    'magicword',
                    $matches[1]
                ],
                strlen($matches[0])
            ];
        }
        return [['text', $markdown[0] . $markdown[1]], 2];
    }

    protected function renderMagicWord($block)
    {
        return $block[1];
    }

}
