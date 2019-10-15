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

use DecodeLabs\Systemic;

class Signal implements Binding
{
    use BindingTrait {
        __construct as __traitConstruct;
    }

    public $signals = [];

    /**
     * Init with timer information
     */
    public function __construct(EventLoop $eventLoop, string $id, bool $persistent, $signals, callable $callback)
    {
        $this->__traitConstruct($eventLoop, $id, $persistent, $callback);
        $this->resource = [];

        foreach ((array)$signals as $signal) {
            $signal = Systemic::$process->newSignal($signal);
            $number = $signal->getNumber();
            $this->signals[$number] = $signal;
            $this->resource[$number] = null;
        }
    }

    /**
     * Get binding type
     */
    public function getType(): string
    {
        return 'Signal';
    }

    /**
     * Get signal list
     */
    public function getSignals(): array
    {
        return $this->signals;
    }

    /**
     * Has signal registered?
     */
    public function hasSignal(int $number): bool
    {
        return isset($this->signals[$number]);
    }

    /**
     * Destroy and unregister this binding
     */
    public function destroy(): Binding
    {
        $this->eventLoop->removeSignalBinding($this);
        return $this;
    }

    /**
     * Trigger event callback
     */
    public function trigger($number): Binding
    {
        if ($this->frozen) {
            return $this;
        }

        ($this->handler)($this->signals[$number], $this);

        if (!$this->persistent) {
            $this->eventLoop->removeSignalBinding($this);
        }

        return $this;
    }
}
