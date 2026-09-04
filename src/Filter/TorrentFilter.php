<?php

declare(strict_types=1);

namespace EztvXrss\Filter;

use EztvXrss\Model\TorrentItem;

final class TorrentFilter
{
    /**
     * @param array<int, TorrentItem> $items
     * @param FilterCriteria $criteria
     * @return array<int, TorrentItem>
     */
    public function filter(array $items, FilterCriteria $criteria): array
    {
        $filtered = [];

        foreach ($items as $item) {
            if ($this->matches($item, $criteria)) {
                $filtered[] = $item;
            }
        }

        return $filtered;
    }

    public function matches(TorrentItem $item, FilterCriteria $criteria): bool
    {
        $text = $item->title . ' ' . $item->filename;

        // 1. Resolution
        if ($criteria->resolution !== 'any') {
            if (!$this->matchesResolution($text, $criteria->resolution)) {
                return false;
            }
        }

        // 2. Codec / Encoding
        if ($criteria->codec !== 'any') {
            if (!$this->matchesCodec($text, $criteria->codec)) {
                return false;
            }
        }

        // 3. Source Quality
        if ($criteria->source !== 'any') {
            if (!$this->matchesSource($text, $criteria->source)) {
                return false;
            }
        }

        // 4. Season
        if ($criteria->season !== null) {
            if ($item->season > 0) {
                if ($item->season !== $criteria->season) {
                    return false;
                }
            } else {
                $pattern = sprintf('/(?:S|Season\s*)0*%d\b/i', $criteria->season);
                if (preg_match($pattern, $text) !== 1) {
                    return false;
                }
            }
        }

        // 5. Episode
        if ($criteria->episode !== null) {
            if ($item->episode > 0) {
                if ($item->episode !== $criteria->episode) {
                    return false;
                }
            } else {
                $pattern = sprintf('/(?:E|Episode\s*)0*%d\b/i', $criteria->episode);
                if (preg_match($pattern, $text) !== 1) {
                    return false;
                }
            }
        }

        // 6. Minimum Seeds
        if ($criteria->minSeeds > 0 && $item->seeds < $criteria->minSeeds) {
            return false;
        }

        // 7. Optional keyword
        if ($criteria->keyword !== null) {
            if (stripos($text, $criteria->keyword) === false) {
                return false;
            }
        }

        return true;
    }

    private function matchesResolution(string $text, string $resolution): bool
    {
        return match ($resolution) {
            '2160p' => preg_match('/\b(?:2160p|4k|uhd)\b/i', $text) === 1,
            '1080p' => preg_match('/\b1080p\b/i', $text) === 1,
            '720p' => preg_match('/\b720p\b/i', $text) === 1,
            '480p' => preg_match('/\b480p\b/i', $text) === 1,
            default => true,
        };
    }

    private function matchesCodec(string $text, string $codec): bool
    {
        return match ($codec) {
            'x265' => preg_match('/\b(?:x265|hevc|h265|h\.265)\b/i', $text) === 1,
            'x264' => preg_match('/\b(?:x264|h264|h\.264|h\s*264|avc)\b/i', $text) === 1,
            'xvid' => preg_match('/\b(?:xvid|divx)\b/i', $text) === 1,
            default => true,
        };
    }

    private function matchesSource(string $text, string $source): bool
    {
        return match (strtoupper($source)) {
            'WEB-DL' => preg_match('/\bweb-?dl\b/i', $text) === 1,
            'WEBRIP' => preg_match('/\bweb-?rip\b/i', $text) === 1,
            'WEB' => preg_match('/\bweb\b/i', $text) === 1,
            'HDTV' => preg_match('/\bhdtv\b/i', $text) === 1,
            'BLURAY' => preg_match('/\b(?:bluray|brrip|bdrip)\b/i', $text) === 1,
            default => true,
        };
    }
}
