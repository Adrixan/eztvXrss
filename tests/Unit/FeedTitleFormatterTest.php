<?php

declare(strict_types=1);

namespace EztvXrss\Tests\Unit;

use EztvXrss\Feed\FeedTitleFormatter;
use EztvXrss\Filter\FilterCriteria;
use EztvXrss\Model\TorrentItem;
use PHPUnit\Framework\TestCase;

final class FeedTitleFormatterTest extends TestCase
{
    public function testFormatWithResolutionAndCodec(): void
    {
        $criteria = new FilterCriteria(
            resolution: '1080p',
            codec: 'x265'
        );

        $title = FeedTitleFormatter::format('Star Trek: Strange New Worlds', $criteria);
        $this->assertSame('Star Trek: Strange New Worlds [1080p | HEVC x265]', $title);
    }

    public function testFormatWith720pAndX264(): void
    {
        $criteria = new FilterCriteria(
            resolution: '720p',
            codec: 'x264'
        );

        $title = FeedTitleFormatter::format('Star Trek: Strange New Worlds', $criteria);
        $this->assertSame('Star Trek: Strange New Worlds [720p | x264]', $title);
    }

    public function testFormatWithOnlyResolution(): void
    {
        $criteria = new FilterCriteria(
            resolution: '1080p'
        );

        $title = FeedTitleFormatter::format('Fallout', $criteria);
        $this->assertSame('Fallout [1080p]', $title);
    }

    public function testFormatWithOnlyCodec(): void
    {
        $criteria = new FilterCriteria(
            codec: 'x265'
        );

        $title = FeedTitleFormatter::format('The Bear', $criteria);
        $this->assertSame('The Bear [HEVC x265]', $title);
    }

    public function testFormatWithoutFiltersHasNoSquareBrackets(): void
    {
        $criteria = new FilterCriteria();

        $title = FeedTitleFormatter::format('Star Trek: Strange New Worlds', $criteria);
        $this->assertSame('Star Trek: Strange New Worlds', $title);
    }

    public function testExtractShowTitleFromTorrents(): void
    {
        $fixturePath = dirname(__DIR__) . '/Fixtures/eztv_page1.json';
        $json = file_get_contents($fixturePath);
        $data = json_decode($json, true);

        $torrents = [];
        foreach ($data['torrents'] as $item) {
            $torrents[] = TorrentItem::fromArray($item);
        }

        $extracted = FeedTitleFormatter::extractShowTitleFromTorrents($torrents);
        $this->assertSame('Star Trek Strange New Worlds', $extracted);
    }
}
