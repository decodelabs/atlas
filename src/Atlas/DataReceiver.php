<?php
/**
 * This file is part of the Atlas package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Atlas;

use DecodeLabs\Atlas\Channel\Buffer;

interface DataReceiver
{
    public function isWritable(): bool;

    public function write(?string $data, int $length=null): int;
    public function writeLine(?string $data=''): int;
    public function writeBuffer(Buffer $buffer, int $length): int;
}
