<?php

/**
 * @package Atlas
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Atlas;

interface DataProvider
{
    /**
     * @return $this
     */
    public function setReadBlocking(bool $flag): DataProvider;

    public function isReadBlocking(): bool;
    public function isReadable(): bool;

    public function read(int $length): ?string;
    public function readAll(): ?string;
    public function readChar(): ?string;
    public function readLine(): ?string;

    /**
     * @return $this
     */
    public function readTo(DataReceiver $writer): DataProvider;

    public function isAtEnd(): bool;
}
