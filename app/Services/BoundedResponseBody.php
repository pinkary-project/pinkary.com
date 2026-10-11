<?php

declare(strict_types=1);

namespace App\Services;

use GuzzleHttp\Psr7\Stream;
use GuzzleHttp\Psr7\Utils;
use RuntimeException;

final class BoundedResponseBody extends Stream
{
    private int $writtenBytes = 0;

    private bool $limitExceeded = false;

    public function __construct(private readonly int $maximumBytes)
    {
        parent::__construct(Utils::tryFopen('php://temp', 'w+'));
    }

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

    public function markLimitExceeded(): void
    {
        $this->limitExceeded = true;
    }

    public function hasExceededLimit(): bool
    {
        return $this->limitExceeded;
    }
}
