<?php
/**
 * This is a stub file for IDE compatibility only.
 * It should not be included in your projects.
 */
namespace DecodeLabs;

use DecodeLabs\Veneer\Proxy as Proxy;
use DecodeLabs\Veneer\ProxyTrait as ProxyTrait;
use DecodeLabs\Atlas\Context as Inst;
use DecodeLabs\Atlas\Mutex\Local as Ref0;
use DecodeLabs\Atlas\File as Ref1;
use DecodeLabs\Atlas\File\Memory as Ref2;
use Stringable as Ref3;
use DecodeLabs\Atlas\Dir as Ref4;
use DateInterval as Ref5;
use Generator as Ref6;

class Atlas implements Proxy
{
    use ProxyTrait;

    const Veneer = 'DecodeLabs\\Atlas';
    const VeneerTarget = Inst::class;

    public static Inst $instance;

    public static function newMutex(string $name, string $dir): Ref0 {
        return static::$instance->newMutex(...func_get_args());
    }
    public static function newTempFile(): Ref1 {
        return static::$instance->newTempFile();
    }
    public static function createTempFile(?string $data): Ref1 {
        return static::$instance->createTempFile(...func_get_args());
    }
    public static function newMemoryFile(string $key = 'temp'): Ref2 {
        return static::$instance->newMemoryFile(...func_get_args());
    }
    public static function createMemoryFile(?string $data, string $key = 'temp'): Ref2 {
        return static::$instance->createMemoryFile(...func_get_args());
    }
    public static function get(Ref3|Ref4|Ref1|string $path): Ref4|Ref1 {
        return static::$instance->get(...func_get_args());
    }
    public static function getExisting(string $path): Ref4|Ref1|null {
        return static::$instance->getExisting(...func_get_args());
    }
    public static function hasChanged(Ref3|Ref4|Ref1|string $path, int $seconds = 30): bool {
        return static::$instance->hasChanged(...func_get_args());
    }
    public static function setPermissions(Ref3|Ref4|Ref1|string $path, int $permissions): Ref4|Ref1 {
        return static::$instance->setPermissions(...func_get_args());
    }
    public static function setPermissionsRecursive(Ref3|Ref4|Ref1|string $path, int $permissions): Ref4|Ref1 {
        return static::$instance->setPermissionsRecursive(...func_get_args());
    }
    public static function setOwner(Ref3|Ref4|Ref1|string $path, int $owner): Ref4|Ref1 {
        return static::$instance->setOwner(...func_get_args());
    }
    public static function setGroup(Ref3|Ref4|Ref1|string $path, int $group): Ref4|Ref1 {
        return static::$instance->setGroup(...func_get_args());
    }
    public static function copy(Ref3|Ref4|Ref1|string $path, string $destinationPath): Ref4|Ref1 {
        return static::$instance->copy(...func_get_args());
    }
    public static function copyTo(Ref3|Ref4|Ref1|string $path, string $destinationDir, ?string $newName = NULL): Ref4|Ref1 {
        return static::$instance->copyTo(...func_get_args());
    }
    public static function rename(Ref3|Ref4|Ref1|string $path, string $newName): Ref4|Ref1 {
        return static::$instance->rename(...func_get_args());
    }
    public static function move(Ref3|Ref4|Ref1|string $path, string $destinationPath): Ref4|Ref1 {
        return static::$instance->move(...func_get_args());
    }
    public static function moveTo(Ref3|Ref4|Ref1|string $path, string $destinationDir, ?string $newName = NULL): Ref4|Ref1 {
        return static::$instance->moveTo(...func_get_args());
    }
    public static function delete(Ref3|Ref4|Ref1|string $path): void {}
    public static function file(Ref3|Ref1|string $path, ?string $mode = NULL): Ref1 {
        return static::$instance->file(...func_get_args());
    }
    public static function gzFile(Ref3|Ref1|string $path, string $mode): Ref1 {
        return static::$instance->gzFile(...func_get_args());
    }
    public static function existingFile(Ref3|Ref1|string $path, ?string $mode = NULL): ?Ref1 {
        return static::$instance->existingFile(...func_get_args());
    }
    public static function createFile(Ref3|Ref1|string $path, mixed $data): Ref1 {
        return static::$instance->createFile(...func_get_args());
    }
    public static function getContents(Ref3|Ref1|string $path): string {
        return static::$instance->getContents(...func_get_args());
    }
    public static function hasFileChanged(Ref3|Ref1|string $path, int $seconds = 30): bool {
        return static::$instance->hasFileChanged(...func_get_args());
    }
    public static function hasFileChangedIn(Ref3|Ref1|string $path, Ref5|Ref3|string|int $timeout): bool {
        return static::$instance->hasFileChangedIn(...func_get_args());
    }
    public static function setFilePermissions(Ref3|Ref1|string $path, int $permissions): Ref1 {
        return static::$instance->setFilePermissions(...func_get_args());
    }
    public static function setFileOwner(Ref3|Ref1|string $path, int $owner): Ref1 {
        return static::$instance->setFileOwner(...func_get_args());
    }
    public static function setFileGroup(Ref3|Ref1|string $path, int $group): Ref1 {
        return static::$instance->setFileGroup(...func_get_args());
    }
    public static function copyFile(Ref3|Ref1|string $path, string $destinationPath): Ref1 {
        return static::$instance->copyFile(...func_get_args());
    }
    public static function copyFileTo(Ref3|Ref1|string $path, string $destinationDir, ?string $newName = NULL): Ref1 {
        return static::$instance->copyFileTo(...func_get_args());
    }
    public static function renameFile(Ref3|Ref1|string $path, string $newName): Ref1 {
        return static::$instance->renameFile(...func_get_args());
    }
    public static function moveFile(Ref3|Ref1|string $path, string $destinationPath): Ref1 {
        return static::$instance->moveFile(...func_get_args());
    }
    public static function moveFileTo(Ref3|Ref1|string $path, string $destinationDir, ?string $newName = NULL): Ref1 {
        return static::$instance->moveFileTo(...func_get_args());
    }
    public static function deleteFile(Ref3|Ref1|string $path): void {}
    public static function dir(Ref3|Ref4|string $path): Ref4 {
        return static::$instance->dir(...func_get_args());
    }
    public static function existingDir(Ref3|Ref4|string $path): ?Ref4 {
        return static::$instance->existingDir(...func_get_args());
    }
    public static function createDir(Ref3|Ref4|string $path, ?int $permissions = NULL): Ref4 {
        return static::$instance->createDir(...func_get_args());
    }
    public static function createTempDir(): Ref4 {
        return static::$instance->createTempDir();
    }
    public static function hasDirChanged(Ref3|Ref4|string $path, int $seconds = 30): bool {
        return static::$instance->hasDirChanged(...func_get_args());
    }
    public static function hasDirChangedIn(Ref3|Ref4|string $path, Ref5|Ref3|string|int $timeout): bool {
        return static::$instance->hasDirChangedIn(...func_get_args());
    }
    public static function setDirPermissions(Ref3|Ref4|string $path, int $permissions): Ref4 {
        return static::$instance->setDirPermissions(...func_get_args());
    }
    public static function setDirPermissionsRecursive(Ref3|Ref4|string $path, int $permissions): Ref4 {
        return static::$instance->setDirPermissionsRecursive(...func_get_args());
    }
    public static function setDirOwner(Ref3|Ref4|string $path, int $owner): Ref4 {
        return static::$instance->setDirOwner(...func_get_args());
    }
    public static function setDirOwnerRecursive(Ref3|Ref4|string $path, int $owner): Ref4 {
        return static::$instance->setDirOwnerRecursive(...func_get_args());
    }
    public static function setDirGroup(Ref3|Ref4|string $path, int $group): Ref4 {
        return static::$instance->setDirGroup(...func_get_args());
    }
    public static function setDirGroupRecursive(Ref3|Ref4|string $path, int $group): Ref4 {
        return static::$instance->setDirGroupRecursive(...func_get_args());
    }
    public static function scan(Ref3|Ref4|string $path, ?callable $filter = NULL): Ref6 {
        return static::$instance->scan(...func_get_args());
    }
    public static function list(Ref3|Ref4|string $path, ?callable $filter = NULL): array {
        return static::$instance->list(...func_get_args());
    }
    public static function scanNames(Ref3|Ref4|string $path, ?callable $filter = NULL): Ref6 {
        return static::$instance->scanNames(...func_get_args());
    }
    public static function listNames(Ref3|Ref4|string $path, ?callable $filter = NULL): array {
        return static::$instance->listNames(...func_get_args());
    }
    public static function scanPaths(Ref3|Ref4|string $path, ?callable $filter = NULL): Ref6 {
        return static::$instance->scanPaths(...func_get_args());
    }
    public static function listPaths(Ref3|Ref4|string $path, ?callable $filter = NULL): array {
        return static::$instance->listPaths(...func_get_args());
    }
    public static function countContents(Ref3|Ref4|string $path, ?callable $filter = NULL): int {
        return static::$instance->countContents(...func_get_args());
    }
    public static function scanFiles(Ref3|Ref4|string $path, ?callable $filter = NULL): Ref6 {
        return static::$instance->scanFiles(...func_get_args());
    }
    public static function listFiles(Ref3|Ref4|string $path, ?callable $filter = NULL): array {
        return static::$instance->listFiles(...func_get_args());
    }
    public static function scanFileNames(Ref3|Ref4|string $path, ?callable $filter = NULL): Ref6 {
        return static::$instance->scanFileNames(...func_get_args());
    }
    public static function listFileNames(Ref3|Ref4|string $path, ?callable $filter = NULL): array {
        return static::$instance->listFileNames(...func_get_args());
    }
    public static function scanFilePaths(Ref3|Ref4|string $path, ?callable $filter = NULL): Ref6 {
        return static::$instance->scanFilePaths(...func_get_args());
    }
    public static function listFilePaths(Ref3|Ref4|string $path, ?callable $filter = NULL): array {
        return static::$instance->listFilePaths(...func_get_args());
    }
    public static function countFiles(Ref3|Ref4|string $path, ?callable $filter = NULL): int {
        return static::$instance->countFiles(...func_get_args());
    }
    public static function scanDirs(Ref3|Ref4|string $path, ?callable $filter = NULL): Ref6 {
        return static::$instance->scanDirs(...func_get_args());
    }
    public static function listDirs(Ref3|Ref4|string $path, ?callable $filter = NULL): array {
        return static::$instance->listDirs(...func_get_args());
    }
    public static function scanDirNames(Ref3|Ref4|string $path, ?callable $filter = NULL): Ref6 {
        return static::$instance->scanDirNames(...func_get_args());
    }
    public static function listDirNames(Ref3|Ref4|string $path, ?callable $filter = NULL): array {
        return static::$instance->listDirNames(...func_get_args());
    }
    public static function scanDirPaths(Ref3|Ref4|string $path, ?callable $filter = NULL): Ref6 {
        return static::$instance->scanDirPaths(...func_get_args());
    }
    public static function listDirPaths(Ref3|Ref4|string $path, ?callable $filter = NULL): array {
        return static::$instance->listDirPaths(...func_get_args());
    }
    public static function countDirs(Ref3|Ref4|string $path, ?callable $filter = NULL): int {
        return static::$instance->countDirs(...func_get_args());
    }
    public static function scanRecursive(Ref3|Ref4|string $path, ?callable $filter = NULL): Ref6 {
        return static::$instance->scanRecursive(...func_get_args());
    }
    public static function listRecursive(Ref3|Ref4|string $path, ?callable $filter = NULL): array {
        return static::$instance->listRecursive(...func_get_args());
    }
    public static function scanNamesRecursive(Ref3|Ref4|string $path, ?callable $filter = NULL): Ref6 {
        return static::$instance->scanNamesRecursive(...func_get_args());
    }
    public static function listNamesRecursive(Ref3|Ref4|string $path, ?callable $filter = NULL): array {
        return static::$instance->listNamesRecursive(...func_get_args());
    }
    public static function scanPathsRecursive(Ref3|Ref4|string $path, ?callable $filter = NULL): Ref6 {
        return static::$instance->scanPathsRecursive(...func_get_args());
    }
    public static function listPathsRecursive(Ref3|Ref4|string $path, ?callable $filter = NULL): array {
        return static::$instance->listPathsRecursive(...func_get_args());
    }
    public static function countContentsRecursive(Ref3|Ref4|string $path, ?callable $filter = NULL): int {
        return static::$instance->countContentsRecursive(...func_get_args());
    }
    public static function scanFilesRecursive(Ref3|Ref4|string $path, ?callable $filter = NULL): Ref6 {
        return static::$instance->scanFilesRecursive(...func_get_args());
    }
    public static function listFilesRecursive(Ref3|Ref4|string $path, ?callable $filter = NULL): array {
        return static::$instance->listFilesRecursive(...func_get_args());
    }
    public static function scanFileNamesRecursive(Ref3|Ref4|string $path, ?callable $filter = NULL): Ref6 {
        return static::$instance->scanFileNamesRecursive(...func_get_args());
    }
    public static function listFileNamesRecursive(Ref3|Ref4|string $path, ?callable $filter = NULL): array {
        return static::$instance->listFileNamesRecursive(...func_get_args());
    }
    public static function scanFilePathsRecursive(Ref3|Ref4|string $path, ?callable $filter = NULL): Ref6 {
        return static::$instance->scanFilePathsRecursive(...func_get_args());
    }
    public static function listFilePathsRecursive(Ref3|Ref4|string $path, ?callable $filter = NULL): array {
        return static::$instance->listFilePathsRecursive(...func_get_args());
    }
    public static function countFilesRecursive(Ref3|Ref4|string $path, ?callable $filter = NULL): int {
        return static::$instance->countFilesRecursive(...func_get_args());
    }
    public static function scanDirsRecursive(Ref3|Ref4|string $path, ?callable $filter = NULL): Ref6 {
        return static::$instance->scanDirsRecursive(...func_get_args());
    }
    public static function listDirsRecursive(Ref3|Ref4|string $path, ?callable $filter = NULL): array {
        return static::$instance->listDirsRecursive(...func_get_args());
    }
    public static function scanDirNamesRecursive(Ref3|Ref4|string $path, ?callable $filter = NULL): Ref6 {
        return static::$instance->scanDirNamesRecursive(...func_get_args());
    }
    public static function listDirNamesRecursive(Ref3|Ref4|string $path, ?callable $filter = NULL): array {
        return static::$instance->listDirNamesRecursive(...func_get_args());
    }
    public static function scanDirPathsRecursive(Ref3|Ref4|string $path, ?callable $filter = NULL): Ref6 {
        return static::$instance->scanDirPathsRecursive(...func_get_args());
    }
    public static function listDirPathsRecursive(Ref3|Ref4|string $path, ?callable $filter = NULL): array {
        return static::$instance->listDirPathsRecursive(...func_get_args());
    }
    public static function countDirsRecursive(Ref3|Ref4|string $path, ?callable $filter = NULL): int {
        return static::$instance->countDirsRecursive(...func_get_args());
    }
    public static function copyDir(Ref3|Ref4|string $path, string $destinationPath): Ref4 {
        return static::$instance->copyDir(...func_get_args());
    }
    public static function copyDirTo(Ref3|Ref4|string $path, string $destinationDir, ?string $newName = NULL): Ref4 {
        return static::$instance->copyDirTo(...func_get_args());
    }
    public static function renameDir(Ref3|Ref4|string $path, string $newName): Ref4 {
        return static::$instance->renameDir(...func_get_args());
    }
    public static function moveDir(Ref3|Ref4|string $path, string $destinationPath): Ref4 {
        return static::$instance->moveDir(...func_get_args());
    }
    public static function moveDirTo(Ref3|Ref4|string $path, string $destinationDir, ?string $newName = NULL): Ref4 {
        return static::$instance->moveDirTo(...func_get_args());
    }
    public static function deleteDir(Ref3|Ref4|string $path): void {}
    public static function emptyOut(Ref3|Ref4|string $path): Ref4 {
        return static::$instance->emptyOut(...func_get_args());
    }
    public static function merge(Ref3|Ref4|string $path, string $destination): Ref4 {
        return static::$instance->merge(...func_get_args());
    }
};
