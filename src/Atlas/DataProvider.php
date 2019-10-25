<?php
/**
 * This file is part of the Atlas package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Atlas;

use DecodeLabs\Atlas\Channel;
use DecodeLabs\Atlas\Channel\Buffer;

interface DataProvider
{
    public function read(int $length): ?string;
    public function readAll(): ?string;
    public function readChar(): ?string;
    public function readLine(): ?string;
    public function readTo(Channel $writer): Channel;
}
