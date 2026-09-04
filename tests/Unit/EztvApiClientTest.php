<?php

declare(strict_types=1);

namespace EztvXrss\Tests\Unit;

use EztvXrss\Client\EztvApiClient;
use EztvXrss\Client\HttpClientInterface;
use EztvXrss\Model\TorrentItem;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class EztvApiClientTest extends TestCase
{
    private string $fixtureJson;

    protected function setUp(): void
    {
        $fixturePath = dirname(__DIR__) . '/Fixtures/eztv_page1.json';
        $this->fixtureJson = file_get_contents($fixturePath);
    }

    public function testNormalizeImdbIdStripsPrefix(): void
    {
        $this->assertSame('12327578', EztvApiClient::normalizeImdbId('12327578'));
        $this->assertSame('12327578', EztvApiClient::normalizeImdbId('tt12327578'));
        $this->assertSame('12327578', EztvApiClient::normalizeImdbId('  TT12327578  '));
    }

    public function testNormalizeImdbIdRejectsInvalidInput(): void
    {
        $this->expectException(InvalidArgumentException::class);
        EztvApiClient::normalizeImdbId('1234; DROP TABLE torrents;');
    }

    public function testNormalizeImdbIdRejectsNonNumeric(): void
    {
        $this->expectException(InvalidArgumentException::class);
        EztvApiClient::normalizeImdbId('strange-new-worlds');
    }

    public function testFetchTorrentsParsesItemsSuccessfully(): void
    {
        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockHttpClient->expects($this->once())
            ->method('get')
            ->with($this->stringContains('imdb_id=12327578'))
            ->willReturn($this->fixtureJson);

        $client = new EztvApiClient($mockHttpClient);
        $result = $client->fetchTorrents('tt12327578', 1, 100);

        $this->assertNotEmpty($result);
        $this->assertInstanceOf(TorrentItem::class, $result[0]);
        $this->assertGreaterThan(0, $result[0]->id);
        $this->assertNotEmpty($result[0]->title);
        $this->assertNotEmpty($result[0]->magnetUrl);
        $this->assertGreaterThan(0, $result[0]->dateReleasedUnix);
        $this->assertGreaterThan(0, $result[0]->sizeBytes);
    }

    public function testFetchTorrentsThrowsOnInvalidJsonResponse(): void
    {
        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockHttpClient->method('get')->willReturn('<html>Cloudflare Challenge</html>');

        $client = new EztvApiClient($mockHttpClient);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid JSON returned by EZTV API');
        $client->fetchTorrents('12327578');
    }

    public function testFetchAllPagesCollectsMultiplePages(): void
    {
        $page1Data = json_decode($this->fixtureJson, true);
        $page1Data['torrents_count'] = 150;
        $page1Data['limit'] = 10;
        $page1Data['page'] = 1;
        $page1Data['torrents'] = array_slice($page1Data['torrents'], 0, 10);

        $page2Data = $page1Data;
        $page2Data['limit'] = 10;
        $page2Data['page'] = 2;
        $page2Data['torrents'] = array_slice($page1Data['torrents'], 0, 5);

        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockHttpClient->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                ['https://eztvx.to/api/get-torrents?imdb_id=12327578&limit=100&page=1', json_encode($page1Data)],
                ['https://eztvx.to/api/get-torrents?imdb_id=12327578&limit=100&page=2', json_encode($page2Data)],
            ]);

        $client = new EztvApiClient($mockHttpClient);
        $allTorrents = $client->fetchAllTorrents('12327578', maxPages: 2);

        $this->assertCount(15, $allTorrents);
    }
}
