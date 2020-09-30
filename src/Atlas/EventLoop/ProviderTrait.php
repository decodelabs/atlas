<?php
/**
 * This file is part of the Atlas package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Atlas\EventLoop;

use DecodeLabs\Atlas;
use DecodeLabs\Atlas\EventLoop;

use DecodeLabs\Exceptional;

trait ProviderTrait
{
    protected $events;

    /**
     * Replace current active event loop
     */
    public function setEventLoop(EventLoop $eventLoop): Provider
    {
        if ($this->isRunning()) {
            throw Exceptional::Runtime(
                'You cannot change the event loop while it is running'
            );
        }

        $this->events = $eventLoop;
        return $this;
    }

    /**
     * Get current active event loop
     */
    public function getEventLoop(): EventLoop
    {
        if (!$this->events) {
            $this->events = Atlas::newEventLoop();
        }

        return $this->events;
    }

    /**
     * Check if event loop is running
     */
    public function isRunning(): bool
    {
        return $this->events && $this->events->isListening();
    }
}
