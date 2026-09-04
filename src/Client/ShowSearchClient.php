<?php

declare(strict_types=1);

namespace EztvXrss\Client;

use RuntimeException;

final class ShowSearchClient
{
    private const string BASE_SEARCH_URL = 'https://api.tvmaze.com/search/shows';
    private const string BASE_LOOKUP_URL = 'https://api.tvmaze.com/lookup/shows';

    public function __construct(
        private ?HttpClientInterface $httpClient = null
    ) {
        $this->httpClient ??= new CurlHttpClient();
    }

    /**
     * Lookup show title by IMDb ID (e.g. '12327578' or 'tt12327578')
     */
    public function lookupByImdbId(string $imdbId): ?string
    {
        $trimmed = trim($imdbId);
        if ($trimmed === '') {
            return null;
        }

        $code = str_starts_with(strtolower($trimmed), 'tt') ? $trimmed : 'tt' . $trimmed;
        $url = self::BASE_LOOKUP_URL . '?imdb=' . urlencode($code);

        try {
            $json = $this->httpClient->get($url);
        } catch (RuntimeException) {
            return null;
        }

        $data = json_decode($json, true);
        if (!is_array($data) || empty($data['name'])) {
            return null;
        }

        return (string) $data['name'];
    }

    /**
     * Search TV shows by title and return those with IMDb IDs
     *
     * @return array<int, array{
     *     name: string,
     *     imdb_id: string,
     *     imdb_code: string,
     *     year: string,
     *     image: string,
     *     summary: string,
     *     genres: array<int, string>
     * }>
     */
    public function search(string $query): array
    {
        $trimmed = trim($query);
        if ($trimmed === '') {
            return [];
        }

        $url = self::BASE_SEARCH_URL . '?q=' . urlencode($trimmed);

        try {
            $json = $this->httpClient->get($url);
        } catch (RuntimeException) {
            return [];
        }

        $data = json_decode($json, true);
        if (!is_array($data)) {
            return [];
        }

        $results = [];
        foreach ($data as $item) {
            if (!isset($item['show']) || !is_array($item['show'])) {
                continue;
            }

            $show = $item['show'];
            $imdbRaw = $show['externals']['imdb'] ?? null;
            if (!is_string($imdbRaw) || !preg_match('/^(?:tt)?(\d+)$/i', trim($imdbRaw), $m)) {
                continue;
            }

            $numericId = $m[1];
            $premiered = (string) ($show['premiered'] ?? '');
            $year = $premiered !== '' ? substr($premiered, 0, 4) : '';

            $results[] = [
                'name' => (string) ($show['name'] ?? ''),
                'imdb_id' => $numericId,
                'imdb_code' => 'tt' . $numericId,
                'year' => $year,
                'image' => (string) ($show['image']['medium'] ?? $show['image']['original'] ?? ''),
                'summary' => strip_tags((string) ($show['summary'] ?? '')),
                'genres' => (array) ($show['genres'] ?? []),
            ];
        }

        return $results;
    }
}
