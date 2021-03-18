<?php

/**
 * @package Atlas
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Atlas\DataReceiver;

use DecodeLabs\Atlas\DataReceiver;
use DecodeLabs\Atlas\DataReceiverTrait;

class Proxy implements DataReceiver
{
    use DataReceiverTrait;

    protected $receiver;
    protected $writer;
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
     * Set as writable
     */
    public function setWritable(bool $flag): DataReceiver
    {
        $this->writable = $flag;
        return $this;
    }

    /**
     * Is the resource still writable?
     */
    public function isWritable(): bool
    {
        return $this->writable;
    }

    /**
     * Write ?$length bytes to resource
     */
    public function write(?string $data, int $length = null): int
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
}
