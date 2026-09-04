<?php

declare(strict_types=1);

namespace EztvXrss\Model;

final readonly class TorrentItem
{
    public function __construct(
        public int $id,
        public string $hash,
        public string $filename,
        public string $title,
        public string $magnetUrl,
        public string $imdbId,
        public int $season,
        public int $episode,
        public int $seeds,
        public int $peers,
        public int $dateReleasedUnix,
        public int $sizeBytes,
        public string $smallScreenshot = '',
        public string $largeScreenshot = '',
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            hash: (string) ($data['hash'] ?? ''),
            filename: (string) ($data['filename'] ?? ''),
            title: (string) ($data['title'] ?? ''),
            magnetUrl: (string) ($data['magnet_url'] ?? ''),
            imdbId: (string) ($data['imdb_id'] ?? ''),
            season: (int) ($data['season'] ?? 0),
            episode: (int) ($data['episode'] ?? 0),
            seeds: (int) ($data['seeds'] ?? 0),
            peers: (int) ($data['peers'] ?? 0),
            dateReleasedUnix: (int) ($data['date_released_unix'] ?? 0),
            sizeBytes: (int) ($data['size_bytes'] ?? 0),
            smallScreenshot: (string) ($data['small_screenshot'] ?? ''),
            largeScreenshot: (string) ($data['large_screenshot'] ?? ''),
        );
    }

    /**
     * Format size in human-readable units (MB, GB, etc.)
     */
    public function formattedSize(): string
    {
        if ($this->sizeBytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = min((int) floor(log($this->sizeBytes, 1024)), count($units) - 1);
        $size = $this->sizeBytes / (1024 ** $power);

        return sprintf('%.2f %s', $size, $units[$power]);
    }

    /**
     * Returns release date formatted according to RFC 2822 for RSS pubDate
     */
    public function formattedPubDate(): string
    {
        return gmdate(DATE_RFC2822, $this->dateReleasedUnix);
    }

    /**
     * Returns the direct eztvx.to episode/torrent page URL.
     */
    public function eztvUrl(): string
    {
        $cleanTitle = trim((string) preg_replace('/(?:\s*-\s*|\s+)?EZTV$/i', '', $this->title));
        $slug = strtolower(trim((string) preg_replace('/[^a-zA-Z0-9]+/', '-', $cleanTitle), '-'));
        return sprintf('https://eztvx.to/ep/%d/%s/', $this->id, $slug !== '' ? $slug : 'torrent');
    }
}
