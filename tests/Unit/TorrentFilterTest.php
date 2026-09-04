<?php

declare(strict_types=1);

namespace EztvXrss\Tests\Unit;

use EztvXrss\Filter\FilterCriteria;
use EztvXrss\Filter\TorrentFilter;
use EztvXrss\Model\TorrentItem;
use PHPUnit\Framework\TestCase;

final class TorrentFilterTest extends TestCase
{
    /**
     * @var array<int, TorrentItem>
     */
    private array $sampleTorrents = [];

    protected function setUp(): void
    {
        $fixturePath = dirname(__DIR__) . '/Fixtures/eztv_page1.json';
        $json = file_get_contents($fixturePath);
        $data = json_decode($json, true);

        foreach ($data['torrents'] as $item) {
            $this->sampleTorrents[] = TorrentItem::fromArray($item);
        }
    }

    public function testFilterBy1080pAndHevcX265ReturnsOnlyMatches(): void
    {
        $criteria = new FilterCriteria(
            resolution: '1080p',
            codec: 'x265'
        );

        $filter = new TorrentFilter();
        $filtered = $filter->filter($this->sampleTorrents, $criteria);

        $this->assertNotEmpty($filtered);
        foreach ($filtered as $item) {
            $this->assertStringContainsString('Star Trek Strange New Worlds', $item->title);
            $text = $item->title . ' ' . $item->filename;
            $this->assertMatchesRegularExpression('/1080p/i', $text);
            $this->assertMatchesRegularExpression('/(?:x265|hevc|h265)/i', $text);
            $this->assertDoesNotMatchRegularExpression('/\b(480p|720p|2160p)\b/i', $text);
        }
    }

    public function testFilterBy720pAndX264ReturnsOnlyMatches(): void
    {
        $criteria = new FilterCriteria(
            resolution: '720p',
            codec: 'x264'
        );

        $filter = new TorrentFilter();
        $filtered = $filter->filter($this->sampleTorrents, $criteria);

        $this->assertNotEmpty($filtered);
        foreach ($filtered as $item) {
            $this->assertStringContainsString('Star Trek Strange New Worlds', $item->title);
            $text = $item->title . ' ' . $item->filename;
            $this->assertMatchesRegularExpression('/720p/i', $text);
            $this->assertMatchesRegularExpression('/(?:x264|h264|h\.264|h\s*264)/i', $text);
            $this->assertDoesNotMatchRegularExpression('/\b(480p|1080p|2160p)\b/i', $text);
            $this->assertDoesNotMatchRegularExpression('/(?:x265|hevc)/i', $text);
        }
    }

    public function testFilterBySeasonAndEpisode(): void
    {
        $criteria = new FilterCriteria(
            season: 3,
            episode: 4
        );

        $filter = new TorrentFilter();
        $filtered = $filter->filter($this->sampleTorrents, $criteria);

        $this->assertNotEmpty($filtered);
        foreach ($filtered as $item) {
            $this->assertSame(3, $item->season);
            $this->assertSame(4, $item->episode);
        }
    }

    public function testFilterByMinSeeds(): void
    {
        $criteria = new FilterCriteria(
            minSeeds: 50
        );

        $filter = new TorrentFilter();
        $filtered = $filter->filter($this->sampleTorrents, $criteria);

        $this->assertNotEmpty($filtered);
        foreach ($filtered as $item) {
            $this->assertGreaterThanOrEqual(50, $item->seeds);
        }
    }

    public function testFilterBySourceWebDl(): void
    {
        $criteria = new FilterCriteria(
            source: 'WEB-DL'
        );

        $filter = new TorrentFilter();
        $filtered = $filter->filter($this->sampleTorrents, $criteria);

        $this->assertNotEmpty($filtered);
        foreach ($filtered as $item) {
            $text = $item->title . ' ' . $item->filename;
            $this->assertMatchesRegularExpression('/web-dl/i', $text);
        }
    }

    public function testFilterAnyReturnsAllTorrents(): void
    {
        $criteria = new FilterCriteria();
        $filter = new TorrentFilter();
        $filtered = $filter->filter($this->sampleTorrents, $criteria);

        $this->assertCount(count($this->sampleTorrents), $filtered);
    }
}
