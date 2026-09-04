<?php

declare(strict_types=1);

namespace EztvXrss\Feed;

use EztvXrss\Filter\FilterCriteria;
use EztvXrss\Model\TorrentItem;

final class FeedTitleFormatter
{
    /**
     * Format feed title matching show name with filter tags in square brackets
     */
    public static function format(string $showName, FilterCriteria $criteria): string
    {
        $tags = [];

        if ($criteria->resolution !== 'any') {
            $tags[] = $criteria->resolution;
        }

        if ($criteria->codec !== 'any') {
            $tags[] = match ($criteria->codec) {
                'x265' => 'HEVC x265',
                'x264' => 'x264',
                'xvid' => 'XviD',
                default => $criteria->codec,
            };
        }

        if ($criteria->source !== 'any') {
            $tags[] = $criteria->source;
        }

        if ($criteria->season !== null) {
            $tags[] = 'S' . str_pad((string) $criteria->season, 2, '0', STR_PAD_LEFT);
        }

        if ($criteria->episode !== null) {
            $tags[] = 'E' . str_pad((string) $criteria->episode, 2, '0', STR_PAD_LEFT);
        }

        $cleanShowName = trim($showName);
        if (empty($tags)) {
            return $cleanShowName;
        }

        return sprintf('%s [%s]', $cleanShowName, implode(' | ', $tags));
    }

    /**
     * Extract show title from the title or filename of release items
     *
     * @param array<int, TorrentItem> $torrents
     */
    public static function extractShowTitleFromTorrents(array $torrents): ?string
    {
        if (empty($torrents)) {
            return null;
        }

        $first = $torrents[0];
        $candidates = [$first->title, $first->filename];

        foreach ($candidates as $text) {
            if (trim($text) === '') {
                continue;
            }

            // Match show name before season/episode notation or year, e.g. "Star Trek Strange New Worlds S04E07"
            if (preg_match('/^(.*?)\s+(?:S\d{1,2}|Season\s*\d{1,2}|\b\d{4}\b)/i', $text, $m)) {
                $rawTitle = $m[1];
                // Replace dots or underscores with spaces
                $cleaned = preg_replace('/[._]+/', ' ', $rawTitle);
                $cleaned = trim(preg_replace('/\s+/', ' ', (string) $cleaned));
                if ($cleaned !== '') {
                    return $cleaned;
                }
            }
        }

        return null;
    }
}
