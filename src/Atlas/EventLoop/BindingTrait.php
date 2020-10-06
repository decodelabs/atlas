<?php

/**
 * @package Atlas
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Atlas\EventLoop;

use DecodeLabs\Atlas\EventLoop;

trait BindingTrait
{
    public $id;
    public $persistent = true;
    public $frozen = false;
    public $handler;
    public $resource;
    public $eventLoop;

    /**
     * Init with ref to event loop, id, options and handler
     */
    public function __construct(EventLoop $eventLoop, string $id, bool $persistent, callable $handler)
    {
        $this->id = $id;
        $this->persistent = $persistent;
        $this->handler = $handler;
        $this->eventLoop = $eventLoop;
    }

    /**
     * Get designated id for type
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Will this binding persist after use?
     */
    public function isPersistent(): bool
    {
        return $this->persistent;
    }

    /**
     * Get handler callback
     */
    public function getHandler(): callable
    {
        return $this->handler;
    }

    /**
     * Get parent event loop
     */
    public function getEventLoop(): EventLoop
    {
        return $this->eventLoop;
    }


    /**
     * Set event lib resource
     */
    public function setEventResource($resource): Binding
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * Get event lib resource
     */
    public function getEventResource()
    {
        return $this->resource;
    }


    /**
     * Freeze this binding
     */
    public function freeze(): Binding
    {
        $this->eventLoop->freezeBinding($this);
        return $this;
    }

    /**
     * Unfreeze this binding
     */
    public function unfreeze(): Binding
    {
        $this->eventLoop->unfreezeBinding($this);
        return $this;
    }

    /**
     * Toggle freezing
     */
    public function setFrozen(bool $frozen): Binding
    {
        if ($frozen) {
            $this->freeze();
        } else {
            $this->unfreeze();
        }

        return $this;
    }

    /**
     * Actually mark this binding as frozen - should only be used internally
     */
    public function markFrozen(bool $frozen): Binding
    {
        $this->frozen = $frozen;
        return $this;
    }

    /**
     * Has this binding been frozen?
     */
    public function isFrozen(): bool
    {
        return $this->frozen;
    }
}
