<?php

namespace Application\Service\Scheduler;

use Psr\Log\LoggerInterface;
use Infrastructure\Scheduler\SchedulerConfig;

class SchedulerService {
    protected LoggerInterface $logger;
    protected SchedulerConfig $config;

    /**
     * Constructor for SchedulerService.
     *
     * @param LoggerInterface $logger The logger instance used for logging scheduler operations.
     */
    function __construct(LoggerInterface $logger, SchedulerConfig $config) {
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     * Retrieves the scheduler configuration.
     *
     * @return SchedulerConfig The scheduler configuration object.
     */
    function getConfig() : SchedulerConfig {
        return $this->config;
    }
}