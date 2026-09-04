<?php

declare(strict_types=1);

namespace EztvXrss\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class MagnetRedirectTest extends TestCase
{
    public function testValidMagnetUrlIssues302Redirect(): void
    {
        $cmd = sprintf(
            'php -r %s',
            escapeshellarg('
                $_GET = ["url" => "magnet:?xt=urn:btih:1ee025c9da782e6c916d80f4acb5e4b3c34bd863&dn=Star.Trek"];
                require __DIR__ . "/magnet.php";
            ')
        );

        $result = shell_exec($cmd);
        $this->assertNotEmpty($result);
        $this->assertStringContainsString('Open Magnet in Client', $result);
        $this->assertStringContainsString('1ee025c9da782e6c916d80f4acb5e4b3c34bd863', $result);
    }

    public function testValidHashAndTitleGeneratesRedirect(): void
    {
        $cmd = sprintf(
            'php -r %s',
            escapeshellarg('
                $_GET = ["hash" => "1ee025c9da782e6c916d80f4acb5e4b3c34bd863", "title" => "Star Trek"];
                require __DIR__ . "/magnet.php";
            ')
        );

        $result = shell_exec($cmd);
        $this->assertNotEmpty($result);
        $this->assertStringContainsString('Open Magnet in Client', $result);
        $this->assertStringContainsString('1ee025c9da782e6c916d80f4acb5e4b3c34bd863', $result);
    }

    public function testInvalidUrlRejectsOpenRedirect(): void
    {
        $cmd = sprintf(
            'php -r %s',
            escapeshellarg('
                $_GET = ["url" => "https://evil-attacker.com/malware"];
                require __DIR__ . "/magnet.php";
            ')
        );

        $result = shell_exec($cmd);
        $this->assertNotEmpty($result);
        $this->assertStringContainsString('Invalid Magnet URI', $result);
        $this->assertStringNotContainsString('Open Magnet in Client', $result);
    }

    public function testJavascriptProtocolBlocked(): void
    {
        $cmd = sprintf(
            'php -r %s',
            escapeshellarg('
                $_GET = ["url" => "javascript:alert(1)"];
                require __DIR__ . "/magnet.php";
            ')
        );

        $result = shell_exec($cmd);
        $this->assertNotEmpty($result);
        $this->assertStringContainsString('Invalid Magnet URI', $result);
        $this->assertStringNotContainsString('Open Magnet in Client', $result);
    }
}
