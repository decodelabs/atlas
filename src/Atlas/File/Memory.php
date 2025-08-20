<?php

/**
 * @package Atlas
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Atlas\File;

use DecodeLabs\Atlas\Dir;
use DecodeLabs\Atlas\File;
use DecodeLabs\Atlas\Mode;
use DecodeLabs\Exceptional;

class Memory extends Local
{
    public static function create(
        string $key = 'temp'
    ): Memory {
        if (!$ioResource = fopen('php://' . $key, 'w+b')) {
            throw Exceptional::Runtime(
                message: 'Unable to open memory stream'
            );
        }

        return new self($ioResource);
    }

    public function createLink(
        string $path
    ): Dir|File {
        throw Exceptional::Forbidden(
            message: 'Unable to create symbolic link to php://temp stream'
        );
    }


    public function gzOpen(
        string|Mode $mode
    ): Gz {
        throw Exceptional::Runtime(
            message: 'Memory file cannot be opened in GZ mode'
        );
    }

    public function getLastModified(): ?int
    {
        return time();
    }

    public function isOnDisk(): bool
    {
        return false;
    }

    public function getSize(): ?int
    {
        if (!$this->ioResource) {
            return null;
        }

        $pos = $this->getPosition();
        $this->movePosition(0, true);

        $output = $this->getPosition();

        if ($output !== $pos) {
            $this->setPosition($pos);
        }

        return $output;
    }

    public function getParent(): ?Dir
    {
        return null;
    }

    public function lock(
        bool $nonBlocking = false
    ): bool {
        if ($this->ioResource === null) {
            throw Exceptional::Io(
                message: 'Cannot lock file, file not open',
                data: $this
            );
        }

        return true;
    }

    public function lockExclusive(
        bool $nonBlocking = false
    ): bool {
        if ($this->ioResource === null) {
            throw Exceptional::Io(
                message: 'Cannot lock file, file not open',
                data: $this
            );
        }

        return true;
    }

    public function unlock(): File
    {
        return $this;
    }


    public function move(
        string $path
    ): File {
        $output = $this->copy($path);
        $this->close();
        return $output;
    }


    public function delete(): void
    {
        $this->close();
    }
}
