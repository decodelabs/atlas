<?php
/**
 * This file is part of the Atlas package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Atlas\EventLoop;

use DecodeLabs\Atlas\EventLoop;
use DecodeLabs\Atlas\EventLoopTrait;
use DecodeLabs\Atlas\EventLoop\Binding;
use DecodeLabs\Atlas\EventLoop\Binding\Io as IoBinding;

use DecodeLabs\Atlas\EventLoop\Binding\Socket as SocketBinding;
use DecodeLabs\Atlas\EventLoop\Binding\Stream as StreamBinding;
use DecodeLabs\Atlas\EventLoop\Binding\Signal as SignalBinding;
use DecodeLabs\Atlas\EventLoop\Binding\Timer as TimerBinding;

use DecodeLabs\Glitch;

use EventBase as EventLibBase;
use Event as EventLib;

class Event implements EventLoop
{
    use EventLoopTrait;

    protected $base;
    protected $cycleHandlerEvent;

    /**
     * Setup event base
     */
    public function __construct()
    {
        $this->base = new EventLibBase();
    }

    /**
     * Begin event loop
     */
    public function listen(): EventLoop
    {
        $this->listening = true;
        $this->base->loop();
        $this->listening = false;

        return $this;
    }

    /**
     * End event loop and return control
     */
    public function stop(): EventLoop
    {
        if ($this->listening) {
            $this->base->exit();
            $this->listening = false;
        }

        return $this;
    }



    /**
     * Temporarily remove binding from loop
     */
    public function freezeBinding(Binding $binding): EventLoop
    {
        if ($binding->isFrozen()) {
            return $this;
        }

        $this->{'unregister'.$binding->getType().'Binding'}($binding);
        $binding->markFrozen(true);

        return $this;
    }

    /**
     * Re-register frozen binding
     */
    public function unfreezeBinding(Binding $binding): EventLoop
    {
        if (!$binding->isFrozen()) {
            return $this;
        }

        $this->{'register'.$binding->getType().'Binding'}($binding);
        $binding->markFrozen(false);

        return $this;
    }



    /**
     * Add cycle handler to loop
     */
    protected function registerCycleHandler(?callable $callback): void
    {
        if ($this->cycleHandlerEvent) {
            $this->cycleHandlerEvent->free();
            $this->cycleHandlerEvent = null;
        }

        if (!$callback) {
            return;
        }

        $this->cycleHandlerEvent = $this->registerEvent(
            null,
            EventLib::TIMEOUT | EventLib::PERSIST,
            1,
            function () {
                if (!$this->cycleHandler) {
                    return;
                }

                try {
                    $res = ($this->cycleHandler)(++$this->cycles, $this);
                } catch (\Throwable $e) {
                    $this->stop();
                    throw $e;
                }

                if ($res === false) {
                    $this->stop();
                    return;
                }

                $this->registerCycleHandler($this->cycleHandler);
            }
        );
    }



    /**
     * Register socket binding to event loop
     */
    protected function registerSocketBinding(SocketBinding $binding): void
    {
        $binding->resource = $this->registerEvent(
            $binding->socket->getResource(),
            $this->getIoEventFlags($binding),
            $this->getTimeout($binding),
            function ($target, $flags, SocketBinding $binding) {
                try {
                    if ($flags & EventLib::TIMEOUT) {
                        $binding->triggerTimeout($target);
                    } else {
                        $binding->trigger($target);
                    }
                } catch (\Throwable $e) {
                    $this->stop();
                    throw $e;
                }

                if (!$binding->persistent) {
                    $this->unregisterSocketBinding($binding);
                }
            },
            $binding
        );
    }

    /**
     * Unregister socket binding to event loop
     */
    protected function unregisterSocketBinding(SocketBinding $binding): void
    {
        if ($binding->resource) {
            $binding->resource->free();
            $binding->resource = null;
        }
    }





    /**
     * Register stream binding to event loop
     */
    protected function registerStreamBinding(StreamBinding $binding): void
    {
        $binding->resource = $this->registerEvent(
            $binding->stream->getResource(),
            $this->getIoEventFlags($binding),
            $this->getTimeout($binding),
            function ($target, $flags, StreamBinding $binding) {
                try {
                    if ($flags & EventLib::TIMEOUT) {
                        $binding->triggerTimeout($target);
                    } else {
                        $binding->trigger($target);
                    }
                } catch (\Throwable $e) {
                    $this->stop();
                    throw $e;
                }

                if (!$binding->persistent) {
                    $this->unregisterStreamBinding($binding);
                }
            },
            $binding
        );
    }

    /**
     * Unregister stream binding to event loop
     */
    protected function unregisterStreamBinding(StreamBinding $binding): void
    {
        if ($binding->resource) {
            $binding->resource->free();
            $binding->resource = null;
        }
    }



    /**
     * Register signal binding to event loop
     */
    protected function registerSignalBinding(SignalBinding $binding): void
    {
        $flags = EventLib::SIGNAL;

        if ($binding->persistent) {
            $flags |= EventLib::PERSIST;
        }

        foreach ($binding->signals as $number => $signal) {
            $binding->resource[$number] = $this->registerEvent(
                $number,
                $flags,
                null,
                function ($number, SignalBinding $binding) {
                    try {
                        $binding->trigger($number);
                    } catch (\Throwable $e) {
                        $this->stop();
                        throw $e;
                    }

                    $this->unregisterSignalBinding($binding);

                    if ($binding->persistent) {
                        $this->registerSignalBinding($binding);
                    }
                },
                $binding
            );
        }
    }

    /**
     * Unregister signal binding to event loop
     */
    protected function unregisterSignalBinding(SignalBinding $binding): void
    {
        foreach ($binding->resource as $number => $resource) {
            if (!$resource) {
                continue;
            }

            $resource->free();
            $binding->resource[$number] = null;
        }
    }



    /**
     * Register timer binding to event loop
     */
    protected function registerTimerBinding(TimerBinding $binding): void
    {
        $flags = EventLib::TIMEOUT;

        if ($binding->persistent) {
            $flags |= EventLib::PERSIST;
        }

        $binding->resource = $this->registerEvent(
            null,
            $flags,
            $binding->duration,
            function (TimerBinding $binding) {
                try {
                    $binding->trigger(null);
                } catch (\Throwable $e) {
                    $this->stop();
                    throw $e;
                }

                $this->unregisterTimerBinding($binding);

                if ($binding->persistent) {
                    $this->registerTimerBinding($binding);
                }
            },
            $binding
        );
    }

    /**
     * Unregister timer binding to event loop
     */
    protected function unregisterTimerBinding(TimerBinding $binding): void
    {
        if ($binding->resource) {
            $binding->resource->free();
            $binding->resource = null;
        }
    }



    /**
     * Register resource with event base
     */
    protected function registerEvent($target, int $flags, ?float $timeout, callable $callback, $arg=null): EventLib
    {
        if ($timeout <= 0) {
            $timeout = null;
        }

        if ($flags & EventLib::SIGNAL) {
            $event = EventLib::signal($this->base, $target, $callback, $arg);
        } elseif ($target === null) {
            $event = EventLib::timer($this->base, $callback, $arg);
        } else {
            $event = new EventLib($this->base, $target, $flags, $callback, $arg);
        }

        if ($timeout !== null) {
            $res = $event->add($timeout);
        } else {
            $res = $event->add();
        }

        if (!$res) {
            $event->free();

            throw Glitch::{'EBinding,ERuntime'}(
                'Could not add event'
            );
        }

        return $event;
    }


    /**
     * Get read / write flags
     */
    protected function getIoEventFlags(IoBinding $binding): int
    {
        switch ($type = $binding->getIoMode()) {
            case 'r':
                $flags = EventLib::READ;
                break;

            case 'w':
                $flags = EventLib::WRITE;
                break;

            default:
                throw Glitch::EInvalidArgument(
                    'Unknown event io type: '.$type
                );
        }

        if ($binding->isPersistent()) {
            $flags |= EventLib::PERSIST;
        }

        return $flags;
    }

    /**
     * Get timeout duration
     */
    protected function getTimeout(Binding $binding): ?float
    {
        if ($binding instanceof IoBinding) {
            return $binding->getTimeout();
        } else {
            return null;
        }
    }
}
