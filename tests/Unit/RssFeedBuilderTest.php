<?php

declare(strict_types=1);

namespace EztvXrss\Tests\Unit;

use DOMDocument;
use EztvXrss\Feed\RssFeedBuilder;
use EztvXrss\Model\TorrentItem;
use PHPUnit\Framework\TestCase;

final class RssFeedBuilderTest extends TestCase
{
    /**
     * @var array<int, TorrentItem>
     */
    private array $items = [];

    protected function setUp(): void
    {
        $fixturePath = dirname(__DIR__) . '/Fixtures/eztv_page1.json';
        $json = file_get_contents($fixturePath);
        $data = json_decode($json, true);

        foreach (array_slice($data['torrents'], 0, 5) as $item) {
            $this->items[] = TorrentItem::fromArray($item);
        }
    }

    public function testBuildReturnsValidRssXml(): void
    {
        $builder = new RssFeedBuilder(
            channelTitle: 'Star Trek: Strange New Worlds (1080p HEVC)',
            channelLink: 'https://code-alongsi.de/eztvxrss/',
            channelDescription: 'Custom filtered RSS feed for Star Trek: Strange New Worlds'
        );

        $xml = $builder->build($this->items);

        $this->assertNotEmpty($xml);
        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $xml);
        $this->assertStringContainsString('<rss version="2.0"', $xml);
        $this->assertStringContainsString('<title>Star Trek: Strange New Worlds (1080p HEVC)</title>', $xml);
        $this->assertStringContainsString('<ttl>60</ttl>', $xml);

        // Validate XML syntax with DOMDocument
        $dom = new DOMDocument();
        $this->assertTrue($dom->loadXML($xml), 'Feed output must be well-formed XML');

        $rss = $dom->getElementsByTagName('rss')->item(0);
        $this->assertNotNull($rss);
        $this->assertSame('2.0', $rss->getAttribute('version'));

        $items = $dom->getElementsByTagName('item');
        $this->assertCount(5, $items);

        $firstItem = $items->item(0);
        $this->assertNotNull($firstItem);

        $title = $firstItem->getElementsByTagName('title')->item(0)?->nodeValue;
        $this->assertNotEmpty($title);

        $link = $firstItem->getElementsByTagName('link')->item(0)?->nodeValue;
        $this->assertNotEmpty($link);
        $this->assertStringStartsWith('https://eztvx.to/ep/', $link, 'Item link must point to eztvx.to episode page');

        $guid = $firstItem->getElementsByTagName('guid')->item(0)?->nodeValue;
        $this->assertNotEmpty($guid);
        $this->assertStringStartsWith('https://eztvx.to/ep/', $guid, 'Item guid must point to eztvx.to episode page');

        $pubDate = $firstItem->getElementsByTagName('pubDate')->item(0)?->nodeValue;
        $this->assertNotEmpty($pubDate);
        $this->assertNotFalse(strtotime($pubDate));

        $magnetUris = $firstItem->getElementsByTagNameNS('http://xmlns.ezrss.it/0.1/', 'magnetURI');
        $this->assertSame(1, $magnetUris->length, 'Must have torrent:magnetURI element');
        $this->assertStringStartsWith('magnet:?', $magnetUris->item(0)?->nodeValue ?? '');

        $enclosure = $firstItem->getElementsByTagName('enclosure')->item(0);
        $this->assertNotNull($enclosure);
        $this->assertSame('application/x-bittorrent', $enclosure->getAttribute('type'));
        $this->assertStringStartsWith('magnet:?', $enclosure->getAttribute('url'));
        $this->assertGreaterThan(0, (int) $enclosure->getAttribute('length'));

        $desc = $firstItem->getElementsByTagName('description')->item(0)?->nodeValue ?? '';
        $this->assertStringContainsString('https://eztvx.to/ep/', $desc);
        $this->assertMatchesRegularExpression('/href="magnet:\?xt=[^"]+&dn=[^"]+"/', $desc, 'Magnet href in CDATA must use raw & parameter delimiters');
    }

    public function testBuildEscapesXmlEntitiesInSpecialCharacters(): void
    {
        $maliciousItem = new TorrentItem(
            id: 999999,
            hash: 'deadbeef1234567890',
            filename: 'Test.Show.<script>alert(1)</script>&"\'',
            title: 'Test Show <script>alert(1)</script> & Specials <Season 1> "Quoted" \'Single\'',
            magnetUrl: 'magnet:?xt=urn:btih:deadbeef1234567890&dn=Test+Show+S01&tr=udp://tracker.test:1337',
            imdbId: '123456',
            season: 1,
            episode: 1,
            seeds: 10,
            peers: 2,
            dateReleasedUnix: 1700000000,
            sizeBytes: 1000000
        );

        $builder = new RssFeedBuilder(
            channelTitle: 'Special & Characters <Feed>',
            channelLink: 'https://example.com/?a=1&b=2',
            channelDescription: 'Escape & Safe'
        );

        $xml = $builder->build([$maliciousItem]);

        // Must load cleanly without XML parse errors
        $dom = new DOMDocument();
        $this->assertTrue($dom->loadXML($xml), 'XML with special chars must parse cleanly');
        $this->assertStringNotContainsString('<script>', $xml);
        $this->assertStringContainsString('&lt;script&gt;', $xml);
    }
}
