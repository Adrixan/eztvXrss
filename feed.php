<?php

declare(strict_types=1);

// Standalone autoloader or Composer autoloader
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/src/autoload.php')) {
    require_once __DIR__ . '/src/autoload.php';
}

use EztvXrss\Client\EztvApiClient;
use EztvXrss\Client\ShowSearchClient;
use EztvXrss\Filter\FilterCriteria;
use EztvXrss\Filter\TorrentFilter;
use EztvXrss\Feed\FeedTitleFormatter;
use EztvXrss\Feed\RssFeedBuilder;

// Mandatory No-Cache headers: Fresh fetch on each reader poll
header('Content-Type: application/rss+xml; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
header('X-Content-Type-Options: nosniff');

$rawImdb = (string) ($_GET['imdb'] ?? $_GET['imdb_id'] ?? '');

try {
    if (trim($rawImdb) === '') {
        throw new InvalidArgumentException('Missing required parameter "imdb" (e.g. ?imdb=12327578).');
    }

    $normalizedImdb = EztvApiClient::normalizeImdbId($rawImdb);
    $criteria = FilterCriteria::fromArray($_GET);

    $client = new EztvApiClient();
    // Fetch up to 3 pages (up to 300 torrents) to ensure full episode coverage without excessive latency
    $torrents = $client->fetchAllTorrents($normalizedImdb, maxPages: 3);

    $filter = new TorrentFilter();
    $filteredTorrents = $filter->filter($torrents, $criteria);

    // Resolve show name: parameter override -> TV lookup -> release parse -> IMDb fallback
    $showName = trim((string) ($_GET['title'] ?? $_GET['show_title'] ?? ''));
    if ($showName === '') {
        $searchClient = new ShowSearchClient();
        $showName = $searchClient->lookupByImdbId($normalizedImdb) ?? '';
    }
    if ($showName === '') {
        $showName = FeedTitleFormatter::extractShowTitleFromTorrents($torrents) ?? ('IMDb tt' . $normalizedImdb);
    }

    $channelTitle = FeedTitleFormatter::format($showName, $criteria);

    // Determine current URL for self link
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'code-alongsi.de';
    $uri = $_SERVER['REQUEST_URI'] ?? '/eztvxrss/feed.php';
    $selfUrl = $scheme . '://' . $host . $uri;

    $builder = new RssFeedBuilder(
        channelTitle: $channelTitle,
        channelLink: $selfUrl,
        channelDescription: sprintf(
            'EZTV Show RSS Feed for IMDb ID %s with %d matching releases.',
            $normalizedImdb,
            count($filteredTorrents)
        ),
        ttl: 60
    );

    echo $builder->build($filteredTorrents);
} catch (Throwable $e) {
    http_response_code(400);
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<rss version="2.0">' . "\n";
    echo '  <channel>' . "\n";
    echo '    <title>EZTV RSS - Error</title>' . "\n";
    echo '    <description>' . htmlspecialchars($e->getMessage(), ENT_XML1, 'UTF-8') . '</description>' . "\n";
    echo '    <link>https://code-alongsi.de/eztvxrss/</link>' . "\n";
    echo '    <item>' . "\n";
    echo '      <title>Error: ' . htmlspecialchars($e->getMessage(), ENT_XML1, 'UTF-8') . '</title>' . "\n";
    echo '      <description>' . htmlspecialchars($e->getMessage(), ENT_XML1, 'UTF-8') . '</description>' . "\n";
    echo '      <guid isPermaLink="false">error-' . time() . '</guid>' . "\n";
    echo '      <pubDate>' . gmdate(DATE_RFC2822) . '</pubDate>' . "\n";
    echo '    </item>' . "\n";
    echo '  </channel>' . "\n";
    echo '</rss>' . "\n";
}
