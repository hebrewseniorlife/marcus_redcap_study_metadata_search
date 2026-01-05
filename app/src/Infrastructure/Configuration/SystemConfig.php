<?php

namespace Infrastructure\Configuration;

use Infrastructure\Logging\LoggingConfig;

final class SystemConfig
{
    public function __construct(
        public readonly LoggingConfig $logging,
        public readonly array $apiKeys = []
    ) {}
}