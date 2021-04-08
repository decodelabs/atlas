<?php

/**
 * @package Atlas
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Atlas;

use Generator;

interface Dir extends Node
{
    /**
     * @return $this
     */
    public function ensureExists(int $permissions = null): Dir;
    public function isEmpty(): bool;

    /**
     * @return $this
     */
    public function setPermissionsRecursive(int $mode): Dir;

    /**
     * @return $this
     */
    public function setOwnerRecursive(int $owner): Dir;

    /**
     * @return $this
     */
    public function setGroupRecursive(int $group): Dir;


    /**
     * @return Generator<string, Node>
     */
    public function scan(callable $filter = null): Generator;

    /**
     * @return array<string, Node>
     */
    public function list(callable $filter = null): array;

    /**
     * @return Generator<string>
     */
    public function scanNames(callable $filter = null): Generator;

    /**
     * @return array<string>
     */
    public function listNames(callable $filter = null): array;

    /**
     * @return Generator<string, string>
     */
    public function scanPaths(callable $filter = null): Generator;

    /**
     * @return array<string, string>
     */
    public function listPaths(callable $filter = null): array;

    public function countContents(callable $filter = null): int;


    /**
     * @return Generator<string, File>
     */
    public function scanFiles(callable $filter = null): Generator;

    /**
     * @return array<string, File>
     */
    public function listFiles(callable $filter = null): array;

    /**
     * @return Generator<string>
     */
    public function scanFileNames(callable $filter = null): Generator;

    /**
     * @return array<string>
     */
    public function listFileNames(callable $filter = null): array;

    /**
     * @return Generator<string, string>
     */
    public function scanFilePaths(callable $filter = null): Generator;

    /**
     * @return array<string, string>
     */
    public function listFilePaths(callable $filter = null): array;

    public function countFiles(callable $filter = null): int;


    /**
     * @return Generator<string, Dir>
     */
    public function scanDirs(callable $filter = null): Generator;

    /**
     * @return array<string, Dir>
     */
    public function listDirs(callable $filter = null): array;

    /**
     * @return Generator<string>
     */
    public function scanDirNames(callable $filter = null): Generator;

    /**
     * @return array<string>
     */
    public function listDirNames(callable $filter = null): array;

    /**
     * @return Generator<string, string>
     */
    public function scanDirPaths(callable $filter = null): Generator;

    /**
     * @return array<string, string>
     */
    public function listDirPaths(callable $filter = null): array;

    public function countDirs(callable $filter = null): int;


    /**
     * @return Generator<string, Node>
     */
    public function scanRecursive(callable $filter = null): Generator;

    /**
     * @return array<string, Node>
     */
    public function listRecursive(callable $filter = null): array;

    /**
     * @return Generator<string>
     */
    public function scanNamesRecursive(callable $filter = null): Generator;

    /**
     * @return array<string>
     */
    public function listNamesRecursive(callable $filter = null): array;

    /**
     * @return Generator<string, string>
     */
    public function scanPathsRecursive(callable $filter = null): Generator;

    /**
     * @return array<string, string>
     */
    public function listPathsRecursive(callable $filter = null): array;

    public function countContentsRecursive(callable $filter = null): int;


    /**
     * @return Generator<string, File>
     */
    public function scanFilesRecursive(callable $filter = null): Generator;

    /**
     * @return array<string, File>
     */
    public function listFilesRecursive(callable $filter = null): array;

    /**
     * @return Generator<string>
     */
    public function scanFileNamesRecursive(callable $filter = null): Generator;

    /**
     * @return array<string>
     */
    public function listFileNamesRecursive(callable $filter = null): array;

    /**
     * @return Generator<string, string>
     */
    public function scanFilePathsRecursive(callable $filter = null): Generator;

    /**
     * @return array<string, string>
     */
    public function listFilePathsRecursive(callable $filter = null): array;

    public function countFilesRecursive(callable $filter = null): int;


    /**
     * @return Generator<string, Dir>
     */
    public function scanDirsRecursive(callable $filter = null): Generator;

    /**
     * @return array<string, Dir>
     */
    public function listDirsRecursive(callable $filter = null): array;

    /**
     * @return Generator<string>
     */
    public function scanDirNamesRecursive(callable $filter = null): Generator;

    /**
     * @return array<string>
     */
    public function listDirNamesRecursive(callable $filter = null): array;

    /**
     * @return Generator<string, string>
     */
    public function scanDirPathsRecursive(callable $filter = null): Generator;

    /**
     * @return array<string, string>
     */
    public function listDirPathsRecursive(callable $filter = null): array;

    public function countDirsRecursive(callable $filter = null): int;

    public function getChild(string $name): ?Node;
    public function hasChild(string $name): bool;
    public function deleteChild(string $name): Node;

    public function createDir(string $name, int $permissions = null): Dir;
    public function hasDir(string $name): bool;
    public function getDir(string $name): Dir;
    public function getExistingDir(string $name): ?Dir;

    /**
     * @return $this
     */
    public function deleteDir(string $name): Dir;

    public function createFile(string $name, string $content): File;
    public function openFile(string $name, string $mode): File;
    public function hasFile(string $name): bool;
    public function getFile(string $name): File;
    public function getExistingFile(string $name): ?File;

    /**
     * @return $this
     */
    public function deleteFile(string $name): Dir;

    /**
     * @return $this
     */
    public function emptyOut(): Dir;

    /**
     * @return $this
     */
    public function mergeInto(string $destination): Dir;
}
