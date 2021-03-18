<?php

/**
 * @package Atlas
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Atlas;

trait MutexTrait
{
    protected $name;
    protected $counter = 0;

    /**
     * Init with name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Ensure lock released
     */
    public function __destruct()
    {
        if ($this->counter) {
            $this->releaseLock();
        }

        $this->counter = 0;
    }

    /**
     * Get name of mutex
     */
    public function getName(): string
    {
        return $this->name;
    }


    /**
     * Acquire lock (with count), bail after $timeout seconds
     */
    public function lock(int $timeout = null): bool
    {
        if ($this->counter > 0 || $this->waitForLock($timeout)) {
            $this->counter++;
            return true;
        }

        return false;
    }

    /**
     * Keep attempting to lock until $timeout or success
     */
    protected function waitForLock(int $timeout = null): bool
    {
        $blocking = $timeout === null;
        $start = microtime(true);
        $end = $start + $timeout / 1000;
        $locked = false;

        while (
            (!$locked = $this->acquireLock($blocking)) &&
            ($blocking || microtime(true) < $end)
        ) {
            usleep(50000);
        }

        return $locked;
    }

    /**
     * Decrement counter or release lock
     */
    public function unlock(): Mutex
    {
        if (!$this->counter) {
            return $this;
        }

        $this->releaseLock();
        $this->counter = 0;

        return $this;
    }


    /**
     *  Has this mutex been acquired at least once?
     */
    public function isLocked(): bool
    {
        return $this->counter > 0;
    }

    /**
     * How many times as lock() been called without unlock()
     */
    public function countLocks(): int
    {
        return $this->counter;
    }

    abstract protected function acquireLock(bool $blocking): bool;
    abstract protected function releaseLock(): void;
}
