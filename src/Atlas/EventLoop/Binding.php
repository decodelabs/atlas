<?php

/**
 * @package Atlas
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Atlas\EventLoop;

use DecodeLabs\Atlas\EventLoop;

interface Binding
{
    public function getId(): string;
    public function getType(): string;
    public function isPersistent(): bool;
    public function getHandler(): callable;
    public function getEventLoop(): EventLoop;

    public function setEventResource($resource): Binding;
    public function getEventResource();

    public function freeze(): Binding;
    public function unfreeze(): Binding;
    public function setFrozen(bool $frozen): Binding;
    public function markFrozen(bool $frozen): Binding;
    public function isFrozen(): bool;
    public function destroy(): Binding;

    public function trigger($targetResource): Binding;
}
