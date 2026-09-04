<?php

declare(strict_types=1);

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/src/autoload.php')) {
    require_once __DIR__ . '/src/autoload.php';
}

use EztvXrss\Client\ShowSearchClient;
use EztvXrss\Feed\FeedTitleFormatter;
use EztvXrss\Feed\OpmlBuilder;
use EztvXrss\Filter\FilterCriteria;

header('Content-Type: text/x-opml+xml; charset=utf-8');
header('Content-Disposition: attachment; filename="eztv-feeds.opml"');
header('Cache-Control: no-cache, no-store, must-revalidate');

try {
    $feedsJson = $_POST['feeds'] ?? $_GET['feeds'] ?? null;
    $feedList = [];

    if (is_string($feedsJson) && trim($feedsJson) !== '') {
        $decoded = json_decode($feedsJson, true);
        if (is_array($decoded)) {
            $feedList = $decoded;
        }
    }

    // If single feed parameters passed via query string
    if (empty($feedList) && !empty($_GET['imdb'])) {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'code-alongsi.de';
        $path = dirname($_SERVER['SCRIPT_NAME'] ?? '/eztvxrss') . '/feed.php';
        $query = http_build_query($_GET);
        $feedUrl = $scheme . '://' . $host . $path . '?' . $query;

        $criteria = FilterCriteria::fromArray($_GET);
        $showName = trim((string) ($_GET['show_title'] ?? $_GET['title'] ?? ''));
        if ($showName === '') {
            $searchClient = new ShowSearchClient();
            $showName = $searchClient->lookupByImdbId((string) $_GET['imdb']) ?? ('IMDb tt' . $_GET['imdb']);
        }

        $title = FeedTitleFormatter::format($showName, $criteria);

        $feedList[] = [
            'title' => $title,
            'xmlUrl' => $feedUrl,
            'htmlUrl' => $scheme . '://' . $host . dirname($_SERVER['SCRIPT_NAME'] ?? '/eztvxrss') . '/?imdb=' . urlencode((string) $_GET['imdb']),
        ];
    }

    $builder = new OpmlBuilder('EZTV RSS Subscriptions');
    echo $builder->build($feedList);
} catch (Throwable $e) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Error generating OPML: ' . $e->getMessage();
}
