<?php

declare(strict_types=1);

namespace EztvXrss\Client;

use RuntimeException;

final class CurlHttpClient implements HttpClientInterface
{
    /**
     * @param string $defaultUserAgent
     */
    public function __construct(
        private string $defaultUserAgent = 'Mozilla/5.0 (compatible; eztvXrss/1.0; +https://code-alongsi.de/eztvxrss)'
    ) {
    }

    public function get(string $url, array $headers = [], int $timeoutSeconds = 15): string
    {
        $ch = curl_init();
        if ($ch === false) {
            throw new RuntimeException('Failed to initialize cURL');
        }

        $formattedHeaders = [];
        foreach ($headers as $key => $val) {
            $formattedHeaders[] = "{$key}: {$val}";
        }

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => $timeoutSeconds,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_USERAGENT => $this->defaultUserAgent,
            CURLOPT_HTTPHEADER => $formattedHeaders,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException("cURL request failed: {$curlError}");
        }

        if ($httpCode >= 400) {
            throw new RuntimeException("HTTP request failed with status {$httpCode}");
        }

        return (string) $response;
    }
}
