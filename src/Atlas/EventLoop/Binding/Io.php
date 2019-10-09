<?php
/**
 * This file is part of the Atlas package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Atlas\EventLoop\Binding;

use DecodeLabs\Atlas\EventLoop\Binding;

interface Io extends Binding
{
    public function getIoMode(): string;
    public function getIoResource();

    public function getTimeout(): ?float;
    public function getTimeoutHandler(): ?callable;
    public function triggerTimeout($targetResource): Io;
}
