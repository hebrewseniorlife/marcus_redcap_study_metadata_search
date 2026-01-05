<?php

namespace Application\Services\Cron;

final class CronConfig
{
    public function __construct(
        public readonly array $crons = []
    ) {}
}