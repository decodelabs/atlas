<?php

/**
 * @package Atlas
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Atlas;

/**
 * @phpstan-require-implements Mutex
 */
trait MutexTrait
{
    public protected(set) string $name;
    protected int $counter = 0;

    public function __construct(
        string $name
    ) {
        $this->name = $name;
    }

    public function __destruct()
    {
        if ($this->counter) {
            $this->releaseLock();
        }

        $this->counter = 0;
    }

    public function lock(
        ?int $timeout = null
    ): bool {
        if (
            $this->counter > 0 ||
            $this->waitForLock($timeout)
        ) {
            $this->counter++;
            return true;
        }

        return false;
    }

    protected function waitForLock(
        ?int $timeout = null
    ): bool {
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

    public function unlock(): Mutex
    {
        if (!$this->counter) {
            return $this;
        }

        $this->releaseLock();
        $this->counter = 0;

        return $this;
    }


    public function isLocked(): bool
    {
        return $this->counter > 0;
    }

    public function countLocks(): int
    {
        return $this->counter;
    }

    abstract protected function acquireLock(
        bool $blocking
    ): bool;

    abstract protected function releaseLock(): void;
}
