<?php
/**
 * This file is part of the Atlas package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Atlas;

use Generator;
use DecodeLabs\Atlas\Node;

interface Dir extends Node
{
    public function ensureExists(int $permissions=null): Dir;
    public function isEmpty(): bool;

    public function setPermissions(int $mode, bool $recursive=false): Dir;
    public function setOwner(int $owner, bool $recursive=false): Dir;
    public function setGroup(int $group, bool $recursive=false): Dir;

    public function scan(callable $filter=null): Generator;
    public function scanNames(callable $filter=null): Generator;
    public function countContents(callable $filter=null): int;

    public function scanFiles(callable $filter=null): Generator;
    public function scanFileNames(callable $filter=null): Generator;
    public function countFiles(callable $filter=null): int;

    public function scanDirs(callable $filter=null): Generator;
    public function scanDirNames(callable $filter=null): Generator;
    public function countDirs(callable $filter=null): int;

    public function scanRecursive(callable $filter=null): Generator;
    public function scanNamesRecursive(callable $filter=null): Generator;
    public function countContentsRecursive(callable $filter=null): int;

    public function scanFilesRecursive(callable $filter=null): Generator;
    public function scanFileNamesRecursive(callable $filter=null): Generator;
    public function countFilesRecursive(callable $filter=null): int;

    public function scanDirsRecursive(callable $filter=null): Generator;
    public function scanDirNamesRecursive(callable $filter=null): Generator;
    public function countDirsRecursive(callable $filter=null): int;

    public function getChild(string $name): ?Node;
    public function hasChild(string $name): bool;
    public function deleteChild(string $name): Node;

    public function createDir(string $name, int $permissions=null): Dir;
    public function hasDir(string $name): bool;
    public function getDir(string $name, bool $ifExists=false): ?Dir;
    public function deleteDir(string $name): Dir;

    public function createFile(string $name, string $content): File;
    public function openFile(string $name, string $mode): File;
    public function hasFile(string $name): bool;
    public function getFile(string $name, bool $ifExists=false): ?File;
    public function deleteFile(string $name): Dir;

    public function emptyOut(): Dir;
    public function mergeInto(string $destination): Dir;
}
