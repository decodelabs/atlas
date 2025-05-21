<?php

/**
 * @package Atlas
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Atlas\File;

use DecodeLabs\Atlas\File;
use DecodeLabs\Atlas\Mode;
use DecodeLabs\Exceptional;

/**
 * @phpstan-require-implements GzOpenable
 */
trait GzTrait
{
    public function gzOpen(
        string|Mode $mode
    ): Gz {
        return $this->open($mode);
    }

    /**
     * @return resource|false
     */
    protected function fopen(
        string $mode
    ) {
        return gzopen($this->path, $mode);
    }

    protected function fseek(
        int $offset,
        int $flags
    ): int {
        if (!is_resource($this->ioResource)) {
            return -1;
        }

        return gzseek($this->ioResource, $offset, $flags);
    }

    protected function ftell(): int|false
    {
        if (!is_resource($this->ioResource)) {
            return false;
        }

        return gztell($this->ioResource);
    }

    /**
     * @param int<0, max> $length
     */
    protected function fread(
        int $length
    ): string|false {
        if (!is_resource($this->ioResource)) {
            return false;
        }

        return gzread($this->ioResource, $length);
    }

    protected function fgetc(): string|false
    {
        if (!is_resource($this->ioResource)) {
            return false;
        }

        return gzgetc($this->ioResource);
    }

    /**
     * @param int<0, max>|null $length
     */
    protected function fgets(
        ?int $length = null
    ): string|false {
        if (!is_resource($this->ioResource)) {
            return false;
        }

        return gzgets($this->ioResource, $length);
    }

    protected function fwrite(
        string $data,
        ?int $length = null
    ): int|false {
        if (!is_resource($this->ioResource)) {
            return false;
        }

        return gzwrite($this->ioResource, $data, $length);
    }

    protected function feof(): bool
    {
        if (!is_resource($this->ioResource)) {
            return true;
        }

        return gzeof($this->ioResource);
    }

    protected function fclose(): bool
    {
        if (!is_resource($this->ioResource)) {
            return false;
        }

        return gzclose($this->ioResource);
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

        return true;
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

        return true;
    }

    public function unlock(): File
    {
        return $this;
    }
}
