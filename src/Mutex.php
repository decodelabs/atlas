<?php

/**
 * @package Atlas
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Atlas;

interface Mutex
{
    public function getName(): string;

    public function lock(
        int $timeout = null
    ): bool;

    /**
     * @return $this
     */
    public function unlock(): Mutex;

    public function isLocked(): bool;
    public function countLocks(): int;
}
