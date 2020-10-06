<?php

/**
 * @package Atlas
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Atlas;

use DecodeLabs\Atlas\Channel\Stream;
use DecodeLabs\Atlas\EventLoop\Binding;
use DecodeLabs\Atlas\EventLoop\Binding\Signal as SignalBinding;
use DecodeLabs\Atlas\EventLoop\Binding\Socket as SocketBinding;
use DecodeLabs\Atlas\EventLoop\Binding\Stream as StreamBinding;
use DecodeLabs\Atlas\EventLoop\Binding\Timer as TimerBinding;

use DecodeLabs\Systemic\Process\Signal;

interface EventLoop
{
    public function listen(): EventLoop;
    public function isListening(): bool;
    public function stop(): EventLoop;

    // Global
    public function freezeBinding(Binding $binding): EventLoop;
    public function unfreezeBinding(Binding $binding): EventLoop;

    public function freezeAllBindings(): EventLoop;
    public function unfreezeAllBindings(): EventLoop;
    public function removeAllBindings(): EventLoop;
    public function getAllBindings(): array;
    public function countAllBindings(): int;

    public function setCycleHandler(?callable $callback = null): EventLoop;
    public function getCycleHandler(): ?callable;


    // Socket
    public function bindSocketRead(Socket $socket, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): EventLoop;
    public function bindFrozenSocketRead(Socket $socket, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): EventLoop;
    public function bindSocketReadOnce(Socket $socket, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): EventLoop;
    public function bindFrozenSocketReadOnce(Socket $socket, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): EventLoop;
    public function bindSocketWrite(Socket $socket, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): EventLoop;
    public function bindFrozenSocketWrite(Socket $socket, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): EventLoop;
    public function bindSocketWriteOnce(Socket $socket, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): EventLoop;
    public function bindFrozenSocketWriteOnce(Socket $socket, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): EventLoop;

    public function freezeSocket(Socket $socket): EventLoop;
    public function freezeSocketRead(Socket $socket): EventLoop;
    public function freezeSocketWrite(Socket $socket): EventLoop;
    public function freezeAllSockets(): EventLoop;

    public function unfreezeSocket(Socket $socket): EventLoop;
    public function unfreezeSocketRead(Socket $socket): EventLoop;
    public function unfreezeSocketWrite(Socket $socket): EventLoop;
    public function unfreezeAllSockets(): EventLoop;

    public function removeSocket(Socket $socket): EventLoop;
    public function removeSocketRead(Socket $socket): EventLoop;
    public function removeSocketWrite(Socket $socket): EventLoop;
    public function removeSocketBinding(SocketBinding $binding): EventLoop;
    public function removeAllSockets(): EventLoop;

    public function countSocketBindings(): int;
    public function countSocketBindingsFor(Socket $socket): int;
    public function getSocketBindings(): array;
    public function getSocketBindingsFor(Socket $socket): array;
    public function countSocketReadBindings(): int;
    public function getSocketReadBindings(): array;
    public function countSocketWriteBindings(): int;
    public function getSocketWriteBindings(): array;


    // Stream
    public function bindStreamRead(Stream $stream, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): EventLoop;
    public function bindFrozenStreamRead(Stream $stream, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): EventLoop;
    public function bindStreamReadOnce(Stream $stream, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): EventLoop;
    public function bindFrozenStreamReadOnce(Stream $stream, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): EventLoop;
    public function bindStreamWrite(Stream $stream, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): EventLoop;
    public function bindFrozenStreamWrite(Stream $stream, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): EventLoop;
    public function bindStreamWriteOnce(Stream $stream, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): EventLoop;
    public function bindFrozenStreamWriteOnce(Stream $stream, callable $callback, ?float $timeout = null, ?callable $timeoutHandler = null): EventLoop;

    public function freezeStream(Stream $stream): EventLoop;
    public function freezeStreamRead(Stream $stream): EventLoop;
    public function freezeStreamWrite(Stream $stream): EventLoop;
    public function freezeAllStreams(): EventLoop;

    public function unfreezeStream(Stream $stream): EventLoop;
    public function unfreezeStreamRead(Stream $stream): EventLoop;
    public function unfreezeStreamWrite(Stream $stream): EventLoop;
    public function unfreezeAllStreams(): EventLoop;

    public function removeStream(Stream $stream): EventLoop;
    public function removeStreamRead(Stream $stream): EventLoop;
    public function removeStreamWrite(Stream $stream): EventLoop;
    public function removeStreamBinding(StreamBinding $binding): EventLoop;
    public function removeAllStreams(): EventLoop;

    public function countStreamBindings(): int;
    public function countStreamBindingsFor(Stream $stream): int;
    public function getStreamBindings(): array;
    public function getStreamBindingsFor(Stream $stream): array;
    public function countStreamReadBindings(): int;
    public function getStreamReadBindings(): array;
    public function countStreamWriteBindings(): int;
    public function getStreamWriteBindings(): array;


    // Signal
    public function bindSignal(string $id, $signals, callable $callback): EventLoop;
    public function bindFrozenSignal(string $id, $signals, callable $callback): EventLoop;
    public function bindSignalOnce(string $id, $signals, callable $callback): EventLoop;
    public function bindFrozenSignalOnce(string $id, $signals, callable $callback): EventLoop;

    public function freezeSignal($signal): EventLoop;
    public function freezeSignalBinding($binding): EventLoop;
    public function freezeAllSignals(): EventLoop;

    public function unfreezeSignal($signal): EventLoop;
    public function unfreezeSignalBinding($binding): EventLoop;
    public function unfreezeAllSignals(): EventLoop;

    public function removeSignal($signal): EventLoop;
    public function removeSignalBinding($binding): EventLoop;
    public function removeAllSignals(): EventLoop;

    public function getSignalBinding($id): ?SignalBinding;
    public function countSignalBindings(): int;
    public function countSignalBindingsFor($signal): int;
    public function getSignalBindings(): array;
    public function getSignalBindingsFor($signal): array;


    // Timer
    public function bindTimer(string $id, float $duration, callable $callback): EventLoop;
    public function bindFrozenTimer(string $id, float $duration, callable $callback): EventLoop;
    public function bindTimerOnce(string $id, float $duration, callable $callback): EventLoop;
    public function bindFrozenTimerOnce(string $id, float $duration, callable $callback): EventLoop;

    public function freezeTimer($id): EventLoop;
    public function freezeAllTimers(): EventLoop;

    public function unfreezeTimer($id): EventLoop;
    public function unfreezeAllTimers(): EventLoop;

    public function removeTimer($id): EventLoop;
    public function removeAllTimers(): EventLoop;

    public function getTimerBinding($id): ?TimerBinding;
    public function countTimerBindings(): int;
    public function getTimerBindings(): array;
}
