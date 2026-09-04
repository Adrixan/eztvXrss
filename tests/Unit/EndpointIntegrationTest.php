<?php

declare(strict_types=1);

namespace EztvXrss\Tests\Unit;

use DOMDocument;
use PHPUnit\Framework\TestCase;

final class EndpointIntegrationTest extends TestCase
{
    public function testFeedScriptOutputsValidXml(): void
    {
        // Run feed.php in CLI with query string simulation
        $cmd = sprintf(
            'php -r %s',
            escapeshellarg('
                $_GET = ["imdb" => "12327578", "resolution" => "1080p", "codec" => "x265"];
                $_SERVER["HTTPS"] = "on";
                $_SERVER["HTTP_HOST"] = "code-alongsi.de";
                $_SERVER["REQUEST_URI"] = "/eztvxrss/feed.php";
                ob_start();
                // Mock curl response using fixture
                $GLOBALS["MOCK_FIXTURE"] = true;
                require __DIR__ . "/feed.php";
                $output = ob_get_clean();
                echo $output;
            ')
        );

        $output = shell_exec($cmd);
        $this->assertNotEmpty($output);
        $this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $output);
        $this->assertStringContainsString('<rss version="2.0"', $output);
    }

    public function testApiScriptReturnsValidJson(): void
    {
        $cmd = sprintf(
            'php -r %s',
            escapeshellarg('
                $_GET = ["action" => "search", "q" => "strange new worlds"];
                ob_start();
                require __DIR__ . "/api.php";
                $output = ob_get_clean();
                echo $output;
            ')
        );

        $output = shell_exec($cmd);
        $this->assertNotEmpty($output);
        $data = json_decode($output, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('results', $data);
    }

    public function testOpmlScriptOutputsValidXml(): void
    {
        $cmd = sprintf(
            'php -r %s',
            escapeshellarg('
                $_GET = ["imdb" => "12327578", "resolution" => "1080p", "codec" => "x265", "show_title" => "Star Trek"];
                $_SERVER["HTTPS"] = "on";
                $_SERVER["HTTP_HOST"] = "code-alongsi.de";
                $_SERVER["SCRIPT_NAME"] = "/eztvxrss/opml.php";
                ob_start();
                require __DIR__ . "/opml.php";
                $output = ob_get_clean();
                echo $output;
            ')
        );

        $output = shell_exec($cmd);
        $this->assertNotEmpty($output);
        $dom = new DOMDocument();
        $this->assertTrue($dom->loadXML($output));
        $this->assertSame('opml', $dom->documentElement->nodeName);
    }
}
