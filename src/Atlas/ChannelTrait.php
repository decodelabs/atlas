<?php
/**
 * This file is part of the Atlas package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Atlas;

use DecodeLabs\Atlas\Channel\Buffer;

use DecodeLabs\Glitch;

trait ChannelTrait
{
    /**
     * Set read blocking mode
     */
    public function setBlocking(bool $flag): Channel
    {
        if (!$flag) {
            throw Glitch::ERuntime('Channel does not support non-blocking mode');
        }
    }

    /**
     * Is this channel in blocking mode?
     */
    public function isBlocking(): bool
    {
        return true;
    }


    /**
     * Is the resource still accessible?
     */
    public function isReadable(): bool
    {
        return true;
    }


    /**
     * Read all available data from resource
     */
    public function readAll(): ?string
    {
        $this->checkReadable();
        $data = null;

        while (!$this->isAtEnd()) {
            $chunk = $this->read(8192);

            if ($chunk === null) {
                break;
            }

            $data .= $chunk;
        }

        return $data;
    }

    /**
     * Transfer available data to a write instance
     */
    public function readTo(Channel $writer): Channel
    {
        $this->checkReadable();

        while (!$this->isAtEnd()) {
            $chunk = $this->read(8192);

            if ($chunk === null) {
                break;
            }

            $writer->write($chunk);
        }

        return $this;
    }

    /**
     * Check the resource is readable and throw exception if not
     */
    protected function checkReadable(): void
    {
        if (!$this->getResource() || !$this->isReadable()) {
            throw Glitch::ERuntime('Reading has been shut down');
        }
    }





    /**
     * Is the resource still writable?
     */
    public function isWritable(): bool
    {
        return true;
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

    /**
     * Check the resource is readable and throw exception if not
     */
    protected function checkWritable(): void
    {
        if (!$this->getResource() || !$this->isWritable()) {
            throw Glitch::ERuntime('Writing has been shut down');
        }
    }
}
