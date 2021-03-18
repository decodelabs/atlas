<?php

/**
 * @package Atlas
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Atlas\File;

use DecodeLabs\Atlas\Channel;
use DecodeLabs\Atlas\Channel\Buffer;
use DecodeLabs\Atlas\Channel\Stream;
use DecodeLabs\Atlas\Dir;
use DecodeLabs\Atlas\Dir\Local as LocalDir;
use DecodeLabs\Atlas\File;
use DecodeLabs\Atlas\File\Local as LocalFile;
use DecodeLabs\Atlas\Node;
use DecodeLabs\Atlas\Node\LocalTrait;

use DecodeLabs\Exceptional;
use DecodeLabs\Glitch\Dumpable;
use DecodeLabs\Glitch\Proxy;

class Local extends Stream implements File, Dumpable
{
    use LocalTrait;

    /**
     * Init with file path, if mode is set, open file
     */
    public function __construct($path, string $mode = null)
    {
        if (is_resource($path)) {
            parent::__construct($path, null);
            $this->path = stream_get_meta_data($this->resource)['uri'];
        } else {
            $path = (string)$path;
            parent::__construct($path, null);
            $this->path = $path;

            if ($mode !== null) {
                $this->open($mode);
            }
        }
    }

    /**
     * Ensure file is closed
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Does this file exist on disk?
     */
    public function exists(): bool
    {
        if ($this->resource) {
            return true;
        }

        return file_exists($this->path) || is_link($this->path);
    }



    /**
     * Is this a file?
     */
    public function isFile(): bool
    {
        return true;
    }

    /**
     * Is this a dir?
     */
    public function isDir(): bool
    {
        return false;
    }


    /**
     * Can this file be read from disk
     */
    public function isOnDisk(): bool
    {
        return $this->exists();
    }


    /**
     * Is the resource still accessible?
     */
    public function isReadable(): bool
    {
        if ($this->resource === null) {
            return is_readable($this->path);
        }

        return parent::isReadable();
    }

    /**
     * Is the resource still writable?
     */
    public function isWritable(): bool
    {
        if ($this->resource === null) {
            return is_writable($this->path);
        }

        return parent::isWritable();
    }


    /**
     * Get size of file in bytes
     */
    public function getSize(): ?int
    {
        if (!$this->exists()) {
            return null;
        }

        if (false === ($output = filesize($this->path))) {
            return null;
        }

        return $output;
    }

    /**
     * Get hash of file contents
     */
    public function getHash(string $type): ?string
    {
        if (!$this->exists()) {
            return null;
        }

        return hash_file($type, $this->path);
    }

    /**
     * Get hash of file contents
     */
    public function getRawHash(string $type): ?string
    {
        if (!$this->exists()) {
            return null;
        }

        return hash_file($type, $this->path, true);
    }



    /**
     * Write content to file
     */
    public function putContents($data): File
    {
        $closeData = $closeAfter = false;

        if (!$data instanceof Channel) {
            $file = new LocalFile('php://temp', 'w+');
            $file->write((string)$data);
            $file->setPosition(0);
            $data = $file;
            $closeData = true;
        }

        if ($data instanceof File && !$data->isOpen()) {
            $data->open('r');
            $closeData = true;
        }

        if ($this->resource === null) {
            $closeAfter = true;
            $this->open('w');
        }

        if (!$this->lockExclusive()) {
            throw Exceptional::Io(
                'Unable to lock file for writing',
                null,
                $this
            );
        }

        $this->truncate();
        $data->readTo($this);
        $this->unlock();

        if ($closeAfter) {
            $this->close();
        }

        if ($closeData) {
            $data->close();
        }

        return $this;
    }

    /**
     * Read contents of file
     */
    public function getContents(): string
    {
        $closeAfter = false;

        if ($this->resource === null) {
            $closeAfter = true;
            $this->open('r');
        }

        if (!$this->lock()) {
            throw Exceptional::Io(
                'Unable to lock file for reading',
                null,
                $this
            );
        }

        $this->setPosition(0);
        $output = (string)$this->readAll();
        $this->unlock();

        if ($closeAfter) {
            $this->close();
        }

        return $output;
    }

    /**
     * Read contents of file and add to Buffer
     */
    public function bufferContents(): Buffer
    {
        return new Buffer($this->getContents());
    }




    /**
     * Open file for reading and writing
     */
    public function open(string $mode): File
    {
        if ($this->resource !== null) {
            if ($this->mode === $mode) {
                return $this;
            }

            $this->close();
        }

        $this->mode = $mode;

        $isWrite =
            strstr($this->mode, 'x') ||
            strstr($this->mode, 'w') ||
            strstr($this->mode, 'c') ||
            strstr($this->mode, 'a') ||
            strstr($this->mode, '+');

        if ($isWrite && !$this->exists()) {
            $mkDir = true;

            if (false !== strpos($this->path, '://')) {
                $parts = explode('://', $this->path, 2);

                if ($parts[0] !== 'file') {
                    $mkDir = false;
                }
            }

            if ($mkDir) {
                (new LocalDir(dirname($this->path)))->ensureExists();
            }
        }

        if (!$this->resource = fopen($this->path, $mode)) {
            throw Exceptional::Io(
                'Unable to open file',
                null,
                $this
            );
        }

        return $this;
    }

    /**
     * Has this file been opened?
     */
    public function isOpen(): bool
    {
        return $this->resource !== null;
    }

    /**
     * Is this file a symbolic link?
     */
    public function isLink(): bool
    {
        return is_link($this->path);
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

        if ($nonBlocking) {
            return flock($this->resource, LOCK_SH | LOCK_NB);
        } else {
            return flock($this->resource, LOCK_SH);
        }
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

        return flock(
            $this->resource,
            $nonBlocking ?
                LOCK_EX | LOCK_NB :
                LOCK_EX
        );
    }

    /**
     * Unlock file
     */
    public function unlock(): File
    {
        if ($this->resource === null) {
            return $this;
        }

        if (!flock($this->resource, LOCK_UN)) {
            throw Exceptional::Io(
                'Unable to unlock file',
                null,
                $this
            );
        }

        return $this;
    }



    /**
     * Copy file to $destinationPath
     */
    public function copy(string $path): Node
    {
        if ($path === $this->path) {
            return $this;
        }

        if ($this->isLink()) {
            if (file_exists($path)) {
                throw Exceptional::AlreadyExists(
                    'Destination file already exists',
                    null,
                    $this
                );
            }

            return $this->copySymlink($path);
        }

        $target = new self($path, 'w');
        $closeAfter = false;

        if ($this->resource === null) {
            $closeAfter = true;
            $this->open('r');
        }

        $this->lock();
        $this->setPosition(0);

        while (!$this->isAtEnd()) {
            $target->write($this->read(8192));
        }

        $this->unlock();

        if ($closeAfter) {
            $this->close();
        }

        $target->close();
        return $target;
    }



    /**
     * Move file to $destinationPath
     */
    public function move(string $path): Node
    {
        if (!$this->exists()) {
            throw Exceptional::NotFound(
                'Source file does not exist',
                null,
                $this
            );
        }

        if (file_exists($path)) {
            throw Exceptional::AlreadyExists(
                'Destination file already exists',
                null,
                $path
            );
        }

        (new LocalDir(dirname($path)))->ensureExists();

        if (!rename($this->path, $path)) {
            throw Exceptional::Io(
                'Unable to rename file',
                null,
                $this
            );
        }

        $this->path = $path;
        return $this;
    }


    /**
     * Delete file from filesystem
     */
    public function delete(): void
    {
        $exists = $this->exists();
        $this->close();

        if ($exists) {
            try {
                unlink($this->path);
            } catch (\Throwable $e) {
                if ($this->exists()) {
                    throw $e;
                }
            }
        }
    }


    /**
     * Seek file pointer to offset
     */
    public function setPosition(int $offset): File
    {
        if ($this->resource === null) {
            throw Exceptional::Io(
                'Cannot seek file, file not open',
                null,
                $this
            );
        }

        if (0 !== fseek($this->resource, $offset, SEEK_SET)) {
            throw Exceptional::Io(
                'Failed to seek file',
                null,
                $this
            );
        }

        return $this;
    }

    /**
     * Move file pointer to offset
     */
    public function movePosition(int $offset, bool $fromEnd = false): File
    {
        if ($this->resource === null) {
            throw Exceptional::Io(
                'Cannot seek file, file not open',
                null,
                $this
            );
        }

        if (0 !== fseek($this->resource, $offset, $fromEnd ? SEEK_END : SEEK_CUR)) {
            throw Exceptional::Io(
                'Failed to seek file',
                null,
                $this
            );
        }

        return $this;
    }


    /**
     * Get location of file pointer
     */
    public function getPosition(): int
    {
        if ($this->resource === null) {
            throw Exceptional::Io(
                'Cannot ftell file, file not open',
                null,
                $this
            );
        }

        $output = ftell($this->resource);

        if ($output === false) {
            throw Exceptional::Io(
                'Failed to ftell file',
                null,
                $this
            );
        }

        return $output;
    }

    /**
     * Seek and read
     */
    public function readFrom(int $position, int $length): ?string
    {
        $this->setPosition($position);
        return $this->read($length);
    }

    /**
     * Ensure all data is written to file
     */
    public function flush(): File
    {
        if ($this->resource === null) {
            throw Exceptional::Io(
                'Cannot flush file, file not open',
                null,
                $this
            );
        }

        if (false === fflush($this->resource)) {
            throw Exceptional::Io(
                'Failed to flush file',
                null,
                $this
            );
        }

        return $this;
    }

    /**
     * Truncate a file to $size bytes
     */
    public function truncate(int $size = 0): File
    {
        if ($this->resource !== null) {
            ftruncate($this->resource, $size);
        } else {
            $this->open('w');
            $this->close();
        }

        return $this;
    }


    /**
     * Export for dump inspection
     */
    public function glitchDump(): iterable
    {
        yield 'definition' => Proxy::normalizePath($this->path);

        yield 'metaList' => [
            'resource' => $this->resource,
            'exists' => $this->exists(),
            'readable' => $this->isReadable(),
            'writable' => $this->isWritable(),
            'permissions' => $this->getPermissionsOct() . ' : ' . $this->getPermissionsString()
        ];
    }
}
