<?php

declare(strict_types=1);

namespace EztvXrss\Tests\Unit;

use DOMDocument;
use EztvXrss\Feed\OpmlBuilder;
use PHPUnit\Framework\TestCase;

final class OpmlBuilderTest extends TestCase
{
    public function testBuildReturnsValidOpmlXml(): void
    {
        $builder = new OpmlBuilder('My EZTV Show Feeds');
        $feeds = [
            [
                'title' => 'Star Trek: Strange New Worlds (1080p HEVC)',
                'xmlUrl' => 'https://code-alongsi.de/eztvxrss/feed.php?imdb=12327578&resolution=1080p&codec=x265',
                'htmlUrl' => 'https://code-alongsi.de/eztvxrss/?imdb=12327578',
            ],
            [
                'title' => 'Star Trek: Strange New Worlds (720p x264)',
                'xmlUrl' => 'https://code-alongsi.de/eztvxrss/feed.php?imdb=12327578&resolution=720p&codec=x264',
                'htmlUrl' => 'https://code-alongsi.de/eztvxrss/?imdb=12327578',
            ],
        ];

        $xml = $builder->build($feeds);

        $this->assertNotEmpty($xml);
        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $xml);
        $this->assertStringContainsString('<opml version="2.0">', $xml);
        $this->assertStringContainsString('<title>My EZTV Show Feeds</title>', $xml);

        $dom = new DOMDocument();
        $this->assertTrue($dom->loadXML($xml), 'OPML must parse cleanly as valid XML');

        $outlines = $dom->getElementsByTagName('outline');
        $this->assertCount(2, $outlines);

        $first = $outlines->item(0);
        $this->assertNotNull($first);
        $this->assertSame('rss', $first->getAttribute('type'));
        $this->assertSame('Star Trek: Strange New Worlds (1080p HEVC)', $first->getAttribute('text'));
        $this->assertSame('https://code-alongsi.de/eztvxrss/feed.php?imdb=12327578&resolution=1080p&codec=x265', $first->getAttribute('xmlUrl'));
    }

    public function testBuildEscapesSpecialCharacters(): void
    {
        $builder = new OpmlBuilder('Special & Chars <OPML>');
        $feeds = [
            [
                'title' => 'Show <Title> & "Quotes"',
                'xmlUrl' => 'https://example.com/feed.php?a=1&b=2',
                'htmlUrl' => 'https://example.com/?x=3&y=4',
            ],
        ];

        $xml = $builder->build($feeds);

        $dom = new DOMDocument();
        $this->assertTrue($dom->loadXML($xml));
        $this->assertStringNotContainsString('<Title>', $xml);
        $this->assertStringContainsString('&amp;b=2', $xml);
    }
}
