<?php
declare(strict_types=1);

namespace Infrastructure\Scheduler;

use RuntimeException;

final class FileLock
{
    /** @var resource|null */
    private $handle = null;

    /**
     * @param string $lockFilePath
     */
    public function __construct(private readonly string $lockFilePath) {}

    /**
     * Attempts to acquire an exclusive lock on the file.
     *
     * @return bool True if the lock was acquired, false otherwise.
     * @throws RuntimeException If the lock file cannot be opened.
     */
    public function tryAcquire(): bool
    {
        $this->handle = fopen($this->lockFilePath, 'c+');
        if ($this->handle === false) {
            throw new RuntimeException("Cannot open lock file: {$this->lockFilePath}");
        }

        return flock($this->handle, LOCK_EX | LOCK_NB);
    }

    /**
     * Releases the lock and closes the file handle.
     */
    public function release(): void
    {
        if ($this->handle !== null) {
            flock($this->handle, LOCK_UN);
            fclose($this->handle);
            $this->handle = null;
        }
    }
}
