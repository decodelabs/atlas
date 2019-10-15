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
use DecodeLabs\Atlas\EventLoop\Binding\Io as IoBinding;
use DecodeLabs\Atlas\EventLoop\Binding\IoTrait;

use DecodeLabs\Atlas\Socket as SocketChannel;

class Socket implements IoBinding
{
    use BindingTrait {
        __construct as __traitConstruct;
    }
    use IoTrait;

    public $socket;
    public $socketId;

    /**
     * Init with timer information
     */
    public function __construct(EventLoop $eventLoop, bool $persistent, SocketChannel $socket, string $ioMode, callable $callback, ?float $timeout=null, ?callable $timeoutHandler=null)
    {
        $this->socket = $socket;
        $this->socketId = $socket->getId();
        $this->ioMode = $ioMode;

        $this->__traitConstruct($eventLoop, $this->ioMode.':'.$this->socketId, $persistent, $callback);
        $this->timeout = $timeout;
        $this->timeoutHandler = $timeoutHandler;
    }

    /**
     * Get binding type
     */
    public function getType(): string
    {
        return 'Socket';
    }

    /**
     * Get socket object
     */
    public function getSocket(): SocketChannel
    {
        return $this->socket;
    }

    /**
     * Is socket stream based?
     */
    public function isStreamBased(): bool
    {
        return $this->socket->getImplementationName() == 'streams';
    }

    /**
     * Get io resource
     */
    public function getIoResource()
    {
        return $this->socket->getResource();
    }

    /**
     * Destroy and unregister this binding
     */
    public function destroy(): Binding
    {
        $this->eventLoop->removeSocketBinding($this);
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

        ($this->handler)($this->socket, $this);

        if (!$this->persistent) {
            $this->eventLoop->removeSocketBinding($this);
        }

        return $this;
    }

    /**
     * Trigger timeout event callback
     */
    public function triggerTimeout($resource): IoBinding
    {
        if ($this->frozen) {
            return $this;
        }

        if ($this->timeoutHandler) {
            ($this->timeoutHandler)($this->socket, $this);
        }

        return $this;
    }
}
