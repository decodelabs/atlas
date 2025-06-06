<?php

/**
 * @package Atlas
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Atlas;

use DecodeLabs\Deliverance\Channel;
use DecodeLabs\Deliverance\Channel\Buffer;

/**
 * @extends Node<File>
 */
interface File extends
    Node,
    Channel
{
    public ?string $ioMode { get; }

    public function getSize(): ?int;
    public function isOnDisk(): bool;

    public function getHash(
        string $type
    ): ?string;

    public function getRawHash(
        string $type
    ): ?string;


    /**
     * @return $this
     */
    public function putContents(
        mixed $data
    ): File;

    public function getContents(): string;
    public function bufferContents(): Buffer;

    /**
     * @return $this
     */
    public function open(
        string|Mode $mode
    ): File;

    public function isOpen(): bool;
    public function isLink(): bool;

    public function lock(
        bool $nonBlocking = false
    ): bool;

    public function lockExclusive(
        bool $nonBlocking = false
    ): bool;


    /**
     * @return $this
     */
    public function unlock(): File;

    /**
     * @return $this
     */
    public function setPosition(
        int $position
    ): File;

    /**
     * @return $this
     */
    public function movePosition(
        int $position,
        bool $fromEnd = false
    ): File;

    public function getPosition(): int;

    public function readFrom(
        int $position,
        int $length
    ): ?string;

    /**
     * @return $this
     */
    public function flush(): File;

    /**
     * @return $this
     */
    public function truncate(
        int $size = 0
    ): File;
}
