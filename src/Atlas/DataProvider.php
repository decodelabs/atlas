<?php
/**
 * This file is part of the Atlas package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Atlas;

use DecodeLabs\Atlas\DataReceiver;

interface DataProvider
{
    public function setReadBlocking(bool $flag): DataProvider;
    public function isReadBlocking(): bool;

    public function isReadable(): bool;

    public function read(int $length): ?string;
    public function readAll(): ?string;
    public function readChar(): ?string;
    public function readLine(): ?string;
    public function readTo(DataReceiver $writer): DataProvider;

    public function isAtEnd(): bool;
}
