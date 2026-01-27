<?php

namespace Application\Service\Scheduler;

use Psr\Log\LoggerInterface;
use Infrastructure\Scheduler\SchedulerConfig;
use Infrastructure\Scheduler\SingleJobRunner;
use Infrastructure\Scheduler\FileLock;

class SchedulerService {
    protected LoggerInterface $logger;
    protected SchedulerConfig $config;
    protected FileLock $fileLock;
    protected SingleJobRunner $runner;

    /**
     * Constructor for SchedulerService.
     *
     * @param LoggerInterface $logger The logger instance used for logging scheduler operations.
     */
    function __construct(LoggerInterface $logger, SchedulerConfig $config) {
        $this->logger = $logger;
        $this->config = $config;

        // Initialize FileLock and SingleJobRunner with the provided configuration
        $jobConfig = $this->config->jobConfig;

        $this->fileLock = new FileLock($jobConfig->lockFilePath);
        $this->runner   = new SingleJobRunner($jobConfig, $this->fileLock);
    }

    /**
     * Retrieves the scheduler configuration.
     *
     * @return SchedulerConfig The scheduler configuration object.
     */
    function getConfig() : SchedulerConfig {
        return $this->config;
    }

    /**
     * Runs a scheduled job based on the scheduler configuration.
     *
     * @param callable $job The job to be executed.
     * @return void
     */
    function runScheduledJob(callable $job): void {
        // Implementation for running a scheduled job
        $this->logger->info("Running scheduled job...");

        if (!$this->config->enabled) {
            $this->logger->info("Scheduler is disabled. Job will not run.");
            return;
        }   

        $this->logger->info("Scheduler is enabled. Running job...");
        $this->runner->tick($job);
        $this->logger->info("Scheduled job execution completed.");
    }

    /**
     * Retrieves information about the last run of the scheduled job.
     *
     * @return array An associative array containing details about the last run.
     */
    function getLastScheduledJobInfo(): array {
        return $this->runner->getLastRunInfo();
    }
}