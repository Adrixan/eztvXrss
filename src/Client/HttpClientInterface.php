<?php

declare(strict_types=1);

namespace EztvXrss\Client;

interface HttpClientInterface
{
    /**
     * Perform HTTP GET request and return body string
     *
     * @param string $url
     * @param array<string, string> $headers
     * @param int $timeoutSeconds
     * @return string
     * @throws \RuntimeException
     */
    public function get(string $url, array $headers = [], int $timeoutSeconds = 15): string;
}
