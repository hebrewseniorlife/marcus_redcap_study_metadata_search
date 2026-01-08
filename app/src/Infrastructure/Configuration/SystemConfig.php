<?php

namespace Infrastructure\Configuration;

use Infrastructure\Logging\LoggingConfig;
use Infrastructure\Document\DocumentRepositoryConfig;
use Infrastructure\Search\SearchEngineConfig;
use Application\Service\Cron\CronServiceConfig;

final class SystemConfig
{
    public function __construct(
        public readonly LoggingConfig $logging,
        public readonly string $tempFolder = '',
        public readonly DocumentRepositoryConfig $documentRepository,
        public readonly SearchEngineConfig $searchEngine,
        public readonly CronServiceConfig $cron,
        public readonly array $apiKeys = []
    ) {}
}