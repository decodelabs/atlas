<?php
/**
 * This file is part of the Atlas package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Atlas\EventLoop\Binding;

use DecodeLabs\Atlas\EventLoop\Binding;

trait IoTrait
{
    public $ioMode = 'r';
    public $timeout;
    public $timeoutHandler;

    /**
     * Get whether binding is read or write
     */
    public function getIoMode(): string
    {
        return $this->ioMode;
    }


    /**
     * Get timeout duration
     */
    public function getTimeout(): ?float
    {
        return $this->timeout;
    }

    /**
     * Get timeout callback handler
     */
    public function getTimeoutHandler(): ?callable
    {
        return $this->timeoutHandler;
    }
}
