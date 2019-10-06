<?php
/**
 * This file is part of the Atlas package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Atlas\Channel;

use DecodeLabs\Atlas\Channel;
use DecodeLabs\Atlas\ChannelTrait;

class Stream implements Channel
{
    use ChannelTrait;

    protected $resource;
    protected $mode = null;
    protected $readable = null;
    protected $writable = null;

    /**
     * Init with stream path
     */
    public function __construct($path, string $mode='a+')
    {
        if (is_resource($path)) {
            $this->resource = $path;
            $this->mode = stream_get_meta_data($this->resource)['mode'];
        } else {
            if (!$this->resource = fopen($path, $mode)) {
                throw Glitch::EIo('Unable to open stream');
            }

            $this->mode = $mode;
        }
    }


    /**
     * Get resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Get mode stream was opened with
     */
    public function getIoMode(): ?string
    {
        return $this->mode;
    }

    /**
     * Set read blocking mode
     */
    public function setBlocking(bool $flag): Channel
    {
        if (!$this->resource) {
            throw Glitch::ELogic('Cannot set blocking, resource not open');
        }

        stream_set_blocking($this->resource, $flag);
        return $this;
    }

    /**
     * Is this channel in blocking mode?
     */
    public function isBlocking(): bool
    {
        if (!$this->resource) {
            return false;
        }

        $meta = stream_get_meta_data($this->resource);
        return (bool)$meta['blocked'];
    }

    /**
     * Is the resource still accessible?
     */
    public function isReadable(): bool
    {
        if ($this->resource === null) {
            return false;
        }

        if ($this->readable === null) {
            $this->readable = (
                strstr($this->mode, 'r') ||
                strstr($this->mode, '+')
            );
        }

        return $this->readable;
    }

    /**
     * Read up to $length bytes from resource
     */
    public function read(int $length): ?string
    {
        $this->checkReadable();

        try {
            $output = fread($this->resource, $length);
        } catch (\Throwable $e) {
            return null;
        }

        if ($output === '' || $output === false) {
            $output = null;
        }

        return $output;
    }

    /**
     * Read single line from resource
     */
    public function readLine(): ?string
    {
        $this->checkReadable();

        try {
            $output = fgets($this->resource);
        } catch (\Throwable $e) {
            return null;
        }

        if ($output === '' || $output === false) {
            $output = null;
        } else {
            $output = rtrim($output, "\r\n");
        }

        return $output;
    }

    /**
     * Is the resource still writable?
     */
    public function isWritable(): bool
    {
        if ($this->resource === null) {
            return false;
        }

        if ($this->writable === null) {
            $this->writable = (
                strstr($this->mode, 'x') ||
                strstr($this->mode, 'w') ||
                strstr($this->mode, 'c') ||
                strstr($this->mode, 'a') ||
                strstr($this->mode, '+')
            );
        }

        return $this->writable;
    }

    /**
     * Write ?$length bytes to resource
     */
    public function write(?string $data, int $length=null): int
    {
        $this->checkWritable();

        if ($length !== null) {
            return fwrite($this->resource, (string)$data, $length);
        } else {
            return fwrite($this->resource, (string)$data);
        }
    }

    /**
     * Has this stream ended?
     */
    public function isAtEnd(): bool
    {
        if (!$this->resource) {
            return true;
        }

        return feof($this->resource);
    }

    /**
     * Close the stream
     */
    public function close(): Channel
    {
        if ($this->resource) {
            try {
                fclose($this->resource);
            } catch (\Throwable $e) {
            }
        }

        $this->resource = null;
        $this->mode = null;
        $this->readable = null;
        $this->writable = null;

        return $this;
    }
}
