<?php

/**
 * @package Atlas
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Atlas\File;

use DecodeLabs\Atlas\Dir;
use DecodeLabs\Atlas\File;

use DecodeLabs\Exceptional;

class Memory extends Local
{
    /**
     * Create from string key in php://
     */
    public static function create(string $key = 'temp'): Memory
    {
        if (!$resource = fopen('php://' . $key, 'w+b')) {
            throw Exceptional::Runtime('Unable to open memory stream');
        }

        return new self($resource);
    }

    /**
     * Create symbolic link
     */
    public function createLink(string $path): Dir|File
    {
        throw Exceptional::Forbidden(
            'Unable to create symbolic link to php://temp stream'
        );
    }

    /**
     * Get mtime of file
     */
    public function getLastModified(): ?int
    {
        return time();
    }

    /**
     * Can this file be read from disk
     */
    public function isOnDisk(): bool
    {
        return false;
    }

    /**
     * Get size of file in bytes
     */
    public function getSize(): ?int
    {
        if (!$this->resource) {
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

    /**
     * Get parent Dir object
     */
    public function getParent(): ?Dir
    {
        return null;
    }

    /**
     * Attempt to shared lock file
     */
    public function lock(bool $nonBlocking = false): bool
    {
        if ($this->resource === null) {
            throw Exceptional::Io(
                'Cannot lock file, file not open',
                null,
                $this
            );
        }

        return true;
    }

    /**
     * Attempt to exclusive lock file
     */
    public function lockExclusive(bool $nonBlocking = false): bool
    {
        if ($this->resource === null) {
            throw Exceptional::Io(
                'Cannot lock file, file not open',
                null,
                $this
            );
        }

        return true;
    }

    /**
     * Unlock file
     */
    public function unlock(): File
    {
        return $this;
    }


    /**
     * Move file to $destinationPath
     */
    public function move(string $path): File
    {
        $output = $this->copy($path);
        $this->close();
        return $output;
    }


    /**
     * Delete file from filesystem
     */
    public function delete(): void
    {
        $this->close();
    }
}
