<?php

declare(strict_types=1);

namespace App\Services;

use Closure;
use GuzzleHttp\Psr7\UriResolver;
use GuzzleHttp\Psr7\Utils;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Psr\Http\Message\ResponseInterface as PsrResponse;
use RuntimeException;
use Throwable;

final class SafeUrlFetcher
{
    public const int MAX_RESPONSE_BYTES = 5 * 1024 * 1024;

    private const int MAX_REDIRECTS = 5;

    /**
     * @var list<int>
     */
    private const array REDIRECT_STATUSES = [301, 302, 303, 307, 308];

    /**
     * @var Closure(string): list<string>
     */
    private readonly Closure $resolveHost;

    /**
     * @param  Closure(string): list<string>|null  $resolveHost
     */
    public function __construct(?Closure $resolveHost = null)
    {
        $this->resolveHost = $resolveHost ?? self::dnsAddresses(...);
    }

    /**
     * Fetch an HTTP URL only after validating and pinning all of its addresses.
     *
     * @param  array<string, string>  $headers
     */
    public function get(string $url, array $headers = [], int $timeout = 3): ?Response
    {
        if (! extension_loaded('curl') || ! defined('CURLOPT_RESOLVE')) {
            return null;
        }

        $currentUrl = $url;

        for ($redirect = 0; $redirect <= self::MAX_REDIRECTS; $redirect++) {
            $request = $this->validatedRequest($currentUrl);

            if ($request === null) {
                return null;
            }

            $responseBody = new BoundedResponseBody(self::MAX_RESPONSE_BYTES);

            try {
                $response = Http::withOptions([
                    'allow_redirects' => false,
                    'curl' => $request['curl_options'],
                    'on_headers' => static function (PsrResponse $response) use ($responseBody): void {
                        $contentLength = $response->getHeaderLine('Content-Length');

                        if (ctype_digit($contentLength) && (int) $contentLength > self::MAX_RESPONSE_BYTES) {
                            $responseBody->markLimitExceeded();

                            throw new RuntimeException('The response body exceeds the maximum allowed size.');
                        }
                    },
                    'proxy' => '',
                    'sink' => $responseBody,
                ])->withHeaders($headers)
                    ->timeout($timeout)
                    ->get($currentUrl);
            } catch (Throwable $exception) {
                if ($responseBody->hasExceededLimit()) {
                    return null;
                }

                throw $exception;
            }

            $contentLength = $response->header('Content-Length');

            if ($responseBody->hasExceededLimit()
                || (is_string($contentLength)
                    && ctype_digit($contentLength)
                    && (int) $contentLength > self::MAX_RESPONSE_BYTES)) {
                return null;
            }

            if (! in_array($response->status(), self::REDIRECT_STATUSES, true)) {
                return $response;
            }

            $location = $response->header('Location');

            if (! is_string($location) || $location === '') {
                return $response;
            }

            if ($redirect === self::MAX_REDIRECTS) {
                return null;
            }

            try {
                $currentUrl = (string) UriResolver::resolve(
                    Utils::uriFor($currentUrl),
                    Utils::uriFor($location),
                );
            } catch (Throwable) {
                return null;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private static function dnsAddresses(string $host): array
    {
        $records = dns_get_record($host, DNS_A | DNS_AAAA);

        if (! is_array($records)) {
            return [];
        }

        $addresses = [];

        foreach ($records as $record) {
            $address = $record['ip'] ?? $record['ipv6'] ?? null;

            if (is_string($address)) {
                $addresses[] = $address;
            }
        }

        return array_values(array_unique($addresses));
    }

    /**
     * @return array{curl_options: array<int, array<int, string>>}|null
     */
    private function validatedRequest(string $url): ?array
    {
        if (str_contains($url, '\\') || preg_match('/[\x00-\x20\x7f]/', $url) === 1) {
            return null;
        }

        $parts = parse_url($url);

        if (! is_array($parts)) {
            return null;
        }

        $scheme = $parts['scheme'] ?? null;
        $rawHost = $parts['host'] ?? null;

        if (! is_string($scheme)
            || ! in_array(mb_strtolower($scheme), ['http', 'https'], true)
            || ! is_string($rawHost)
            || $rawHost === ''
            || array_key_exists('user', $parts)
            || array_key_exists('pass', $parts)
            || str_contains($rawHost, '%')
            || preg_match('/[^\x21-\x7e]/', $rawHost) === 1) {
            return null;
        }

        $scheme = mb_strtolower($scheme);
        $host = mb_strtolower(mb_trim($rawHost, '[]'));
        $port = $parts['port'] ?? ($scheme === 'https' ? 443 : 80);

        if (! is_int($port) || $port < 1 || $port > 65535) {
            return null;
        }

        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            $addresses = [$host];
        } else {
            if (str_ends_with($host, '.')
                || str_contains($host, '..')
                || ! str_contains($host, '.')
                || preg_match('/^(?:0x[0-9a-f]+|[0-9]+)(?:\.(?:0x[0-9a-f]+|[0-9]+))*$/i', $host) === 1
                || filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) === false
                || $host === 'localhost'
                || str_ends_with($host, '.localhost')) {
                return null;
            }

            try {
                $addresses = ($this->resolveHost)($host);
            } catch (Throwable) {
                return null;
            }
        }

        if ($addresses === [] || ! array_is_list($addresses)) {
            return null;
        }

        foreach ($addresses as $address) {
            if (! is_string($address)
                || filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_GLOBAL_RANGE) === false) {
                return null;
            }
        }

        $curlOptions = [];

        if (filter_var($host, FILTER_VALIDATE_IP) === false) {
            $pinnedAddresses = array_map(
                static fn (string $address): string => str_contains($address, ':') ? '['.$address.']' : $address,
                array_values(array_unique($addresses)),
            );

            $curlOptions[CURLOPT_RESOLVE] = [$host.':'.$port.':'.implode(',', $pinnedAddresses)];
        }

        return ['curl_options' => $curlOptions];
    }
}
