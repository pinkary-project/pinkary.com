<?php

declare(strict_types=1);

namespace App\Services;

use GuzzleHttp\Psr7\Stream;
use GuzzleHttp\Psr7\Utils;
use RuntimeException;

final class BoundedResponseBody extends Stream
{
    /** @var int Number of response body bytes accepted by the stream. */
    private int $writtenBytes = 0;

    /** @var bool Whether the response body exceeded the configured limit. */
    private bool $limitExceeded = false;

    /** @param int $maximumBytes Maximum response body size in bytes. */
    public function __construct(private readonly int $maximumBytes)
    {
        parent::__construct(Utils::tryFopen('php://temp', 'w+'));
    }

    /** {@inheritDoc} */
    public function write(string $string): int
    {
        if ($this->writtenBytes + mb_strlen($string, '8bit') > $this->maximumBytes) {
            $this->limitExceeded = true;

            throw new RuntimeException('The response body exceeds the maximum allowed size.');
        }

        $writtenBytes = parent::write($string);
        $this->writtenBytes += $writtenBytes;

        return $writtenBytes;
    }

    /** Mark the response body as exceeding the configured limit. */
    public function markLimitExceeded(): void
    {
        $this->limitExceeded = true;
    }

    /** Determine whether the response body exceeded the configured limit. */
    public function hasExceededLimit(): bool
    {
        return $this->limitExceeded;
    }
}
