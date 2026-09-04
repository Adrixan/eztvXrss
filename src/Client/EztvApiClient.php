<?php

declare(strict_types=1);

namespace EztvXrss\Client;

use EztvXrss\Model\TorrentItem;
use InvalidArgumentException;
use RuntimeException;

final class EztvApiClient
{
    private const string BASE_URL = 'https://eztvx.to/api/get-torrents';

    public function __construct(
        private ?HttpClientInterface $httpClient = null
    ) {
        $this->httpClient ??= new CurlHttpClient();
    }

    /**
     * Normalize and validate IMDb ID (e.g. 'tt12327578' or '12327578' -> '12327578')
     *
     * @throws InvalidArgumentException
     */
    public static function normalizeImdbId(string $imdbId): string
    {
        $trimmed = trim($imdbId);
        if (preg_match('/^(?:tt)?(\d{1,10})$/i', $trimmed, $matches) !== 1) {
            throw new InvalidArgumentException(
                sprintf('Invalid IMDb ID format "%s". Expected digits or "tt" followed by digits.', $trimmed)
            );
        }

        return $matches[1];
    }

    /**
     * Fetch a single page of torrents for an IMDb ID
     *
     * @return array<int, TorrentItem>
     */
    public function fetchTorrents(string $imdbId, int $page = 1, int $limit = 100): array
    {
        $normalizedId = self::normalizeImdbId($imdbId);
        $safeLimit = max(1, min(100, $limit));
        $safePage = max(1, $page);

        $url = sprintf(
            '%s?imdb_id=%s&limit=%d&page=%d',
            self::BASE_URL,
            urlencode($normalizedId),
            $safeLimit,
            $safePage
        );

        $responseBody = $this->httpClient->get($url);

        $decoded = json_decode($responseBody, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Invalid JSON returned by EZTV API');
        }

        $items = [];
        $torrents = $decoded['torrents'] ?? [];
        if (is_array($torrents)) {
            foreach ($torrents as $rawTorrent) {
                if (is_array($rawTorrent)) {
                    $items[] = TorrentItem::fromArray($rawTorrent);
                }
            }
        }

        return $items;
    }

    /**
     * Fetch all pages of torrents up to $maxPages
     *
     * @return array<int, TorrentItem>
     */
    public function fetchAllTorrents(string $imdbId, int $maxPages = 5): array
    {
        $normalizedId = self::normalizeImdbId($imdbId);
        $all = [];

        for ($page = 1; $page <= $maxPages; $page++) {
            $url = sprintf(
                '%s?imdb_id=%s&limit=100&page=%d',
                self::BASE_URL,
                urlencode($normalizedId),
                $page
            );

            $responseBody = $this->httpClient->get($url);
            $decoded = json_decode($responseBody, true);

            if (!is_array($decoded)) {
                throw new RuntimeException('Invalid JSON returned by EZTV API');
            }

            $torrents = $decoded['torrents'] ?? [];
            if (!is_array($torrents) || empty($torrents)) {
                break;
            }

            foreach ($torrents as $rawTorrent) {
                if (is_array($rawTorrent)) {
                    $all[] = TorrentItem::fromArray($rawTorrent);
                }
            }

            $totalCount = (int) ($decoded['torrents_count'] ?? 0);
            $limit = (int) ($decoded['limit'] ?? 100);
            if (count($all) >= $totalCount || count($torrents) < $limit) {
                break;
            }
        }

        return $all;
    }
}
