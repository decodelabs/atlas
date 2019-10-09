<?php
/**
 * This file is part of the Atlas package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Atlas\EventLoop\Binding;

use DecodeLabs\Atlas\EventLoop;
use DecodeLabs\Atlas\EventLoop\Binding;
use DecodeLabs\Atlas\EventLoop\BindingTrait;
use DecodeLabs\Atlas\EventLoop\Binding\Io;
use DecodeLabs\Atlas\EventLoop\Binding\IoTrait;
use DecodeLabs\Atlas\Channel\Stream as StreamChannel;

class Stream implements Binding, Io
{
    use BindingTrait {
        __construct as __traitConstruct;
    }
    use IoTrait;

    public $stream;
    public $streamId;

    /**
     * Init with timer information
     */
    public function __construct(EventLoop $eventLoop, bool $persistent, StreamChannel $stream, string $ioMode, callable $callback, ?float $timeout=null, ?callable $timeoutCallback=null)
    {
        $this->stream = $stream;
        $this->streamId = spl_object_id($stream);
        $this->ioMode = $ioMode;

        $this->__traitConstruct($eventLoop, $this->ioMode.':'.$this->streamId, $persistent, $callback);
        $this->timeout = $timeout;
        $this->timeoutHandler = $timeoutHandler;
    }

    /**
     * Get binding type
     */
    public function getType(): string
    {
        return 'Stream';
    }

    /**
     * Get stream object
     */
    public function getStream(): StreamChannel
    {
        return $this->stream;
    }

    /**
     * Get io resource
     */
    public function getIoResource()
    {
        return $this->stream->getResource();
    }

    /**
     * Destroy and unregister this binding
     */
    public function destroy(): Binding
    {
        $this->eventLoop->removeStreamBinding($this);
        return $this;
    }

    /**
     * Trigger event callback
     */
    public function trigger($resource): Binding
    {
        if ($this->frozen) {
            return $this;
        }

        ($this->handler)($this->stream, $this);

        if (!$this->persistent) {
            $this->eventLoop->removeStreamBinding($this);
        }

        return $this;
    }

    /**
     * Trigger timeout event callback
     */
    public function triggerTimeout($resource)
    {
        if ($this->frozen) {
            return;
        }

        if ($this->timeoutHandler) {
            ($this->timeoutHandler)($this->stream, $this);
        }

        return $this;
    }
}
