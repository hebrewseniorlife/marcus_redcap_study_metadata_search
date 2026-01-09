<?php

declare(strict_types=1);

namespace Infrastructure\Logging;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;
use RuntimeException;

final class JsonLogReader
{
    public function __construct(
        private readonly string $logPath
    ) {
        if (!is_file($logPath) || !is_readable($logPath)) {
            throw new InvalidArgumentException("Log file not found or not readable: {$logPath}");
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function since(string $intervalString, ?DateTimeInterface $now = null): array
    {
        $since = $this->computeSince($intervalString, $now);

        $records = [];
        foreach ($this->records() as $record) {
            if (!isset($record['datetime'])) {
                continue;
            }

            $dt = new DateTimeImmutable($record['datetime']);
            if ($dt >= $since) {
                $records[] = $record;
            }
        }

        return $records;
    }

    /**
     * Stream records from the file.
     *
     * @return \Generator<array<string, mixed>>
     */
    public function records(): \Generator
    {
        $fh = fopen($this->logPath, 'rb');
        if ($fh === false) {
            throw new RuntimeException("Failed to open log file: {$this->logPath}");
        }

        try {
            while (($line = fgets($fh)) !== false) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }

                $record = json_decode($line, true, flags: JSON_THROW_ON_ERROR);
                yield $record;
            }
        } finally {
            fclose($fh);
        }
    }

    private function computeSince(string $intervalString, ?DateTimeInterface $now): DateTimeImmutable
    {
        $intervalString = trim($intervalString);
        if ($intervalString === '') {
            throw new InvalidArgumentException('Interval string must not be empty.');
        }

        $interval = DateInterval::createFromDateString($intervalString);
        if ($interval === false) {
            throw new InvalidArgumentException("Invalid interval string: {$intervalString}");
        }

        $base = $now instanceof DateTimeImmutable
            ? $now
            : new DateTimeImmutable($now?->format(DateTimeInterface::ATOM) ?? 'now');

        return $base->sub($interval);
    }
}
