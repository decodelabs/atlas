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

    public function setPermissionsRecursive(int $mode): Dir;
    public function setOwnerRecursive(int $owner): Dir;
    public function setGroupRecursive(int $group): Dir;

    public function scan(callable $filter=null): Generator;
    public function list(callable $filter=null): array;
    public function scanNames(callable $filter=null): Generator;
    public function listNames(callable $filter=null): array;
    public function scanPaths(callable $filter=null): Generator;
    public function listPaths(callable $filter=null): array;
    public function countContents(callable $filter=null): int;

    public function scanFiles(callable $filter=null): Generator;
    public function listFiles(callable $filter=null): array;
    public function scanFileNames(callable $filter=null): Generator;
    public function listFileNames(callable $filter=null): array;
    public function scanFilePaths(callable $filter=null): Generator;
    public function listFilePaths(callable $filter=null): array;
    public function countFiles(callable $filter=null): int;

    public function scanDirs(callable $filter=null): Generator;
    public function listDirs(callable $filter=null): array;
    public function scanDirNames(callable $filter=null): Generator;
    public function listDirNames(callable $filter=null): array;
    public function scanDirPaths(callable $filter=null): Generator;
    public function listDirPaths(callable $filter=null): array;
    public function countDirs(callable $filter=null): int;

    public function scanRecursive(callable $filter=null): Generator;
    public function listRecursive(callable $filter=null): array;
    public function scanNamesRecursive(callable $filter=null): Generator;
    public function listNamesRecursive(callable $filter=null): array;
    public function scanPathsRecursive(callable $filter=null): Generator;
    public function listPathsRecursive(callable $filter=null): array;
    public function countContentsRecursive(callable $filter=null): int;

    public function scanFilesRecursive(callable $filter=null): Generator;
    public function listFilesRecursive(callable $filter=null): array;
    public function scanFileNamesRecursive(callable $filter=null): Generator;
    public function listFileNamesRecursive(callable $filter=null): array;
    public function scanFilePathsRecursive(callable $filter=null): Generator;
    public function listFilePathsRecursive(callable $filter=null): array;
    public function countFilesRecursive(callable $filter=null): int;

    public function scanDirsRecursive(callable $filter=null): Generator;
    public function listDirsRecursive(callable $filter=null): array;
    public function scanDirNamesRecursive(callable $filter=null): Generator;
    public function listDirNamesRecursive(callable $filter=null): array;
    public function scanDirPathsRecursive(callable $filter=null): Generator;
    public function listDirPathsRecursive(callable $filter=null): array;
    public function countDirsRecursive(callable $filter=null): int;

    public function getChild(string $name): ?Node;
    public function hasChild(string $name): bool;
    public function deleteChild(string $name): Node;

    public function createDir(string $name, int $permissions=null): Dir;
    public function hasDir(string $name): bool;
    public function getDir(string $name): Dir;
    public function getExistingDir(string $name): ?Dir;
    public function deleteDir(string $name): Dir;

    public function createFile(string $name, string $content): File;
    public function openFile(string $name, string $mode): File;
    public function hasFile(string $name): bool;
    public function getFile(string $name): File;
    public function getExistingFile(string $name): ?File;
    public function deleteFile(string $name): Dir;

    public function emptyOut(): Dir;
    public function mergeInto(string $destination): Dir;
}
