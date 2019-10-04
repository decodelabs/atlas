<?php
/**
 * This file is part of the Atlas package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Atlas\File;

use DecodeLabs\Atlas\Node;
use DecodeLabs\Atlas\Node\LocalTrait;

use DecodeLabs\Atlas\File;
use DecodeLabs\Atlas\Dir;
use DecodeLabs\Atlas\Dir\Local as LocalDir;

use DecodeLabs\Atlas\Channel;
use DecodeLabs\Atlas\Channel\Stream;
use DecodeLabs\Atlas\Channel\Buffer;

use Generator;

use DecodeLabs\Glitch\Inspectable;
use DecodeLabs\Glitch\Dumper\Entity;
use DecodeLabs\Glitch\Dumper\Inspector;

class Local extends Stream implements File, Inspectable
{
    use LocalTrait;

    /**
     * Init with file path, if mode is set, open file
     */
    public function __construct(string $path, string $mode=null)
    {
        $this->path = $path;

        if ($mode !== null) {
            $this->open($mode);
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

        return file_exists($this->path);
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

        return filesize($this->path);
    }

    /**
     * Get hash of file contents
     */
    public function getHash(string $type): ?string
    {
        if (!$this->exists()) {
            return null;
        }

        return hash_file($type, $this->path, $raw);
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
            $file = new File('php://temp', 'w+');
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
            throw Glitch::EIo('Unable to lock file for writing', null, $this);
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
            throw Glitch::EIo('Unable to lock file for reading', null, $this);
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
            throw Glitch::EIo('Unable to open file', null, $this);
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
     * Set permissions on file
     */
    public function setPermissions(int $mode): File
    {
        if (!$this->exists()) {
            throw Glitch::ENotFound('Cannot set permissions, file does not exist', null, $this);
        }

        chmod($this->path, $mode);
        return $this;
    }

    /**
     * Set owner of file
     */
    public function setOwner(int $owner): File
    {
        if (!$this->exists()) {
            throw Glitch::ENotFound('Cannot set owner, file does not exist', null, $this);
        }

        chown($this->path, $owner);
        return $this;
    }

    /**
     * Set group of file
     */
    public function setGroup(int $group): File
    {
        if (!$this->exists()) {
            throw Glitch::ENotFound('Cannot set owner, file does not exist', null, $this);
        }

        chgrp($this->path, $group);
        return $this;
    }


    /**
     * Attempt to shared lock file
     */
    public function lock(bool $nonBlocking=false): bool
    {
        if ($this->resource === null) {
            throw Glitch::EIo('Cannot lock file, file not open', null, $this);
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
    public function lockExclusive(bool $nonBlocking=false): bool
    {
        if ($this->resource === null) {
            throw Glitch::EIo('Cannot lock file, file not open', null, $this);
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
            throw Glitch::EIo('Unable to unlock file', null, $this);
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
            throw Glitch::ENotFound('Source file does not exist', null, $this);
        }

        (new LocalDir(dirname($path)))->ensureExists();

        if (file_exists($path)) {
            throw Glitch::EIo('Destination file already exists', null, $path);
        }

        if (!rename($this->path, $path)) {
            throw Glitch::EIo('Unable to rename file', null, $this);
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
            throw Glitch::EIo('Cannot seek file, file not open', null, $this);
        }

        if (0 !== fseek($this->resource, $offset, SEEK_SET)) {
            throw Glitch::EIo('Failed to seek file', null, $this);
        }

        return $this;
    }

    /**
     * Move file pointer to offset
     */
    public function movePosition(int $offset, bool $fromEnd=false): File
    {
        if ($this->resource === null) {
            throw Glitch::EIo('Cannot seek file, file not open', null, $this);
        }

        if (0 !== fseek($this->resource, $offset, $fromEnd ? SEEK_END : SEEK_CUR)) {
            throw Glitch::EIo('Failed to seek file', null, $this);
        }

        return $this;
    }


    /**
     * Get location of file pointer
     */
    public function getPosition(): int
    {
        if ($this->resource === null) {
            throw Glitch::EIo('Cannot ftell file, file not open', null, $this);
        }

        $output = ftell($this->resource);

        if ($output === false) {
            throw Glitch::EIo('Failed to ftell file', null, $this);
        }

        return $output;
    }

    /**
     * Ensure all data is written to file
     */
    public function flush(): File
    {
        if ($this->resource === null) {
            throw Glitch::EIo('Cannot flush file, file not open', null, $this);
        }

        $output = fflush($this->resource);

        if ($output === false) {
            throw Glitch::EIo('Failed to flush file', null, $this);
        }

        return $output;
    }

    /**
     * Truncate a file to $size bytes
     */
    public function truncate(int $size=0): File
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
     * Inspect for Glitch
     */
    public function glitchInspect(Entity $entity, Inspector $inspector): void
    {
        $entity
            ->setDefinition(Glitch::normalizePath($this->path))
            ->setMetaList([
                'resource' => $inspector($this->resource),
                'exists' => $inspector($this->exists()),
                'readable' => $inspector($this->isReadable()),
                'writable' => $inspector($this->isWritable()),
                'permissions' => $this->getPermissionsOct().' : '.$this->getPermissionsString()
            ]);
    }
}
