<?php

/**
 * @package Atlas
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Atlas\File;

use DecodeLabs\Atlas\Dir\Local as LocalDir;
use DecodeLabs\Atlas\File;
use DecodeLabs\Atlas\File\Local as LocalFile;
use DecodeLabs\Atlas\Mode;
use DecodeLabs\Atlas\Node\LocalTrait;
use DecodeLabs\Coercion;
use DecodeLabs\Deliverance\Channel;
use DecodeLabs\Deliverance\Channel\Buffer;
use DecodeLabs\Deliverance\Channel\Stream;
use DecodeLabs\Exceptional;
use DecodeLabs\Glitch\Dumpable;
use DecodeLabs\Glitch\Proxy;
use Throwable;

class Local extends Stream implements
    File,
    GzOpenable,
    Dumpable
{
    /**
     * @use LocalTrait<File>
     */
    use LocalTrait;

    /**
     * Init with file path, if mode is set, open file
     *
     * @param string|resource $stream
     */
    public function __construct(
        $stream,
        string|Mode|null $mode = null
    ) {
        if (is_resource($stream)) {
            parent::__construct($stream, null);

            if ($this->resource !== null) {
                $this->path = stream_get_meta_data($this->resource)['uri'];
            }
        } else {
            $stream = (string)$stream;
            parent::__construct($stream, null);
            $this->path = $stream;

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

        return
            file_exists($this->path) ||
            is_link($this->path);
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
    public function getHash(
        string $type
    ): ?string {
        if (!$this->exists()) {
            return null;
        }

        $output = hash_file($type, $this->path);

        if ($output === false) {
            $output = null;
        }

        return $output;
    }

    /**
     * Get hash of file contents
     */
    public function getRawHash(
        string $type
    ): ?string {
        if (!$this->exists()) {
            return null;
        }

        $output = hash_file($type, $this->path, true);

        if ($output === false) {
            $output = null;
        }

        return $output;
    }



    /**
     * Write content to file
     */
    public function putContents(
        mixed $data
    ): File {
        $closeData = $closeAfter = false;

        if (!$data instanceof Channel) {
            $file = new LocalFile('php://temp', 'w+');
            $file->write(Coercion::forceString($data));
            $file->setPosition(0);
            $data = $file;
            $closeData = true;
        }

        if ($data instanceof File) {
            if (!$data->isOpen()) {
                $data->open('r');
                $closeData = true;
            }

            $data->setPosition(0);
        }

        if ($this->resource === null) {
            $closeAfter = true;
            $this->open('w');
        }

        if (!$this->lockExclusive()) {
            throw Exceptional::Io(
                message: 'Unable to lock file for writing',
                data: $this
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
                message: 'Unable to lock file for reading',
                data: $this
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
    public function open(
        string|Mode $mode
    ): File {
        if ($mode instanceof Mode) {
            $mode = $mode->getValue();
        }

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

        if (
            $isWrite &&
            !$this->exists()
        ) {
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

        if (!$resource = $this->fopen($mode)) {
            throw Exceptional::Io(
                message: 'Unable to open file',
                data: $this
            );
        }

        $this->resource = $resource;
        return $this;
    }

    /**
     * @return resource|false
     */
    protected function fopen(
        string $mode
    ) {
        return fopen($this->path, $mode);
    }


    public function gzOpen(
        string|Mode $mode
    ): Gz {
        $this->close();
        return new GzLocal($this->path, $mode);
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
    public function lock(
        bool $nonBlocking = false
    ): bool {
        if ($this->resource === null) {
            throw Exceptional::Io(
                message: 'Cannot lock file, file not open',
                data: $this
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
    public function lockExclusive(
        bool $nonBlocking = false
    ): bool {
        if ($this->resource === null) {
            throw Exceptional::Io(
                message: 'Cannot lock file, file not open',
                data: $this
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
                message: 'Unable to unlock file',
                data: $this
            );
        }

        return $this;
    }



    /**
     * Copy file to $destinationPath
     */
    public function copy(
        string $path
    ): File {
        if ($path === $this->path) {
            return $this;
        }

        if ($this->isLink()) {
            if (file_exists($path)) {
                throw Exceptional::AlreadyExists(
                    message: 'Destination file already exists',
                    data: $this
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
    public function move(
        string $path
    ): File {
        if (!$this->exists()) {
            throw Exceptional::NotFound(
                message: 'Source file does not exist',
                data: $this
            );
        }

        if (file_exists($path)) {
            throw Exceptional::AlreadyExists(
                message: 'Destination file already exists',
                data: $path
            );
        }

        (new LocalDir(dirname($path)))->ensureExists();

        if (!rename($this->path, $path)) {
            throw Exceptional::Io(
                message: 'Unable to rename file',
                data: $this
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
            } catch (Throwable $e) {
                if ($this->exists()) {
                    throw $e;
                }
            }
        }
    }


    /**
     * Seek file pointer to offset
     */
    public function setPosition(
        int $offset
    ): File {
        if ($this->resource === null) {
            throw Exceptional::Io(
                message: 'Cannot seek file, file not open',
                data: $this
            );
        }

        if (0 !== $this->fseek($offset, SEEK_SET)) {
            throw Exceptional::Io(
                message: 'Failed to seek file',
                data: $this
            );
        }

        return $this;
    }


    /**
     * Move file pointer to offset
     */
    public function movePosition(
        int $offset,
        bool $fromEnd = false
    ): File {
        if ($this->resource === null) {
            throw Exceptional::Io(
                message: 'Cannot seek file, file not open',
                data: $this
            );
        }

        if (0 !== fseek($this->resource, $offset, $fromEnd ? SEEK_END : SEEK_CUR)) {
            throw Exceptional::Io(
                message: 'Failed to seek file',
                data: $this
            );
        }

        return $this;
    }

    protected function fseek(
        int $offset,
        int $flags
    ): int {
        if ($this->resource === null) {
            return -1;
        }

        return fseek($this->resource, $offset, $flags);
    }


    /**
     * Get location of file pointer
     */
    public function getPosition(): int
    {
        if ($this->resource === null) {
            throw Exceptional::Io(
                message: 'Cannot ftell file, file not open',
                data: $this
            );
        }

        $output = $this->ftell();

        if ($output === false) {
            throw Exceptional::Io(
                message: 'Failed to ftell file',
                data: $this
            );
        }

        return $output;
    }

    protected function ftell(): int|false
    {
        if ($this->resource === null) {
            return false;
        }

        return ftell($this->resource);
    }

    /**
     * Seek and read
     *
     * @param int<1, max> $length
     */
    public function readFrom(
        int $position,
        int $length
    ): ?string {
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
                message: 'Cannot flush file, file not open',
                data: $this
            );
        }

        if (false === fflush($this->resource)) {
            throw Exceptional::Io(
                message: 'Failed to flush file',
                data: $this
            );
        }

        return $this;
    }

    /**
     * Truncate a file to $size bytes
     *
     * @param int<0, max> $size
     */
    public function truncate(
        int $size = 0
    ): File {
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
