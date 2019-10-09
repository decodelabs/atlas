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

class Timer implements Binding
{
    use BindingTrait {
        __construct as __traitConstruct;
    }

    public $duration;

    /**
     * Init with timer information
     */
    public function __construct(EventLoop $eventLoop, string $id, bool $persistent, float $duration, callable $callback)
    {
        $this->__traitConstruct($eventLoop, $id, $persistent, $callback);
        $this->duration = $float;
    }

    /**
     * Get binding type
     */
    public function getType(): string
    {
        return 'Timer';
    }

    /**
     * Get timer duration
     */
    public function getDuration(): float
    {
        return $this->duration;
    }

    /**
     * Destroy and unregister this binding
     */
    public function destroy(): Binding
    {
        $this->eventLoop->removeTimer($this);
        return $this;
    }

    /**
     * Trigger event callback
     */
    public function trigger($time): Binding
    {
        if ($this->frozen) {
            return $this;
        }

        ($this->handler)($this);

        if (!$this->persistent) {
            $this->eventLoop->removeTimer($this);
        }

        return $this;
    }
}
