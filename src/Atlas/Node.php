<?php

/**
 * @package Atlas
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Atlas;

/**
 * @template T of Dir|File
 */
interface Node
{
    public function getPath(): string;
    public function __toString(): string;
    public function getName(): string;
    public function exists(): bool;

    public function isFile(): bool;
    public function isDir(): bool;

    public function isLink(): bool;

    /**
     * @return T
     */
    public function getLinkTarget(): ?Node;

    /**
     * @return T
     */
    public function createLink(string $path): Node;


    /**
     * @return $this
     */
    public function clearStatCache(): Node;

    public function getLastModified(): ?int;
    public function hasChanged(int $timeout = 30): bool;
    public function hasChangedIn(string $timeout): bool;


    /**
     * @return $this
     */
    public function setPermissions(int $mode): Node;

    public function getPermissions(): ?int;
    public function getPermissionsOct(): ?string;
    public function getPermissionsString(): ?string;

    /**
     * @return $this
     */
    public function setOwner(int $owner): Node;

    public function getOwner(): ?int;

    /**
     * @return $this
     */
    public function setGroup(int $group): Node;

    public function getGroup(): ?int;
    public function getParent(): ?Dir;


    /**
     * @return T
     */
    public function copy(string $path): Node;

    /**
     * @return T
     */
    public function copyTo(string $destinationDir, string $newName = null): Node;

    /**
     * @return T
     */
    public function renameTo(string $newName): Node;

    /**
     * @return T
     */
    public function move(string $path): Node;

    /**
     * @return T
     */
    public function moveTo(string $destinationDir, string $newName = null): Node;

    public function delete(): void;
}
