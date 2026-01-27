<?php
declare(strict_types=1);

namespace Infrastructure\Scheduler;

use Cron\CronExpression;
use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;
use Throwable;

final class SingleJobRunner
{
    /**
     * @param SingleJobConfig $config
     * @param FileLock $lock
     */
    public function __construct(
        private readonly SingleJobConfig $config,
        private readonly FileLock $lock
    ) {}

    /**
     * Attempts to run the job if it is due.
     *
     * @param callable $job The job to run.
     * @return int Returns 1 if the job was run, 0 otherwise.
     * @throws Throwable Rethrows any exception thrown by the job.
     */
    public function tick(callable $job): int
    {
        if (!$this->lock->tryAcquire()) {
            return 0;
        }

        try {
            return $this->runIfDue($job);
        } finally {
            $this->lock->release();
        }
    }

    /**
     * Runs the job if it is due according to the cron expression and
     * has not already been run for the current scheduled minute.
     *
     * @param callable $job The job to run.
     * @return int Returns 1 if the job was run, 0 otherwise.
     * @throws Throwable Rethrows any exception thrown by the job.
     */
    private function runIfDue(callable $job): int
    {
        $tz = new DateTimeZone($this->config->timezone);
        $nowLocal = new DateTimeImmutable('now', $tz);

        $cron = CronExpression::factory($this->config->cronExpression);

        if (!$cron->isDue($nowLocal)) {
            return 0;
        }

        $state = $this->loadState();
        $scheduledMinuteKey = $nowLocal->format('Y-m-d H:i');

        if (($state['last_due_minute'] ?? null) === $scheduledMinuteKey) {
            return 0;
        }

        try {
            $job();
            $this->saveState([
                'job_key' => $this->config->jobKey,
                'last_due_minute' => $scheduledMinuteKey,
                'last_run_at' => (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format(DATE_ATOM),
                'status' => 'success',
            ]);
            return 1;
        } catch (Throwable $e) {
            $this->saveState([
                'job_key' => $this->config->jobKey,
                'last_due_minute' => $scheduledMinuteKey,
                'last_run_at' => (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format(DATE_ATOM),
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Loads the scheduler state from the state file.
     *
     * @return array The loaded state.
     */
    private function loadState(): array
    {
        if (!file_exists($this->config->stateFilePath)) {
            return [];
        }

        $raw = file_get_contents($this->config->stateFilePath);
        if ($raw === false || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Saves the scheduler state to the state file.
     *
     * @param array $state The state to save.
     * @throws RuntimeException If saving the state fails.
     */
    private function saveState(array $state): void
    {
        $tmp = $this->config->stateFilePath . '.tmp';
        $json = json_encode($state, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        if ($json === false) {
            throw new RuntimeException('Failed to encode scheduler state');
        }

        if (file_put_contents($tmp, $json) === false) {
            throw new RuntimeException("Failed to write temp state file: {$tmp}");
        }

        if (!rename($tmp, $this->config->stateFilePath)) {
            throw new RuntimeException('Failed to replace scheduler state file');
        }
    }

    /**
     * Retrieves information about the last run of the job.
     *
     * @return array The last run information.
     */
    public function getLastRunInfo(): array
    {
        return $this->loadState();
    }
}
