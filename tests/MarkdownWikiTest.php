<?php

namespace paveld\markdownwiki\tests;

use cebe\markdown\tests\BaseMarkdownTest;
use paveld\markdownwiki\MarkdownWiki;

/**
 * Test case for traditional markdown.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @group default
 */
class MarkdownWikiTest extends BaseMarkdownTest
{

    protected $outputFileExtension = '.wiki';

    public function getDataPaths()
    {
        return [
            'markdownwiki-data' => __DIR__ . '/markdownwiki-data',
        ];
    }

    public function testEdgeCases()
    {
        $this->assertEquals("&amp;", $this->createMarkdown()->parse('&'));
        $this->assertEquals("&lt;", $this->createMarkdown()->parse('<'));
    }

    public function createMarkdown()
    {
        return new MarkdownWiki();
    }

    public function testKeepZeroAlive()
    {
        $parser = $this->createMarkdown();

        $this->assertEquals("0", $parser->parseParagraph("0"));
        $this->assertEquals("0", $parser->parse("0"));
    }

    public function testUtf8()
    {
        $this->assertSame("ěščřžýáíéóůňďť", $this->createMarkdown()->parse('ěščřžýáíéóůňďť'));
    }

    public function testInvalidUtf8()
    {
        $m = $this->createMarkdown();
        $this->assertEquals("<code>�</code>", $m->parse("`\x80`"));
        $this->assertEquals('<code>�</code>', $m->parseParagraph("`\x80`"));
    }

    /**
     * @dataProvider pregData
     */
    public function testPregReplaceR($input, $exptected, $pexpect = null)
    {
        $this->assertSame($exptected, $this->createMarkdown()->parseParagraph($input));
        $this->assertSame($pexpect === null ? "$exptected" : "$pexpect", $this->createMarkdown()->parse($input));
    }

    public function testAutoLinkLabelingWithEncodedUrl()
    {
        $parser = $this->createMarkdown();

        $utfText = "\xe3\x81\x82\xe3\x81\x84\xe3\x81\x86\xe3\x81\x88\xe3\x81\x8a";
        $utfNaturalUrl = "http://example.com/" . $utfText;
        $utfEncodedUrl = "http://example.com/" . urlencode($utfText);
        $eucEncodedUrl = "http://example.com/" . urlencode(mb_convert_encoding($utfText, 'EUC-JP', 'UTF-8'));

        $this->assertStringEndsWith(" {$utfNaturalUrl}]", $parser->parseParagraph("<{$utfNaturalUrl}>"), "Natural UTF-8 URL needs no conversion.");
        $this->assertStringEndsWith(" {$utfNaturalUrl}]", $parser->parseParagraph("<{$utfEncodedUrl}>"), "Encoded UTF-8 URL will be converted to readable format.");
        $this->assertStringEndsWith(" {$eucEncodedUrl}]", $parser->parseParagraph("<{$eucEncodedUrl}>"), "Non UTF-8 URL should never be converted.");
        // See: \cebe\markdown\inline\LinkTrait::renderUrl
    }

    public function pregData()
    {
        // http://en.wikipedia.org/wiki/Newline#Representations
        return [
            ["a\r\nb", "a\nb"],
            ["a\n\rb", "a\nb"], // Acorn BBC and RISC OS spooled text output :)
            ["a\nb", "a\nb"],
            ["a\rb", "a\nb"],

            ["a\n\nb", "a\n\nb", "a\n\nb"],
            ["a\r\rb", "a\n\nb", "a\n\nb"],
            ["a\n\r\n\rb", "a\n\nb", "a\n\nb"], // Acorn BBC and RISC OS spooled text output :)
            ["a\r\n\r\nb", "a\n\nb", "a\n\nb"],
        ];
    }
}
