<?php
/**
 * This file is part of the Atlas package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Atlas;

use DecodeLabs\Atlas\Channel;
use DecodeLabs\Atlas\Node;

interface File extends Node, Channel
{
    public function getSize(): ?int;
    public function getHash(string $type, bool $raw=false): ?string;

    public function putContents($data): File;
    public function getContents(): string;
    public function bufferContents(): Buffer;

    public function open(string $mode): File;
    public function isOpen(): bool;
    public function isLink(): bool;
    public function getIoMode(): ?string;

    public function setPermissions(int $mode): File;
    public function setOwner(int $owner): File;
    public function setGroup(int $group): File;

    public function lock(bool $nonBlocking=false): bool;
    public function lockExclusive(bool $nonBlocking=false): bool;
    public function unlock(): File;

    public function setPosition(int $position): File;
    public function movePosition(int $position, bool $fromEnd=false): File;
    public function getPosition(): int;
    public function flush(): File;
    public function truncate(int $size=0): File;
}
