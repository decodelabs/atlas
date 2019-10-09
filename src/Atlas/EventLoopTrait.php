<?php
/**
 * This file is part of the Atlas package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Atlas;

use DecodeLabs\Atlas\Socket;
use DecodeLabs\Atlas\Channel\Stream;
use DecodeLabs\Systemic\Process\Signal;

use DecodeLabs\Atlas\EventLoop\Binding\Socket as SocketBinding;
use DecodeLabs\Atlas\EventLoop\Binding\Stream as StreamBinding;
use DecodeLabs\Atlas\EventLoop\Binding\Signal as SignalBinding;
use DecodeLabs\Atlas\EventLoop\Binding\Timer as TimerBinding;

trait EventLoopTrait
{
    protected $listening = false;
    protected $cycleHandler;

    protected $sockets = [];
    protected $streams = [];
    protected $signals = [];
    protected $timers = [];


    /**
     * Has the event loop been started?
     */
    public function isListening(): bool
    {
        return $this->listening;
    }


    /**
     * Freeze all registered bindings
     */
    public function freezeAllBindings(): EventLoop
    {
        $this->freezeAllSockets();
        $this->freezeAllStreams();
        $this->freezeAllSignals();
        $this->freezeAllTimers();

        return $this;
    }

    /**
     * Unfreeze all registered bindings
     */
    public function unfreezeAllBindings(): EventLoop
    {
        $this->unfreezeAllSockets();
        $this->unfreezeAllStreams();
        $this->unfreezeAllSignals();
        $this->unfreezeAllTimers();

        return $this;
    }

    /**
     * Remove all registered bindings
     */
    public function removeAllBindings(): EventLoop
    {
        $this->removeAllSockets();
        $this->removeAllStreams();
        $this->removeAllSignals();
        $this->removeAllTimers();

        return $this;
    }

    /**
     * Get combined list of all bindings
     */
    public function getAllBindings(): array
    {
        return array_merge(
            array_values($this->sockets),
            array_values($this->streams),
            array_values($this->signals),
            array_values($this->timers)
        );
    }

    /**
     * Count all registered bindings
     */
    public function countAllBindings(): int
    {
        return count($this->sockets)
             + count($this->streams)
             + count($this->signals)
             + count($this->timers);
    }



    /**
     * Register generic callback for each cycle
     */
    public function setCycleHandler(?callable $callback=null): EventLoop
    {
        $this->cycleHandler = $callback;
        return $this;
    }

    /**
     * Get registered cycle callback
     */
    public function getCycleHandler(): ?callable
    {
        return $this->cycleHandler;
    }



    /**
     * Bind to socket read event
     */
    public function bindSocketRead(Socket $socket, callable $callback, ?float $timeout=null, ?callable $timeoutCallback=null): EventLoop
    {
        return $this->addSocketBinding(new SocketBinding(
            $this, true, $socket, 'r', $callback, $timeout, $timeoutCallback
        ), false);
    }

    /**
     * Bind to socket read event, frozen
     */
    public function bindFrozenSocketRead(Socket $socket, callable $callback, ?float $timeout=null, ?callable $timeoutCallback=null): EventLoop
    {
        return $this->addSocketBinding(new SocketBinding(
            $this, true, $socket, 'r', $callback, $timeout, $timeoutCallback
        ), true);
    }

    /**
     * Bind to single socket read event
     */
    public function bindSocketReadOnce(Socket $socket, callable $callback, ?float $timeout=null, ?callable $timeoutCallback=null): EventLoop
    {
        return $this->addSocketBinding(new SocketBinding(
            $this, false, $socket, 'r', $callback, $timeout, $timeoutCallback
        ), false);
    }

    /**
     * Bind to single socket read event, frozen
     */
    public function bindFrozenSocketReadOnce(Socket $socket, callable $callback, ?float $timeout=null, ?callable $timeoutCallback=null): EventLoop
    {
        return $this->addSocketBinding(new SocketBinding(
            $this, false, $socket, 'r', $callback, $timeout, $timeoutCallback
        ), true);
    }

    /**
     * Bind to socket write event
     */
    public function bindSocketWrite(Socket $socket, callable $callback, ?float $timeout=null, ?callable $timeoutCallback=null): EventLoop
    {
        return $this->addSocketBinding(new SocketBinding(
            $this, true, $socket, 'w', $callback, $timeout, $timeoutCallback
        ), false);
    }

    /**
     * Bind to socket write event, frozen
     */
    public function bindFrozenSocketWrite(Socket $socket, callable $callback, ?float $timeout=null, ?callable $timeoutCallback=null): EventLoop
    {
        return $this->addSocketBinding(new SocketBinding(
            $this, true, $socket, 'w', $callback, $timeout, $timeoutCallback
        ), true);
    }

    /**
     * Bind to single socket write event
     */
    public function bindSocketWriteOnce(Socket $socket, callable $callback, ?float $timeout=null, ?callable $timeoutCallback=null): EventLoop
    {
        return $this->addSocketBinding(new SocketBinding(
            $this, false, $socket, 'w', $callback, $timeout, $timeoutCallback
        ), false);
    }

    /**
     * Bind to single socket read event, frozen
     */
    public function bindFrozenSocketWriteOnce(Socket $socket, callable $callback, ?float $timeout=null, ?callable $timeoutCallback=null): EventLoop
    {
        return $this->addSocketBinding(new SocketBinding(
            $this, false, $socket, 'w', $callback, $timeout, $timeoutCallback
        ), true);
    }

    /**
     * Register a socket binding
     */
    protected function addSocketBinding(SocketBinding $binding, bool $frozen): EventLoop
    {
        $id = $binding->getId();

        if (isset($this->sockets[$id])) {
            $this->removeSocketBinding($binding);
        }

        $this->sockets[$id] = $binding;

        if ($frozen) {
            $binding->setFrozen(true);
        } else {
            $this->registerSocketBinding($binding);
        }

        return $this;
    }

    abstract protected function registerSocketBinding(SocketBinding $binding): void;
    abstract protected function unregisterSocketBinding(SocketBinding $binding): void;



    /**
     * Freeze all bindings for socket
     */
    public function freezeSocket(Socket $socket): EventLoop
    {
        $id = $socket->getId();

        if (isset($this->sockets['r:'.$id])) {
            $this->freezeBinding($this->sockets['r:'.$id]);
        }

        if (isset($this->sockets['w:'.$id])) {
            $this->freezeBinding($this->sockets['w:'.$id]);
        }

        return $this;
    }

    /**
     * Freeze read bindings for socket
     */
    public function freezeSocketRead(Socket $socket): EventLoop
    {
        $id = $socket->getId();

        if (isset($this->sockets['r:'.$id])) {
            $this->freezeBinding($this->sockets['r:'.$id]);
        }

        return $this;
    }

    /**
     * Freeze write bindings for socket
     */
    public function freezeSocketWrite(Socket $socket): EventLoop
    {
        $id = $socket->getId();

        if (isset($this->sockets['w:'.$id])) {
            $this->freezeBinding($this->sockets['w:'.$id]);
        }

        return $this;
    }

    /**
     * Freeze all socket bindings
     */
    public function freezeAllSockets(): EventLoop
    {
        foreach ($this->sockets as $id => $binding) {
            $this->freezeBinding($binding);
        }

        return $this;
    }



    /**
     * Unfreeze all bindings for socket
     */
    public function unfreezeSocket(Socket $socket): EventLoop
    {
        $id = $socket->getId();

        if (isset($this->sockets['r:'.$id])) {
            $this->unfreezeBinding($this->sockets['r:'.$id]);
        }

        if (isset($this->sockets['w:'.$id])) {
            $this->unfreezeBinding($this->sockets['w:'.$id]);
        }

        return $this;
    }

    /**
     * Unfreeze bindings for socket reads
     */
    public function unfreezeSocketRead(Socket $socket): EventLoop
    {
        $id = $socket->getId();

        if (isset($this->sockets['r:'.$id])) {
            $this->unfreezeBinding($this->sockets['r:'.$id]);
        }

        return $this;
    }

    /**
     * Unfreeze bindings for socket writes
     */
    public function unfreezeSocketWrite(Socket $socket): EventLoop
    {
        $id = $socket->getId();

        if (isset($this->sockets['w:'.$id])) {
            $this->unfreezeBinding($this->sockets['w:'.$id]);
        }

        return $this;
    }

    /**
     * Unfreeze all socket bindings
     */
    public function unfreezeAllSockets(): EventLoop
    {
        foreach ($this->sockets as $id => $binding) {
            $this->unfreezeBinding($binding);
        }

        return $this;
    }



    /**
     * Remove all bindings for socket
     */
    public function removeSocket(Socket $socket): EventLoop
    {
        $id = $socket->getId();

        if (isset($this->sockets['r:'.$id])) {
            $this->removeSocketBinding($this->sockets['r:'.$id]);
        }

        if (isset($this->sockets['w:'.$id])) {
            $this->removeSocketBinding($this->sockets['w:'.$id]);
        }

        return $this;
    }

    /**
     * Remove bindings for socket read
     */
    public function removeSocketRead(Socket $socket): EventLoop
    {
        $id = $socket->getId();

        if (isset($this->sockets['r:'.$id])) {
            $this->removeSocketBinding($this->sockets['r:'.$id]);
        }

        return $this;
    }

    /**
     * Remove bindings for socket write
     */
    public function removeSocketWrite(Socket $socket): EventLoop
    {
        $id = $socket->getId();

        if (isset($this->sockets['w:'.$id])) {
            $this->removeSocketBinding($this->sockets['w:'.$id]);
        }

        return $this;
    }

    /**
     * Remove specific socket binding
     */
    public function removeSocketBinding(SocketBinding $binding): EventLoop
    {
        $this->unregisterSocketBinding($binding);
        unset($this->sockets[$binding->getId()]);

        return $this;
    }

    /**
     * Remove all socket bindings
     */
    public function removeAllSockets(): EventLoop
    {
        foreach ($this->sockets as $id => $binding) {
            $this->unregisterSocketBinding($binding);
            unset($this->sockets[$id]);
        }

        return $this;
    }


    /**
     * Count all socket bindings
     */
    public function countSocketBindings(): int
    {
        return count($this->sockets);
    }

    /**
     * Count all bindings for socket
     */
    public function countSocketBindingsFor(Socket $socket): int
    {
        $count = 0;
        $id = $socket->getId();

        if (isset($this->sockets['r:'.$id])) {
            $count++;
        }

        if (isset($this->sockets['w:'.$id])) {
            $count++;
        }

        return $count;
    }

    /**
     * Get all socket bindings
     */
    public function getSocketBindings(): array
    {
        return $this->sockets;
    }

    /**
     * Get all bindings for socket
     */
    public function getSocketBindingsFor(Socket $socket): array
    {
        $output = [];
        $id = $socket->getId();

        if (isset($this->sockets['r:'.$id])) {
            $output['r:'.$id] = $this->sockets['r:'.$id];
        }

        if (isset($this->sockets['w:'.$id])) {
            $output['w:'.$id] = $this->sockets['w:'.$id];
        }

        return $output;
    }

    /**
     * Count all bindings for socket read
     */
    public function countSocketReadBindings(): int
    {
        $count = 0;

        foreach ($this->sockets as $binding) {
            if ($binding->getIoMode() == 'r') {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get all bindings for socket read
     */
    public function getSocketReadBindings(): array
    {
        $output = [];

        foreach ($this->sockets as $id => $binding) {
            if ($binding->getIoMode() == 'r') {
                $output[$id] = $binding;
            }
        }

        return $output;
    }

    /**
     * Count all bindings for socket write
     */
    public function countSocketWriteBindings(): int
    {
        $count = 0;

        foreach ($this->sockets as $binding) {
            if ($binding->getIoMode() == 'w') {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get all bindings for socket write
     */
    public function getSocketWriteBindings(): array
    {
        $output = [];

        foreach ($this->sockets as $id => $binding) {
            if ($binding->getIoMode() == 'w') {
                $output[$id] = $binding;
            }
        }

        return $output;
    }




    /**
     * Bind to stream read event
     */
    public function bindStreamRead(Stream $stream, callable $callback, ?float $timeout=null, ?callable $timeoutCallback=null): EventLoop
    {
        return $this->addStreamBinding(new StreamBinding(
            $this, true, $socket, 'r', $callback, $timeout, $timeoutCallback
        ), false);
    }

    /**
     * Bind to stream read event, frozen
     */
    public function bindFrozenStreamRead(Stream $stream, callable $callback, ?float $timeout=null, ?callable $timeoutCallback=null): EventLoop
    {
        return $this->addStreamBinding(new StreamBinding(
            $this, true, $socket, 'r', $callback, $timeout, $timeoutCallback
        ), true);
    }

    /**
     * Bind to single stream read event
     */
    public function bindStreamReadOnce(Stream $stream, callable $callback, ?float $timeout=null, ?callable $timeoutCallback=null): EventLoop
    {
        return $this->addStreamBinding(new StreamBinding(
            $this, false, $socket, 'r', $callback, $timeout, $timeoutCallback
        ), false);
    }

    /**
     * Bind to single stream read event, frozen
     */
    public function bindFrozenStreamReadOnce(Stream $stream, callable $callback, ?float $timeout=null, ?callable $timeoutCallback=null): EventLoop
    {
        return $this->addStreamBinding(new StreamBinding(
            $this, false, $socket, 'r', $callback, $timeout, $timeoutCallback
        ), true);
    }

    /**
     * Bind to socket write event
     */
    public function bindStreamWrite(Stream $stream, callable $callback, ?float $timeout=null, ?callable $timeoutCallback=null): EventLoop
    {
        return $this->addStreamBinding(new StreamBinding(
            $this, true, $socket, 'w', $callback, $timeout, $timeoutCallback
        ), false);
    }

    /**
     * Bind to socket write event, frozen
     */
    public function bindFrozenStreamWrite(Stream $stream, callable $callback, ?float $timeout=null, ?callable $timeoutCallback=null): EventLoop
    {
        return $this->addStreamBinding(new StreamBinding(
            $this, true, $socket, 'w', $callback, $timeout, $timeoutCallback
        ), true);
    }

    /**
     * Bind to single socket write event
     */
    public function bindStreamWriteOnce(Stream $stream, callable $callback, ?float $timeout=null, ?callable $timeoutCallback=null): EventLoop
    {
        return $this->addStreamBinding(new StreamBinding(
            $this, false, $socket, 'w', $callback, $timeout, $timeoutCallback
        ), false);
    }

    /**
     * Bind to single socket read event, frozen
     */
    public function bindFrozenStreamWriteOnce(Stream $stream, callable $callback, ?float $timeout=null, ?callable $timeoutCallback=null): EventLoop
    {
        return $this->addStreamBinding(new StreamBinding(
            $this, false, $socket, 'w', $callback, $timeout, $timeoutCallback
        ), true);
    }

    /**
     * Register a stream binding
     */
    protected function addStreamBinding(StreamBinding $binding, bool $frozen): EventLoop
    {
        $id = $binding->getId();

        if (isset($this->streams[$id])) {
            $this->removeStream($binding);
        }

        $this->streams[$id] = $binding;

        if ($frozen) {
            $binding->setFrozen(true);
        } else {
            $this->registerStreamBinding($binding);
        }

        return $this;
    }

    abstract protected function registerStreamBinding(StreamBinding $binding): void;
    abstract protected function unregisterStreamBinding(StreamBinding $binding): void;


    /**
     * Freeze all bindings for stream
     */
    public function freezeStream(Stream $stream): EventLoop
    {
        $id = $this->getStreamId($stream);

        if (isset($this->streams['r:'.$id])) {
            $this->freezeBinding($this->streams['r:'.$id]);
        }

        if (isset($this->streams['w:'.$id])) {
            $this->freezeBinding($this->streams['w:'.$id]);
        }

        return $this;
    }

    /**
     * Freeze read bindings for stream
     */
    public function freezeStreamRead(Stream $stream): EventLoop
    {
        $id = $this->getStreamId($stream);

        if (isset($this->streams['r:'.$id])) {
            $this->freezeBinding($this->streams['r:'.$id]);
        }

        return $this;
    }

    /**
     * Freeze write bindings for stream
     */
    public function freezeStreamWrite(Stream $stream): EventLoop
    {
        $id = $this->getStreamId($stream);

        if (isset($this->streams['w:'.$id])) {
            $this->freezeBinding($this->streams['w:'.$id]);
        }

        return $this;
    }

    /**
     * Freeze all stream bindings
     */
    public function freezeAllStreams(): EventLoop
    {
        foreach ($this->streams as $id => $binding) {
            $this->freezeBinding($binding);
        }

        return $this;
    }


    /**
     * Unfreeze all bindings for stream
     */
    public function unfreezeStream(Stream $stream): EventLoop
    {
        $id = $this->getStreamId($stream);

        if (isset($this->streams['r:'.$id])) {
            $this->unfreezeBinding($this->streams['r:'.$id]);
        }

        if (isset($this->streams['w:'.$id])) {
            $this->unfreezeBinding($this->streams['w:'.$id]);
        }

        return $this;
    }

    /**
     * Unfreeze bindings for stream reads
     */
    public function unfreezeStreamRead(Stream $stream): EventLoop
    {
        $id = $this->getStreamId($stream);

        if (isset($this->streams['r:'.$id])) {
            $this->unfreezeBinding($this->streams['r:'.$id]);
        }

        return $this;
    }

    /**
     * Unfreeze bindings for stream writes
     */
    public function unfreezeStreamWrite(Stream $stream): EventLoop
    {
        $id = $this->getStreamId($stream);

        if (isset($this->streams['w:'.$id])) {
            $this->unfreezeBinding($this->streams['w:'.$id]);
        }

        return $this;
    }

    /**
     * Unfreeze all stream bindings
     */
    public function unfreezeAllStreams(): EventLoop
    {
        foreach ($this->streams as $id => $binding) {
            $this->unfreezeBinding($binding);
        }

        return $this;
    }



    /**
     * Remove all bindings for stream
     */
    public function removeStream(Stream $stream): EventLoop
    {
        $id = $this->getStreamId($stream);

        if (isset($this->streams['r:'.$id])) {
            $this->removeStreamBinding($this->streams['r:'.$id]);
        }

        if (isset($this->streams['w:'.$id])) {
            $this->removeStreamBinding($this->streams['w:'.$id]);
        }

        return $this;
    }

    /**
     * Remove bindings for stream read
     */
    public function removeStreamRead(Stream $stream): EventLoop
    {
        $id = $this->getStreamId($stream);

        if (isset($this->streams['r:'.$id])) {
            $this->removeStreamBinding($this->streams['r:'.$id]);
        }

        return $this;
    }

    /**
     * Remove bindings for stream write
     */
    public function removeStreamWrite(Stream $stream): EventLoop
    {
        $id = $this->getStreamId($stream);

        if (isset($this->streams['w:'.$id])) {
            $this->removeStreamBinding($this->streams['w:'.$id]);
        }

        return $this;
    }

    /**
     * Remove specific stream binding
     */
    public function removeStreamBinding(StreamBinding $binding): EventLoop
    {
        $this->unregisterStreamBinding($binding);
        unset($this->streams[$binding->getId()]);

        return $this;
    }

    /**
     * Remove all stream bindings
     */
    public function removeAllStreams(): EventLoop
    {
        foreach ($this->streams as $id => $binding) {
            $this->unregisterStreamBinding($binding);
            unset($this->streams[$id]);
        }

        return $this;
    }


    /**
     * Count all stream bindings
     */
    public function countStreamBindings(): int
    {
        return count($this->streams);
    }

    /**
     * Count all bindings for stream
     */
    public function countStreamBindingsFor(Stream $stream): int
    {
        $count = 0;
        $id = $this->getStreamId($stream);

        if (isset($this->streams['r:'.$id])) {
            $count++;
        }

        if (isset($this->streams['w:'.$id])) {
            $count++;
        }

        return $count;
    }

    /**
     * Get all stream bindings
     */
    public function getStreamBindings(): array
    {
        return $this->streams;
    }

    /**
     * Get all bindings for stream
     */
    public function getStreamBindingsFor(Stream $stream): array
    {
        $output = [];
        $id = $this->getStreamId($stream);

        if (isset($this->streams['r:'.$id])) {
            $output['r:'.$id] = $this->streams['r:'.$id];
        }

        if (isset($this->streams['w:'.$id])) {
            $output['w:'.$id] = $this->streams['w:'.$id];
        }

        return $output;
    }

    /**
     * Count all bindings for stream read
     */
    public function countStreamReadBindings(): int
    {
        $count = 0;

        foreach ($this->streams as $binding) {
            if ($binding->getIoMode() == 'r') {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get all bindings for stream read
     */
    public function getStreamReadBindings(): array
    {
        $output = [];

        foreach ($this->streams as $id => $binding) {
            if ($binding->getIoMode() == 'r') {
                $output[$id] = $binding;
            }
        }

        return $output;
    }

    /**
     * Count all bindings for socket write
     */
    public function countStreamWriteBindings(): int
    {
        $count = 0;

        foreach ($this->streams as $binding) {
            if ($binding->getIoMode() == 'w') {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get all bindings for socket write
     */
    public function getStreamWriteBindings(): array
    {
        $output = [];

        foreach ($this->streams as $id => $binding) {
            if ($binding->getIoMode() == 'w') {
                $output[$id] = $binding;
            }
        }

        return $output;
    }

    /**
     * Get id for stream
     */
    protected function getStreamId(Stream $stream): string
    {
        return spl_object_id($stream);
    }




    /**
     * Bind to signal event
     */
    public function bindSignal(string $id, $signals, callable $callback): EventLoop
    {
        return $this->addSignalBinding(new SignalBinding(
            $this, $id, true, $signals, $callback
        ), false);
    }

    /**
     * Bind to signal event, frozen
     */
    public function bindFrozenSignal(string $id, $signals, callable $callback): EventLoop
    {
        return $this->addSignalBinding(new SignalBinding(
            $this, $id, true, $signals, $callback
        ), true);
    }

    /**
     * Bind to single signal event
     */
    public function bindSignalOnce(string $id, $signals, callable $callback): EventLoop
    {
        return $this->addSignalBinding(new SignalBinding(
            $this, $id, false, $signals, $callback
        ), false);
    }

    /**
     * Bind to single signal event, frozen
     */
    public function bindFrozenSignalOnce(string $id, $signals, callable $callback): EventLoop
    {
        return $this->addSignalBinding(new SignalBinding(
            $this, $id, false, $signals, $callback
        ), true);
    }

    /**
     * Register a signal binding
     */
    protected function addSignalBinding(SignalBinding $binding, bool $frozen): EventLoop
    {
        $id = $binding->getId();

        if (isset($this->signals[$id])) {
            $this->removeSignal($binding);
        }

        $this->signals[$id] = $binding;

        if ($frozen) {
            $binding->setFrozen(true);
        } else {
            $this->registerSignalBinding($binding);
        }

        return $this;
    }

    abstract protected function registerSignalBinding(SignalBinding $binding): void;
    abstract protected function unregisterSignalBinding(SignalBinding $binding): void;


    /**
     * Freeze all bindings with signal
     */
    public function freezeSignal($signal): EventLoop
    {
        $number = Systemic::$process->normalizeSignal($signal);

        foreach ($this->signals as $id => $binding) {
            if ($binding->hasSignal($number)) {
                $this->freezeBinding($binding);
            }
        }

        return $this;
    }

    /**
     * Freeze specific signal binding by object or id
     */
    public function freezeSignalBinding($binding): EventLoop
    {
        if (!$binding instanceof SignalBinding) {
            $orig = $binding;

            if (!$binding = $this->getSignalBinding($binding)) {
                throw Glitch::EInvalidArgument('Invalid signal binding', null, $orig);
            }
        }

        $this->freezeBinding($binding);
        return $this;
    }

    /**
     * Freeze all signal bindings
     */
    public function freezeAllSignals(): EventLoop
    {
        foreach ($this->signals as $id => $binding) {
            $this->freezeBinding($binding);
        }

        return $this;
    }


    /**
     * Unfreeze all bindings with signal
     */
    public function unfreezeSignal($signal): EventLoop
    {
        $number = Systemic::$process->normalizeSignal($signal);

        foreach ($this->signals as $id => $binding) {
            if ($binding->hasSignal($number)) {
                $this->unfreezeBinding($binding);
            }
        }

        return $this;
    }

    /**
     * Unfreeze specific signal binding by object or id
     */
    public function unfreezeSignalBinding($binding): EventLoop
    {
        if (!$binding instanceof SignalBinding) {
            $orig = $binding;

            if (!$binding = $this->getSignalBinding($binding)) {
                throw Glitch::EInvalidArgument('Invalid signal binding', null, $orig);
            }
        }

        $this->unfreezeBinding($binding);
        return $this;
    }

    /**
     * Unfreeze all signal bindings
     */
    public function unfreezeAllSignals(): EventLoop
    {
        foreach ($this->signals as $id => $binding) {
            $this->unfreezeBinding($binding);
        }

        return $this;
    }


    /**
     * Remove all bindings with signal
     */
    public function removeSignal($signal): EventLoop
    {
        $number = Systemic::$process->normalizeSignal($signal);

        foreach ($this->signals as $id => $binding) {
            if ($binding->hasSignal($number)) {
                $this->removeSignalBinding($binding);
            }
        }

        return $this;
    }

    /**
     * Remove specific signal binding
     */
    public function removeSignalBinding($binding): EventLoop
    {
        if (!$binding instanceof SignalBinding) {
            $orig = $binding;

            if (!$binding = $this->getSignalBinding($binding)) {
                throw Glitch::EInvalidArgument('Invalid signal binding', null, $orig);
            }
        }

        $id = $binding->getId();
        $this->unregisterSignalBinding($binding);
        unset($this->signals[$id]);

        return $this;
    }

    /**
     * Remove all signal bindings
     */
    public function removeAllSignals(): EventLoop
    {
        foreach ($this->signals as $id => $binding) {
            $this->unregisterSignalBinding($binding);
            unset($this->signals[$id]);
        }

        return $this;
    }



    /**
     * Get signal binding by id or object
     */
    public function getSignalBinding($id): ?SignalBinding
    {
        if ($id instanceof SignalBinding) {
            $id = $id->getId();
        }

        if (!is_string($id)) {
            throw Glitch::EInvalidArgument('Invalid signal id', null, $id);
        }

        return $this->signals[$id] ?? null;
    }

    /**
     * Count all signal bindings
     */
    public function countSignalBindings(): int
    {
        return count($this->signals);
    }

    /**
     * Count bindings with signal
     */
    public function countSignalBindingsFor($signal): int
    {
        $count = 0;
        $number = Systemic::$process->normalizeSignal($signal);

        foreach ($this->signals as $id => $binding) {
            if ($binding->hasSignal($number)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get all signal bindings
     */
    public function getSignalBindings(): array
    {
        return $this->signals;
    }

    /**
     * Get bidings with signal
     */
    public function getSignalBindingsFor($signal): array
    {
        $output = [];
        $number = Systemic::$process->normalizeSignal($signal);

        foreach ($this->signals as $id => $binding) {
            if ($binding->hasSignal($number)) {
                $output[$id] = $binding;
            }
        }

        return $output;
    }




    /**
     * Bind to a timer event
     */
    public function bindTimer(string $id, float $duration, callable $callback): EventLoop
    {
        return $this->addTimerBinding(new TimerBinding(
            $this, $id, true, $duration, $callback
        ), false);
    }

    /**
     * Bind to a timer event, frozen
     */
    public function bindFrozenTimer(string $id, float $duration, callable $callback): EventLoop
    {
        return $this->addTimerBinding(new TimerBinding(
            $this, $id, true, $duration, $callback
        ), true);
    }

    /**
     * Bind to a single timer event
     */
    public function bindTimerOnce(string $id, float $duration, callable $callback): EventLoop
    {
        return $this->addTimerBinding(new TimerBinding(
            $this, $id, false, $duration, $callback
        ), false);
    }

    /**
     * Bind to a single timer event, frozen
     */
    public function bindFrozenTimerOnce(string $id, float $duration, callable $callback): EventLoop
    {
        return $this->addTimerBinding(new TimerBinding(
            $this, $id, false, $duration, $callback
        ), true);
    }

    /**
     * Register a timing binding
     */
    protected function addTimerBinding(TimerBinding $binding, bool $frozen): EventLoop
    {
        $id = $binding->getId();

        if (isset($this->timers[$id])) {
            $this->removeTimer($binding);
        }

        $this->timers[$id] = $binding;

        if ($frozen) {
            $binding->setFrozen(true);
        } else {
            $this->registerTimerBinding($binding);
        }

        return $this;
    }

    abstract protected function registerTimerBinding(TimerBinding $binding): void;
    abstract protected function unregisterTimerBinding(TimerBinding $binding): void;


    /**
     * Freeze timer binding by id
     */
    public function freezeTimer($id): EventLoop
    {
        if (!$binding instanceof TimerBinding) {
            $orig = $binding;

            if (!$binding = $this->getTimerBinding($binding)) {
                throw Glitch::EInvalidArgument('Invalid timer binding', null, $orig);
            }
        }

        $this->freezeBinding($binding);
        return $this;
    }

    /**
     * Freeze all timer bindings
     */
    public function freezeAllTimers(): EventLoop
    {
        foreach ($this->timers as $id => $binding) {
            $this->freezeBinding($binding);
        }

        return $this;
    }


    /**
     * Unfreeze timer binding by id
     */
    public function unfreezeTimer($id): EventLoop
    {
        if (!$binding instanceof TimerBinding) {
            $orig = $binding;

            if (!$binding = $this->getTimerBinding($binding)) {
                throw Glitch::EInvalidArgument('Invalid timer binding', null, $orig);
            }
        }

        $this->unfreezeBinding($binding);
        return $this;
    }

    /**
     * Unfreeze all timer bindings
     */
    public function unfreezeAllTimers(): EventLoop
    {
        foreach ($this->timers as $id => $binding) {
            $this->unfreezeBinding($binding);
        }

        return $this;
    }


    /**
     * Remove a timer binding by id or object
     */
    public function removeTimer($id): EventLoop
    {
        if (!$binding instanceof TimerBinding) {
            $orig = $binding;

            if (!$binding = $this->getTimerBinding($binding)) {
                throw Glitch::EInvalidArgument('Invalid timer binding', null, $orig);
            }
        }

        $id = $binding->getId();
        $this->unregisterTimerBinding($binding);
        unset($this->signals[$id]);

        return $this;
    }

    /**
     * Remove all timer bindings
     */
    public function removeAllTimers(): EventLoop
    {
        foreach ($this->timers as $id => $binding) {
            $this->unregisterTimerBinding($binding);
            unset($this->timers[$id]);
        }

        return $this;
    }


    /**
     * Get signal binding by id or object
     */
    public function getTimerBinding($id): ?TimerBinding
    {
        if ($id instanceof TimerBinding) {
            $id = $id->getId();
        }

        if (!is_string($id)) {
            throw Glitch::EInvalidArgument('Invalid timer id', null, $id);
        }

        return $this->timers[$id] ?? null;
    }

    /**
     * Count all timer bindings
     */
    public function countTimerBindings(): int
    {
        return count($this->timers);
    }

    /**
     * Get all timer bindings
     */
    public function getTimerBindings(): array
    {
        return $this->timers;
    }
}
