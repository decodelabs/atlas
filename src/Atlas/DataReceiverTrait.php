<?php
/**
 * This file is part of the Atlas package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Atlas;

use DecodeLabs\Atlas\Channel\Buffer;
use DecodeLabs\Exceptional;

trait DataReceiverTrait
{
    /**
     * Is the resource still writable?
     */
    public function isWritable(): bool
    {
        return true;
    }

    /**
     * Check the resource is readable and throw exception if not
     */
    protected function checkWritable(): void
    {
        if (!$this->isWritable()) {
            throw Exceptional::Runtime(
                'Writing has been shut down'
            );
        }
    }


    /**
     * Write a single line of data
     */
    public function writeLine(?string $data=''): int
    {
        return $this->write($data.PHP_EOL);
    }

    /**
     * Pluck and write $length bytes from buffer
     */
    public function writeBuffer(Buffer $buffer, int $length): int
    {
        return $this->write($buffer->read($length), $length);
    }
}
