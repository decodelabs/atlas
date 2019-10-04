<?php
/**
 * This file is part of the Atlas package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Atlas;

interface Node
{
    public function getPath(): string;
    public function getName(): string;
    public function exists(): bool;
    public function clearStatCache(): Node;
    public function getLastModified(): ?int;
    public function hasChanged(int $timeout=30): bool;

    public function getPermissions(): ?int;
    public function getPermissionsOct(): ?string;
    public function getPermissionsString(): ?string;
    public function getOwner(): ?int;
    public function getGroup(): ?int;

    public function getParent(): ?Dir;

    public function copy(string $path): Node;
    public function copyTo(string $destinationDir, string $newName=null): Node;
    public function renameTo(string $newName): Node;
    public function move(string $path): Node;
    public function moveTo(string $destinationDir, string $newName=null): Node;
    public function delete(): void;
}
