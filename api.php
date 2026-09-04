<?php

declare(strict_types=1);

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/src/autoload.php')) {
    require_once __DIR__ . '/src/autoload.php';
}

use EztvXrss\Client\EztvApiClient;
use EztvXrss\Client\ShowSearchClient;
use EztvXrss\Filter\FilterCriteria;
use EztvXrss\Filter\TorrentFilter;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('X-Content-Type-Options: nosniff');

$action = (string) ($_GET['action'] ?? '');

try {
    switch ($action) {
        case 'search':
            $q = (string) ($_GET['q'] ?? '');
            if (trim($q) === '') {
                echo json_encode(['results' => []]);
                exit;
            }
            $searchClient = new ShowSearchClient();
            $shows = $searchClient->search($q);
            echo json_encode(['results' => $shows]);
            break;

        case 'preview':
            $rawImdb = (string) ($_GET['imdb'] ?? $_GET['imdb_id'] ?? '');
            if (trim($rawImdb) === '') {
                throw new InvalidArgumentException('IMDb ID is required.');
            }

            $normalizedImdb = EztvApiClient::normalizeImdbId($rawImdb);
            $criteria = FilterCriteria::fromArray($_GET);

            $apiClient = new EztvApiClient();
            $torrents = $apiClient->fetchAllTorrents($normalizedImdb, maxPages: 3);

            $filter = new TorrentFilter();
            $filtered = $filter->filter($torrents, $criteria);

            $previewItems = [];
            foreach (array_slice($filtered, 0, 50) as $item) {
                $previewItems[] = [
                    'id' => $item->id,
                    'title' => $item->title,
                    'filename' => $item->filename,
                    'size' => $item->formattedSize(),
                    'seeds' => $item->seeds,
                    'peers' => $item->peers,
                    'released' => $item->formattedPubDate(),
                    'magnet' => $item->magnetUrl,
                    'season' => $item->season,
                    'episode' => $item->episode,
                ];
            }

            echo json_encode([
                'success' => true,
                'imdb_id' => $normalizedImdb,
                'total_fetched' => count($torrents),
                'total_matched' => count($filtered),
                'items' => $previewItems,
            ]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Unknown action. Valid actions: search, preview.']);
            break;
    }
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
