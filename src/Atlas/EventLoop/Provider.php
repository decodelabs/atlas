<?php
/**
 * This file is part of the Atlas package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Atlas\EventLoop;

use DecodeLabs\Atlas\EventLoop;

interface Provider
{
    public function setEventLoop(EventLoop $eventLoop): Provider;
    public function getEventLoop(): EventLoop;
    public function isRunning(): bool;
}
