<?php
declare(strict_types=1);

namespace Infrastructure\Scheduler;

use RuntimeException;

final class SingleJobConfig
{
    const string DEFAULT_LOCK_FILENAME  = 'single-job.lock';
    const string DEFAULT_STATE_FILENAME = 'single-job.state.json';

    public function __construct(
        public readonly string $jobKey,
        public readonly string $cronExpression,
        public readonly string $timezone = 'UTC',
        public readonly string $stateFilePath = SingleJobConfig::DEFAULT_STATE_FILENAME,
        public readonly string $lockFilePath = SingleJobConfig::DEFAULT_LOCK_FILENAME,
    ) {}

    // public static function fromEnvironment(): self
    // {
    //     $projectRoot = dirname(__DIR__, 3);
    //     $baseDir = getenv('SCHEDULER_DIR');

    //     if ($baseDir === false || trim($baseDir) === '') {
    //         $baseDir = $projectRoot . '/var/scheduler';
    //     }

    //     if (!is_dir($baseDir)) {
    //         if (!mkdir($baseDir, 0775, true) && !is_dir($baseDir)) {
    //             throw new RuntimeException("Failed to create scheduler directory: {$baseDir}");
    //         }
    //     }

    //     $jobKey = getenv('SINGLE_JOB_KEY') ?: 'single_job';
    //     $cron = getenv('SINGLE_JOB_CRON') ?: '*/5 * * * *';
    //     $tz = getenv('SINGLE_JOB_TZ') ?: 'UTC';

    //     return new self(
    //         $jobKey,
    //         $cron,
    //         $tz,
    //         $baseDir . '/single-job.state.json',
    //         $baseDir . '/single-job.lock'
    //     );
    // }
}
