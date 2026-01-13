<?php

namespace Infrastructure\Configuration;

use Infrastructure\Logging\LoggingConfig;
use Infrastructure\Document\DocumentRepositoryConfig;
use Infrastructure\Search\SearchEngineConfig;
use Infrastructure\FileSystem\FileSystemConfig;
use Application\Service\Cron\CronServiceConfig;
use Application\Service\Cart\CartConfig;

final class SystemConfig
{
    public CartConfig $cart;

    public function __construct(
        public readonly LoggingConfig $logging,
        public readonly FileSystemConfig $fileSystem,
        public readonly DocumentRepositoryConfig $documentRepository,
        public readonly SearchEngineConfig $searchEngine,
        public readonly CronServiceConfig $cron,
        public readonly array $apiKeys = []
    ) {
        $this->cart = new CartConfig();
    }
}