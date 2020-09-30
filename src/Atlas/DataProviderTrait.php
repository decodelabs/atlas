<?php
/**
 * This file is part of the Atlas package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Atlas;

use DecodeLabs\Atlas\DataProvider;
use DecodeLabs\Atlas\DataReceiver;
use DecodeLabs\Atlas\Channel;

use DecodeLabs\Exceptional;

trait DataProviderTrait
{
    /**
     * Set read blocking mode
     */
    public function setReadBlocking(bool $flag): DataProvider
    {
        if ($flag) {
            throw Exceptional::Runtime(
                'DataProvider does not support blocking mode'
            );
        }

        return $this;
    }

    /**
     * Is this channel in blocking mode?
     */
    public function isReadBlocking(): bool
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
        if (!$this->isReadable()) {
            throw Exceptional::Runtime(
                'Reading has been shut down'
            );
        }
    }



    /**
     * Read all available data from resource
     */
    public function readAll(): ?string
    {
        $this->checkReadable();
        $data = null;

        while (!$this->isAtEnd()) {
            $chunk = $this->read(8192);

            if ($chunk === null) {
                break;
            }

            $data .= $chunk;
        }

        return $data;
    }

    /**
     * Transfer available data to a write instance
     */
    public function readTo(DataReceiver $writer): DataProvider
    {
        $this->checkReadable();

        while (!$this->isAtEnd()) {
            $chunk = $this->read(8192);

            if ($chunk === null) {
                break;
            }

            $writer->write($chunk);
        }

        return $this;
    }
}
