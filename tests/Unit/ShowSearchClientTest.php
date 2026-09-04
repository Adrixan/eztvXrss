<?php

declare(strict_types=1);

namespace EztvXrss\Tests\Unit;

use EztvXrss\Client\HttpClientInterface;
use EztvXrss\Client\ShowSearchClient;
use PHPUnit\Framework\TestCase;

final class ShowSearchClientTest extends TestCase
{
    private string $fixtureJson;

    protected function setUp(): void
    {
        $fixturePath = dirname(__DIR__) . '/Fixtures/tvmaze_search.json';
        $this->fixtureJson = file_get_contents($fixturePath);
    }

    public function testSearchShowsParsesResultsWithImdbIds(): void
    {
        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockHttpClient->expects($this->once())
            ->method('get')
            ->with($this->stringContains('api.tvmaze.com/search/shows?q=strange+new+worlds'))
            ->willReturn($this->fixtureJson);

        $client = new ShowSearchClient($mockHttpClient);
        $results = $client->search('strange new worlds');

        $this->assertNotEmpty($results);
        $first = $results[0];

        $this->assertSame('Star Trek: Strange New Worlds', $first['name']);
        $this->assertSame('12327578', $first['imdb_id']);
        $this->assertSame('tt12327578', $first['imdb_code']);
        $this->assertSame('2022', $first['year']);
        $this->assertNotEmpty($first['image']);
    }

    public function testSearchWithEmptyQueryReturnsEmptyArray(): void
    {
        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockHttpClient->expects($this->never())->method('get');

        $client = new ShowSearchClient($mockHttpClient);
        $results = $client->search('   ');

        $this->assertSame([], $results);
    }

    public function testSearchFiltersOutShowsWithoutImdbId(): void
    {
        $mockData = [
            [
                'score' => 1.0,
                'show' => [
                    'id' => 101,
                    'name' => 'Show Without IMDb',
                    'premiered' => '2023-01-01',
                    'externals' => [
                        'imdb' => null,
                    ],
                ],
            ],
            [
                'score' => 0.9,
                'show' => [
                    'id' => 102,
                    'name' => 'Show With IMDb',
                    'premiered' => '2023-02-02',
                    'externals' => [
                        'imdb' => 'tt9876543',
                    ],
                ],
            ],
        ];

        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockHttpClient->method('get')->willReturn(json_encode($mockData));

        $client = new ShowSearchClient($mockHttpClient);
        $results = $client->search('test');

        $this->assertCount(1, $results);
        $this->assertSame('Show With IMDb', $results[0]['name']);
        $this->assertSame('9876543', $results[0]['imdb_id']);
    }

    public function testLookupByImdbIdReturnsShowName(): void
    {
        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockHttpClient->expects($this->once())
            ->method('get')
            ->with($this->stringContains('api.tvmaze.com/lookup/shows?imdb=tt12327578'))
            ->willReturn(json_encode([
                'id' => 48090,
                'name' => 'Star Trek: Strange New Worlds',
            ]));

        $client = new ShowSearchClient($mockHttpClient);
        $name = $client->lookupByImdbId('12327578');

        $this->assertSame('Star Trek: Strange New Worlds', $name);
    }

    public function testLookupByImdbIdReturnsNullOnError(): void
    {
        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockHttpClient->method('get')->willThrowException(new \RuntimeException('Not found'));

        $client = new ShowSearchClient($mockHttpClient);
        $name = $client->lookupByImdbId('99999999');

        $this->assertNull($name);
    }
}
