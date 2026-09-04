<?php

declare(strict_types=1);

namespace EztvXrss\Tests\Unit;

use EztvXrss\Model\TorrentItem;
use PHPUnit\Framework\TestCase;

final class TorrentItemTest extends TestCase
{
    public function testEztvUrlGeneratesCorrectSlugAndId(): void
    {
        $item = new TorrentItem(
            id: 3148599,
            hash: '1ee025c9da782e6c916d80f4acb5e4b3c34bd863',
            filename: 'Star.Trek.Strange.New.Worlds.S04E07.1080p.HEVC.x265-MeGusta[EZTVx.to].mkv',
            title: 'Star Trek Strange New Worlds S04E07 1080p HEVC x265-MeGusta EZTV',
            magnetUrl: 'magnet:?xt=urn:btih:1ee025c9da782e6c916d80f4acb5e4b3c34bd863',
            imdbId: '12327578',
            season: 4,
            episode: 7,
            seeds: 3801,
            peers: 1469,
            dateReleasedUnix: 1788419895,
            sizeBytes: 400035005
        );

        $expectedUrl = 'https://eztvx.to/ep/3148599/star-trek-strange-new-worlds-s04e07-1080p-hevc-x265-megusta/';
        $this->assertSame($expectedUrl, $item->eztvUrl());
    }

    public function testEztvUrlFallbackWhenTitleEmpty(): void
    {
        $item = new TorrentItem(
            id: 12345,
            hash: 'abc',
            filename: '',
            title: 'EZTV',
            magnetUrl: 'magnet:?xt=urn:btih:abc',
            imdbId: '12345',
            season: 1,
            episode: 1,
            seeds: 1,
            peers: 1,
            dateReleasedUnix: 1700000000,
            sizeBytes: 1000
        );

        $expectedUrl = 'https://eztvx.to/ep/12345/torrent/';
        $this->assertSame($expectedUrl, $item->eztvUrl());
    }

    public function testFormattedSize(): void
    {
        $item0 = new TorrentItem(
            id: 1, hash: 'a', filename: '', title: 'Test', magnetUrl: '', imdbId: '1',
            season: 1, episode: 1, seeds: 0, peers: 0, dateReleasedUnix: 0, sizeBytes: 0
        );
        $this->assertSame('0 B', $item0->formattedSize());

        $itemMb = new TorrentItem(
            id: 2, hash: 'b', filename: '', title: 'Test', magnetUrl: '', imdbId: '1',
            season: 1, episode: 1, seeds: 0, peers: 0, dateReleasedUnix: 0, sizeBytes: 104857600
        );
        $this->assertSame('100.00 MB', $itemMb->formattedSize());
    }
}
