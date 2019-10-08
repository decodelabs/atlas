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

    public function getTimeoutDuration(): ?DateInterval;
    public function getTimeoutHandler(): ?callable;
    public function triggerTimeout($targetResource): Io;
}
