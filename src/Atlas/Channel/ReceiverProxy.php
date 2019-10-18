<?php
/**
 * This file is part of the Atlas package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Atlas\Channel;

use DecodeLabs\Atlas\Channel;
use DecodeLabs\Atlas\ChannelTrait;

class ReceiverProxy implements Channel
{
    use ChannelTrait;

    protected $receiver;
    protected $writer;
    protected $open = true;
    protected $writable = true;

    /**
     * Init with stream path
     */
    public function __construct(object $receiver, callable $writer)
    {
        $this->receiver = $receiver;
        $this->writer = $writer;
    }


    /**
     * Get resource
     */
    public function getResource()
    {
        return $this->receiver;
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
     * Set as readable
     */
    public function setReadable(bool $flag): Channel
    {
        return $this;
    }

    /**
     * Is the resource still accessible?
     */
    public function isReadable(): bool
    {
        return false;
    }

    /**
     * Read up to $length bytes from resource
     */
    public function read(int $length): ?string
    {
        return null;
    }

    /**
     * Read single char from resource
     */
    public function readChar(): ?string
    {
        return null;
    }

    /**
     * Read single line from resource
     */
    public function readLine(): ?string
    {
        return null;
    }


    /**
     * Set as writable
     */
    public function setWritable(bool $flag): Channel
    {
        $this->writable = $flag;
        return $this;
    }

    /**
     * Is the resource still writable?
     */
    public function isWritable(): bool
    {
        return $this->open && $this->writable;
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

        ($this->writer)($this->receiver, $data);
        return strlen($data);
    }

    /**
     * Has this stream ended?
     */
    public function isAtEnd(): bool
    {
        return false;
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
