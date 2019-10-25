<?php
/**
 * This file is part of the Atlas package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Atlas;

use DecodeLabs\Atlas\DataProviderTrait;
use DecodeLabs\Atlas\DataReceiverTrait;
use DecodeLabs\Atlas\Channel\Buffer;

use DecodeLabs\Glitch;

trait ChannelTrait
{
    use DataProviderTrait;
    use DataReceiverTrait;

    /**
     * Set read blocking mode
     */
    public function setBlocking(bool $flag): Channel
    {
        if (!$flag) {
            throw Glitch::ERuntime('Channel does not support non-blocking mode');
        }
    }

    /**
     * Is this channel in blocking mode?
     */
    public function isBlocking(): bool
    {
        return true;
    }


    /**
     * Is the resource still accessible?
     */
    public function isReadable(): bool
    {
        return true;
    }


    /**
     * Check the resource is readable and throw exception if not
     */
    protected function checkReadable(): void
    {
        if (!$this->getResource() || !$this->isReadable()) {
            throw Glitch::ERuntime('Reading has been shut down');
        }
    }





    /**
     * Is the resource still writable?
     */
    public function isWritable(): bool
    {
        return true;
    }

    /**
     * Check the resource is readable and throw exception if not
     */
    protected function checkWritable(): void
    {
        if (!$this->getResource() || !$this->isWritable()) {
            throw Glitch::ERuntime('Writing has been shut down');
        }
    }
}
