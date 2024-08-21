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
        if ($this->resource === null) {
            return -1;
        }

        return gzseek($this->resource, $offset, $flags);
    }

    protected function ftell(): int|false
    {
        if ($this->resource === null) {
            return false;
        }

        return gztell($this->resource);
    }

    /**
     * @param int<0, max> $length
     */
    protected function fread(
        int $length
    ): string|false {
        if ($this->resource === null) {
            return false;
        }

        return gzread($this->resource, $length);
    }

    protected function fgetc(): string|false
    {
        if ($this->resource === null) {
            return false;
        }

        return gzgetc($this->resource);
    }

    /**
     * @param int<0, max>|null $length
     */
    protected function fgets(
        ?int $length = null
    ): string|false {
        if ($this->resource === null) {
            return false;
        }

        return gzgets($this->resource, $length);
    }

    protected function fwrite(
        string $data,
        ?int $length = null
    ): int|false {
        if ($this->resource === null) {
            return false;
        }

        return gzwrite($this->resource, $data, $length);
    }

    protected function feof(): bool
    {
        if ($this->resource === null) {
            return true;
        }

        return gzeof($this->resource);
    }

    protected function fclose(): bool
    {
        if ($this->resource === null) {
            return false;
        }

        return gzclose($this->resource);
    }



    /**
     * Attempt to shared lock file
     */
    public function lock(
        bool $nonBlocking = false
    ): bool {
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
    public function lockExclusive(
        bool $nonBlocking = false
    ): bool {
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
}
