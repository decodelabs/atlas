<?php
/**
 * This file is part of the Atlas package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Atlas;

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

        while (!$this->eof()) {
            $data .= $this->read(8192);
        }

        return $data;
    }

    /**
     * Transfer available data to a write instance
     */
    public function writeTo(Channel $writer): Channel
    {
        $this->checkReadable();

        while (!$this->eof()) {
            $writer->write($this->read(8192));
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
        return $this->write($data."\r\n");
    }

    /**
     * Pluck and write $length bytes from buffer
     */
    public function writeBuffer(string &$buffer, int $length): int
    {
        $result = $this->write($buffer, $length);
        $buffer = substr($buffer, $result);
        return $result;
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
