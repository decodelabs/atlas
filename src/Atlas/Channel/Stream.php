<?php

/**
 * @package Atlas
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Atlas\Channel;

use DecodeLabs\Atlas\Channel;
use DecodeLabs\Atlas\DataProvider;
use DecodeLabs\Atlas\DataProviderTrait;
use DecodeLabs\Atlas\DataReceiverTrait;

use DecodeLabs\Exceptional;
use Throwable;

class Stream implements Channel
{
    use DataProviderTrait;
    use DataReceiverTrait;

    /**
     * @var resource|null
     */
    protected $resource;

    /**
     * @var string|null
     */
    protected $mode = null;

    /**
     * @var bool|null
     */
    protected $readable = null;

    /**
     * @var bool|null
     */
    protected $writable = null;

    /**
     * Init with stream path
     *
     * @param string|resource $stream
     */
    public function __construct($stream, ?string $mode = 'a+')
    {
        if (empty($stream)) {
            return;
        }

        $isResource = is_resource($stream);

        if ($mode === null && !$isResource) {
            return;
        }

        if ($isResource) {
            $this->resource = $stream;
            $this->mode = stream_get_meta_data($this->resource)['mode'];
        } else {
            if (!$resource = fopen($stream, (string)$mode)) {
                throw Exceptional::Io(
                    'Unable to open stream'
                );
            }

            $this->resource = $resource;
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
    public function setReadBlocking(bool $flag): DataProvider
    {
        if ($this->resource === null) {
            throw Exceptional::Logic(
                'Cannot set blocking, resource not open'
            );
        }

        stream_set_blocking($this->resource, $flag);
        return $this;
    }

    /**
     * Is this channel in blocking mode?
     */
    public function isReadBlocking(): bool
    {
        if ($this->resource === null) {
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
            if ($this->mode === null) {
                return false;
            }

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

        if ($this->resource === null) {
            return null;
        }

        try {
            $output = fread($this->resource, $length);
        } catch (Throwable $e) {
            return null;
        }

        if ($output === '' || $output === false) {
            $output = null;
        }

        return $output;
    }

    /**
     * Read single cgar from resource
     */
    public function readChar(): ?string
    {
        $this->checkReadable();

        if ($this->resource === null) {
            return null;
        }

        try {
            $output = fgetc($this->resource);
        } catch (Throwable $e) {
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

        if ($this->resource === null) {
            return null;
        }

        try {
            $output = fgets($this->resource);
        } catch (Throwable $e) {
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
            if ($this->mode === null) {
                return false;
            }

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
    public function write(?string $data, int $length = null): int
    {
        $this->checkWritable();

        if ($this->resource === null) {
            return 0;
        }

        if ($length !== null) {
            $output = fwrite($this->resource, (string)$data, $length);
        } else {
            $output = fwrite($this->resource, (string)$data);
        }

        if ($output === false) {
            throw Exceptional::Io(
                'Unable to write to stream',
                null,
                $this
            );
        }

        return $output;
    }

    /**
     * Has this stream ended?
     */
    public function isAtEnd(): bool
    {
        if ($this->resource === null) {
            return true;
        }

        return feof($this->resource);
    }

    /**
     * Close the stream
     */
    public function close(): Channel
    {
        if ($this->resource !== null) {
            try {
                fclose($this->resource);
            } catch (Throwable $e) {
            }
        }

        $this->resource = null;
        $this->mode = null;
        $this->readable = null;
        $this->writable = null;

        return $this;
    }
}
