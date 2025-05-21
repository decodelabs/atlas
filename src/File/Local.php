<?php

/**
 * @package Atlas
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Atlas\File;

use DecodeLabs\Atlas\Dir;
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

/**
 * @implements Channel<resource>
 */
class Local extends Stream implements
    Channel,
    File,
    GzOpenable,
    Dumpable
{
    /**
     * @use LocalTrait<File>
     */
    use LocalTrait;

    /**
     * @param string|resource $stream
     */
    public function __construct(
        $stream,
        string|Mode|null $mode = null
    ) {
        if (is_resource($stream)) {
            parent::__construct($stream, null);
            $this->path = stream_get_meta_data($stream)['uri'];
        } else {
            $stream = (string)$stream;
            parent::__construct($stream, null);
            $this->path = $stream;

            if ($mode !== null) {
                $this->open($mode);
            }
        }
    }

    public function __destruct()
    {
        $this->close();
    }

    public function exists(): bool
    {
        if ($this->ioResource) {
            return true;
        }

        return
            file_exists($this->path) ||
            is_link($this->path);
    }



    public function isFile(): bool
    {
        return true;
    }

    public function isDir(): bool
    {
        return false;
    }


    public function isOnDisk(): bool
    {
        return $this->exists();
    }


    public function isReadable(): bool
    {
        if (!is_resource($this->ioResource)) {
            return is_readable($this->path);
        }

        return parent::isReadable();
    }

    public function isWritable(): bool
    {
        if (!is_resource($this->ioResource)) {
            return is_writable($this->path);
        }

        return parent::isWritable();
    }



    /**
     * @return LocalDir
     */
    public function getParent(): ?Dir
    {
        return new LocalDir(dirname($this->path));
    }


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



    public function putContents(
        mixed $data
    ): File {
        $closeData = $closeAfter = false;

        if (!$data instanceof Channel) {
            $file = new LocalFile('php://temp', 'w+');
            $file->write(Coercion::toString($data));
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

        if (!is_resource($this->ioResource)) {
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

    public function getContents(): string
    {
        $closeAfter = false;

        if (!is_resource($this->ioResource)) {
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

    public function bufferContents(): Buffer
    {
        return new Buffer($this->getContents());
    }




    public function open(
        string|Mode $mode
    ): File {
        if ($mode instanceof Mode) {
            $mode = $mode->getValue();
        }

        if ($this->ioResource !== null) {
            if ($this->ioMode === $mode) {
                return $this;
            }

            $this->close();
        }

        // @phpstan-ignore-next-line
        $this->ioMode = $mode;

        $isWrite =
            strstr($mode, 'x') ||
            strstr($mode, 'w') ||
            strstr($mode, 'c') ||
            strstr($mode, 'a') ||
            strstr($mode, '+');

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

        // @phpstan-ignore-next-line
        $this->ioResource = $resource;
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


    public function isOpen(): bool
    {
        return $this->ioResource !== null;
    }

    public function isLink(): bool
    {
        return is_link($this->path);
    }



    public function lock(
        bool $nonBlocking = false
    ): bool {
        if (!is_resource($this->ioResource)) {
            throw Exceptional::Io(
                message: 'Cannot lock file, file not open',
                data: $this
            );
        }

        if ($nonBlocking) {
            return flock($this->ioResource, LOCK_SH | LOCK_NB);
        } else {
            return flock($this->ioResource, LOCK_SH);
        }
    }

    public function lockExclusive(
        bool $nonBlocking = false
    ): bool {
        if (!is_resource($this->ioResource)) {
            throw Exceptional::Io(
                message: 'Cannot lock file, file not open',
                data: $this
            );
        }

        return flock(
            $this->ioResource,
            $nonBlocking ?
                LOCK_EX | LOCK_NB :
                LOCK_EX
        );
    }

    public function unlock(): File
    {
        if (!is_resource($this->ioResource)) {
            return $this;
        }

        if (!flock($this->ioResource, LOCK_UN)) {
            throw Exceptional::Io(
                message: 'Unable to unlock file',
                data: $this
            );
        }

        return $this;
    }



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

        if (!is_resource($this->ioResource)) {
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


    public function setPosition(
        int $offset
    ): File {
        if (!is_resource($this->ioResource)) {
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


    public function movePosition(
        int $offset,
        bool $fromEnd = false
    ): File {
        if (!is_resource($this->ioResource)) {
            throw Exceptional::Io(
                message: 'Cannot seek file, file not open',
                data: $this
            );
        }

        if (0 !== fseek($this->ioResource, $offset, $fromEnd ? SEEK_END : SEEK_CUR)) {
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
        if (!is_resource($this->ioResource)) {
            return -1;
        }

        return fseek($this->ioResource, $offset, $flags);
    }


    public function getPosition(): int
    {
        if (!is_resource($this->ioResource)) {
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
        if (!is_resource($this->ioResource)) {
            return false;
        }

        return ftell($this->ioResource);
    }

    /**
     * @param int<1, max> $length
     */
    public function readFrom(
        int $position,
        int $length
    ): ?string {
        $this->setPosition($position);
        return $this->read($length);
    }

    public function flush(): File
    {
        if (!is_resource($this->ioResource)) {
            throw Exceptional::Io(
                message: 'Cannot flush file, file not open',
                data: $this
            );
        }

        if (false === fflush($this->ioResource)) {
            throw Exceptional::Io(
                message: 'Failed to flush file',
                data: $this
            );
        }

        return $this;
    }

    /**
     * @param int<0, max> $size
     */
    public function truncate(
        int $size = 0
    ): File {
        if (is_resource($this->ioResource)) {
            ftruncate($this->ioResource, $size);
        } else {
            $this->open('w');
            $this->close();
        }

        return $this;
    }


    public function glitchDump(): iterable
    {
        yield 'definition' => Proxy::normalizePath($this->path);

        yield 'metaList' => [
            'ioResource' => $this->ioResource,
            'exists' => $this->exists(),
            'readable' => $this->isReadable(),
            'writable' => $this->isWritable(),
            'permissions' => $this->getPermissionsOct() . ' : ' . $this->getPermissionsString()
        ];
    }
}
