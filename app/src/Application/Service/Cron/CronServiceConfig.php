<?php

namespace Application\Service\Cron;

final class CronServiceConfig
{
    public function __construct(
        public readonly bool $enabled = false,
        public readonly string $pattern = '',
        public readonly array $crons = []
    ) {}
}