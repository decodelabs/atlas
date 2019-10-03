<?php
/**
 * This file is part of the Atlas package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Atlas\Channel;

use DecodeLabs\Atlas\Channel;
use DecodeLabs\Atlas\ChannelTrait;

class Buffer implements Channel
{
    use ChannelTrait;

    protected $buffer;
    protected $open = true;

    /**
     * Init with stream path
     */
    public function __construct(string $buffer=null)
    {
        $this->buffer = (string)$buffer;
    }


    /**
     * Get resource
     */
    public function getResource()
    {
        return $this;
    }

    /**
     * Set read blocking mode
     */
    public function setBlocking(bool $flag): Channel
    {
        return $this;
    }

    /**
     * Is this channel in blocking mode?
     */
    public function isBlocking(): bool
    {
        return false;
    }

    /**
     * Is the resource still accessible?
     */
    public function isReadable(): bool
    {
        return $this->open;
    }

    /**
     * Read up to $length bytes from resource
     */
    public function read(int $length): ?string
    {
        $this->checkReadable();

        $output = substr($this->buffer, 0, $length);
        $this->buffer = substr($this->buffer, $length);

        return $output;
    }

    /**
     * Read single line from resource
     */
    public function readLine(): ?string
    {
        $this->checkReadable();

        $output = '';
        $length = strlen($this->buffer);
        $pos = 0;

        while ($pos < $length) {
            if ($this->buffer[$pos] == "\n") {
                $pos++;
                break;
            }

            $output .= $this->buffer[$pos];
            $pos++;
        }

        $this->buffer = substr($this->buffer, $pos);
        return $output;
    }

    /**
     * Is the resource still writable?
     */
    public function isWritable(): bool
    {
        return $this->open;
    }

    /**
     * Write ?$length bytes to resource
     */
    public function write(?string $data, int $length=null): int
    {
        $this->checkWritable();

        if ($data === null) {
            return 0;
        }

        if ($length !== null) {
            $data = substr($data, 0, $length);
        }

        $this->buffer .= $data;
        return strlen($data);
    }

    /**
     * Has this stream ended?
     */
    public function isAtEnd(): bool
    {
        return !strlen($this->buffer);
    }

    /**
     * Close the stream
     */
    public function close(): Channel
    {
        $this->open = false;
        return $this;
    }
}
