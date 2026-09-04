<?php

declare(strict_types=1);

namespace EztvXrss\Filter;

final readonly class FilterCriteria
{
    public string $resolution;
    public string $codec;
    public string $source;
    public ?int $season;
    public ?int $episode;
    public int $minSeeds;
    public ?string $keyword;

    public function __construct(
        string $resolution = 'any',
        string $codec = 'any',
        string $source = 'any',
        ?int $season = null,
        ?int $episode = null,
        int $minSeeds = 0,
        ?string $keyword = null,
    ) {
        $this->resolution = self::sanitizeResolution($resolution);
        $this->codec = self::sanitizeCodec($codec);
        $this->source = self::sanitizeSource($source);
        $this->season = ($season !== null && $season >= 0) ? $season : null;
        $this->episode = ($episode !== null && $episode >= 0) ? $episode : null;
        $this->minSeeds = max(0, $minSeeds);
        $this->keyword = ($keyword !== null && trim($keyword) !== '') ? trim($keyword) : null;
    }

    /**
     * Build from array of query parameters (e.g. $_GET)
     *
     * @param array<string, mixed> $params
     */
    public static function fromArray(array $params): self
    {
        $season = isset($params['season']) && is_numeric($params['season']) ? (int) $params['season'] : null;
        $episode = isset($params['episode']) && is_numeric($params['episode']) ? (int) $params['episode'] : null;
        $minSeeds = isset($params['min_seeds']) && is_numeric($params['min_seeds']) ? (int) $params['min_seeds'] : 0;

        return new self(
            resolution: (string) ($params['resolution'] ?? 'any'),
            codec: (string) ($params['codec'] ?? 'any'),
            source: (string) ($params['source'] ?? 'any'),
            season: $season,
            episode: $episode,
            minSeeds: $minSeeds,
            keyword: isset($params['keyword']) ? (string) $params['keyword'] : null,
        );
    }

    public static function sanitizeResolution(string $value): string
    {
        $lower = strtolower(trim($value));
        return match ($lower) {
            '2160p', '4k', 'uhd' => '2160p',
            '1080p', 'fhd' => '1080p',
            '720p', 'hd' => '720p',
            '480p', 'sd' => '480p',
            default => 'any',
        };
    }

    public static function sanitizeCodec(string $value): string
    {
        $lower = strtolower(trim($value));
        if (str_contains($lower, '265') || str_contains($lower, 'hevc')) {
            return 'x265';
        }
        if (str_contains($lower, '264') || str_contains($lower, 'avc')) {
            return 'x264';
        }
        if (str_contains($lower, 'xvid') || str_contains($lower, 'divx')) {
            return 'xvid';
        }
        return 'any';
    }

    public static function sanitizeSource(string $value): string
    {
        $lower = strtolower(trim($value));
        return match ($lower) {
            'web-dl', 'webdl' => 'WEB-DL',
            'webrip' => 'WEBRip',
            'web' => 'WEB',
            'hdtv' => 'HDTV',
            'bluray', 'bdrip', 'brrip' => 'BluRay',
            default => 'any',
        };
    }
}
