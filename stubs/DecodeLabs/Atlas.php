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
use DecodeLabs\Atlas\Mode as Ref5;
use DateInterval as Ref6;
use Generator as Ref7;

class Atlas implements Proxy
{
    use ProxyTrait;

    public const Veneer = 'DecodeLabs\\Atlas';
    public const VeneerTarget = Inst::class;

    protected static Inst $_veneerInstance;

    public static function newMutex(string $name, string $dir): Ref0 {
        return static::$_veneerInstance->newMutex(...func_get_args());
    }
    public static function newTempFile(): Ref1 {
        return static::$_veneerInstance->newTempFile();
    }
    public static function createTempFile(?string $data): Ref1 {
        return static::$_veneerInstance->createTempFile(...func_get_args());
    }
    public static function newMemoryFile(string $key = 'temp'): Ref2 {
        return static::$_veneerInstance->newMemoryFile(...func_get_args());
    }
    public static function createMemoryFile(?string $data, string $key = 'temp'): Ref2 {
        return static::$_veneerInstance->createMemoryFile(...func_get_args());
    }
    public static function get(Ref3|Ref4|Ref1|string $path): Ref4|Ref1 {
        return static::$_veneerInstance->get(...func_get_args());
    }
    public static function getExisting(string $path): Ref4|Ref1|null {
        return static::$_veneerInstance->getExisting(...func_get_args());
    }
    public static function hasChanged(Ref3|Ref4|Ref1|string $path, int $seconds = 30): bool {
        return static::$_veneerInstance->hasChanged(...func_get_args());
    }
    public static function setPermissions(Ref3|Ref4|Ref1|string $path, int $permissions): Ref4|Ref1 {
        return static::$_veneerInstance->setPermissions(...func_get_args());
    }
    public static function setPermissionsRecursive(Ref3|Ref4|Ref1|string $path, int $permissions): Ref4|Ref1 {
        return static::$_veneerInstance->setPermissionsRecursive(...func_get_args());
    }
    public static function setOwner(Ref3|Ref4|Ref1|string $path, int $owner): Ref4|Ref1 {
        return static::$_veneerInstance->setOwner(...func_get_args());
    }
    public static function setGroup(Ref3|Ref4|Ref1|string $path, int $group): Ref4|Ref1 {
        return static::$_veneerInstance->setGroup(...func_get_args());
    }
    public static function copy(Ref3|Ref4|Ref1|string $path, string $destinationPath): Ref4|Ref1 {
        return static::$_veneerInstance->copy(...func_get_args());
    }
    public static function copyTo(Ref3|Ref4|Ref1|string $path, string $destinationDir, ?string $newName = NULL): Ref4|Ref1 {
        return static::$_veneerInstance->copyTo(...func_get_args());
    }
    public static function rename(Ref3|Ref4|Ref1|string $path, string $newName): Ref4|Ref1 {
        return static::$_veneerInstance->rename(...func_get_args());
    }
    public static function move(Ref3|Ref4|Ref1|string $path, string $destinationPath): Ref4|Ref1 {
        return static::$_veneerInstance->move(...func_get_args());
    }
    public static function moveTo(Ref3|Ref4|Ref1|string $path, string $destinationDir, ?string $newName = NULL): Ref4|Ref1 {
        return static::$_veneerInstance->moveTo(...func_get_args());
    }
    public static function delete(Ref3|Ref4|Ref1|string $path): void {}
    public static function file(Ref3|Ref1|string $path, Ref5|string|null $mode = NULL): Ref1 {
        return static::$_veneerInstance->file(...func_get_args());
    }
    public static function gzFile(Ref3|Ref1|string $path, Ref5|string $mode): Ref1 {
        return static::$_veneerInstance->gzFile(...func_get_args());
    }
    public static function existingFile(Ref3|Ref1|string $path, Ref5|string|null $mode = NULL): ?Ref1 {
        return static::$_veneerInstance->existingFile(...func_get_args());
    }
    public static function createFile(Ref3|Ref1|string $path, mixed $data): Ref1 {
        return static::$_veneerInstance->createFile(...func_get_args());
    }
    public static function getContents(Ref3|Ref1|string $path): string {
        return static::$_veneerInstance->getContents(...func_get_args());
    }
    public static function hasFileChanged(Ref3|Ref1|string $path, int $seconds = 30): bool {
        return static::$_veneerInstance->hasFileChanged(...func_get_args());
    }
    public static function hasFileChangedIn(Ref3|Ref1|string $path, Ref6|Ref3|string|int $timeout): bool {
        return static::$_veneerInstance->hasFileChangedIn(...func_get_args());
    }
    public static function setFilePermissions(Ref3|Ref1|string $path, int $permissions): Ref1 {
        return static::$_veneerInstance->setFilePermissions(...func_get_args());
    }
    public static function setFileOwner(Ref3|Ref1|string $path, int $owner): Ref1 {
        return static::$_veneerInstance->setFileOwner(...func_get_args());
    }
    public static function setFileGroup(Ref3|Ref1|string $path, int $group): Ref1 {
        return static::$_veneerInstance->setFileGroup(...func_get_args());
    }
    public static function copyFile(Ref3|Ref1|string $path, string $destinationPath): Ref1 {
        return static::$_veneerInstance->copyFile(...func_get_args());
    }
    public static function copyFileTo(Ref3|Ref1|string $path, string $destinationDir, ?string $newName = NULL): Ref1 {
        return static::$_veneerInstance->copyFileTo(...func_get_args());
    }
    public static function renameFile(Ref3|Ref1|string $path, string $newName): Ref1 {
        return static::$_veneerInstance->renameFile(...func_get_args());
    }
    public static function moveFile(Ref3|Ref1|string $path, string $destinationPath): Ref1 {
        return static::$_veneerInstance->moveFile(...func_get_args());
    }
    public static function moveFileTo(Ref3|Ref1|string $path, string $destinationDir, ?string $newName = NULL): Ref1 {
        return static::$_veneerInstance->moveFileTo(...func_get_args());
    }
    public static function deleteFile(Ref3|Ref1|string $path): void {}
    public static function dir(Ref3|Ref4|string $path): Ref4 {
        return static::$_veneerInstance->dir(...func_get_args());
    }
    public static function existingDir(Ref3|Ref4|string $path): ?Ref4 {
        return static::$_veneerInstance->existingDir(...func_get_args());
    }
    public static function createDir(Ref3|Ref4|string $path, ?int $permissions = NULL): Ref4 {
        return static::$_veneerInstance->createDir(...func_get_args());
    }
    public static function createTempDir(): Ref4 {
        return static::$_veneerInstance->createTempDir();
    }
    public static function hasDirChanged(Ref3|Ref4|string $path, int $seconds = 30): bool {
        return static::$_veneerInstance->hasDirChanged(...func_get_args());
    }
    public static function hasDirChangedIn(Ref3|Ref4|string $path, Ref6|Ref3|string|int $timeout): bool {
        return static::$_veneerInstance->hasDirChangedIn(...func_get_args());
    }
    public static function setDirPermissions(Ref3|Ref4|string $path, int $permissions): Ref4 {
        return static::$_veneerInstance->setDirPermissions(...func_get_args());
    }
    public static function setDirPermissionsRecursive(Ref3|Ref4|string $path, int $permissions): Ref4 {
        return static::$_veneerInstance->setDirPermissionsRecursive(...func_get_args());
    }
    public static function setDirOwner(Ref3|Ref4|string $path, int $owner): Ref4 {
        return static::$_veneerInstance->setDirOwner(...func_get_args());
    }
    public static function setDirOwnerRecursive(Ref3|Ref4|string $path, int $owner): Ref4 {
        return static::$_veneerInstance->setDirOwnerRecursive(...func_get_args());
    }
    public static function setDirGroup(Ref3|Ref4|string $path, int $group): Ref4 {
        return static::$_veneerInstance->setDirGroup(...func_get_args());
    }
    public static function setDirGroupRecursive(Ref3|Ref4|string $path, int $group): Ref4 {
        return static::$_veneerInstance->setDirGroupRecursive(...func_get_args());
    }
    public static function scan(Ref3|Ref4|string $path, ?callable $filter = NULL): Ref7 {
        return static::$_veneerInstance->scan(...func_get_args());
    }
    public static function list(Ref3|Ref4|string $path, ?callable $filter = NULL): array {
        return static::$_veneerInstance->list(...func_get_args());
    }
    public static function scanNames(Ref3|Ref4|string $path, ?callable $filter = NULL): Ref7 {
        return static::$_veneerInstance->scanNames(...func_get_args());
    }
    public static function listNames(Ref3|Ref4|string $path, ?callable $filter = NULL): array {
        return static::$_veneerInstance->listNames(...func_get_args());
    }
    public static function scanPaths(Ref3|Ref4|string $path, ?callable $filter = NULL): Ref7 {
        return static::$_veneerInstance->scanPaths(...func_get_args());
    }
    public static function listPaths(Ref3|Ref4|string $path, ?callable $filter = NULL): array {
        return static::$_veneerInstance->listPaths(...func_get_args());
    }
    public static function countContents(Ref3|Ref4|string $path, ?callable $filter = NULL): int {
        return static::$_veneerInstance->countContents(...func_get_args());
    }
    public static function scanFiles(Ref3|Ref4|string $path, ?callable $filter = NULL): Ref7 {
        return static::$_veneerInstance->scanFiles(...func_get_args());
    }
    public static function listFiles(Ref3|Ref4|string $path, ?callable $filter = NULL): array {
        return static::$_veneerInstance->listFiles(...func_get_args());
    }
    public static function scanFileNames(Ref3|Ref4|string $path, ?callable $filter = NULL): Ref7 {
        return static::$_veneerInstance->scanFileNames(...func_get_args());
    }
    public static function listFileNames(Ref3|Ref4|string $path, ?callable $filter = NULL): array {
        return static::$_veneerInstance->listFileNames(...func_get_args());
    }
    public static function scanFilePaths(Ref3|Ref4|string $path, ?callable $filter = NULL): Ref7 {
        return static::$_veneerInstance->scanFilePaths(...func_get_args());
    }
    public static function listFilePaths(Ref3|Ref4|string $path, ?callable $filter = NULL): array {
        return static::$_veneerInstance->listFilePaths(...func_get_args());
    }
    public static function countFiles(Ref3|Ref4|string $path, ?callable $filter = NULL): int {
        return static::$_veneerInstance->countFiles(...func_get_args());
    }
    public static function scanDirs(Ref3|Ref4|string $path, ?callable $filter = NULL): Ref7 {
        return static::$_veneerInstance->scanDirs(...func_get_args());
    }
    public static function listDirs(Ref3|Ref4|string $path, ?callable $filter = NULL): array {
        return static::$_veneerInstance->listDirs(...func_get_args());
    }
    public static function scanDirNames(Ref3|Ref4|string $path, ?callable $filter = NULL): Ref7 {
        return static::$_veneerInstance->scanDirNames(...func_get_args());
    }
    public static function listDirNames(Ref3|Ref4|string $path, ?callable $filter = NULL): array {
        return static::$_veneerInstance->listDirNames(...func_get_args());
    }
    public static function scanDirPaths(Ref3|Ref4|string $path, ?callable $filter = NULL): Ref7 {
        return static::$_veneerInstance->scanDirPaths(...func_get_args());
    }
    public static function listDirPaths(Ref3|Ref4|string $path, ?callable $filter = NULL): array {
        return static::$_veneerInstance->listDirPaths(...func_get_args());
    }
    public static function countDirs(Ref3|Ref4|string $path, ?callable $filter = NULL): int {
        return static::$_veneerInstance->countDirs(...func_get_args());
    }
    public static function scanRecursive(Ref3|Ref4|string $path, ?callable $filter = NULL): Ref7 {
        return static::$_veneerInstance->scanRecursive(...func_get_args());
    }
    public static function listRecursive(Ref3|Ref4|string $path, ?callable $filter = NULL): array {
        return static::$_veneerInstance->listRecursive(...func_get_args());
    }
    public static function scanNamesRecursive(Ref3|Ref4|string $path, ?callable $filter = NULL): Ref7 {
        return static::$_veneerInstance->scanNamesRecursive(...func_get_args());
    }
    public static function listNamesRecursive(Ref3|Ref4|string $path, ?callable $filter = NULL): array {
        return static::$_veneerInstance->listNamesRecursive(...func_get_args());
    }
    public static function scanPathsRecursive(Ref3|Ref4|string $path, ?callable $filter = NULL): Ref7 {
        return static::$_veneerInstance->scanPathsRecursive(...func_get_args());
    }
    public static function listPathsRecursive(Ref3|Ref4|string $path, ?callable $filter = NULL): array {
        return static::$_veneerInstance->listPathsRecursive(...func_get_args());
    }
    public static function countContentsRecursive(Ref3|Ref4|string $path, ?callable $filter = NULL): int {
        return static::$_veneerInstance->countContentsRecursive(...func_get_args());
    }
    public static function scanFilesRecursive(Ref3|Ref4|string $path, ?callable $filter = NULL): Ref7 {
        return static::$_veneerInstance->scanFilesRecursive(...func_get_args());
    }
    public static function listFilesRecursive(Ref3|Ref4|string $path, ?callable $filter = NULL): array {
        return static::$_veneerInstance->listFilesRecursive(...func_get_args());
    }
    public static function scanFileNamesRecursive(Ref3|Ref4|string $path, ?callable $filter = NULL): Ref7 {
        return static::$_veneerInstance->scanFileNamesRecursive(...func_get_args());
    }
    public static function listFileNamesRecursive(Ref3|Ref4|string $path, ?callable $filter = NULL): array {
        return static::$_veneerInstance->listFileNamesRecursive(...func_get_args());
    }
    public static function scanFilePathsRecursive(Ref3|Ref4|string $path, ?callable $filter = NULL): Ref7 {
        return static::$_veneerInstance->scanFilePathsRecursive(...func_get_args());
    }
    public static function listFilePathsRecursive(Ref3|Ref4|string $path, ?callable $filter = NULL): array {
        return static::$_veneerInstance->listFilePathsRecursive(...func_get_args());
    }
    public static function countFilesRecursive(Ref3|Ref4|string $path, ?callable $filter = NULL): int {
        return static::$_veneerInstance->countFilesRecursive(...func_get_args());
    }
    public static function scanDirsRecursive(Ref3|Ref4|string $path, ?callable $filter = NULL): Ref7 {
        return static::$_veneerInstance->scanDirsRecursive(...func_get_args());
    }
    public static function listDirsRecursive(Ref3|Ref4|string $path, ?callable $filter = NULL): array {
        return static::$_veneerInstance->listDirsRecursive(...func_get_args());
    }
    public static function scanDirNamesRecursive(Ref3|Ref4|string $path, ?callable $filter = NULL): Ref7 {
        return static::$_veneerInstance->scanDirNamesRecursive(...func_get_args());
    }
    public static function listDirNamesRecursive(Ref3|Ref4|string $path, ?callable $filter = NULL): array {
        return static::$_veneerInstance->listDirNamesRecursive(...func_get_args());
    }
    public static function scanDirPathsRecursive(Ref3|Ref4|string $path, ?callable $filter = NULL): Ref7 {
        return static::$_veneerInstance->scanDirPathsRecursive(...func_get_args());
    }
    public static function listDirPathsRecursive(Ref3|Ref4|string $path, ?callable $filter = NULL): array {
        return static::$_veneerInstance->listDirPathsRecursive(...func_get_args());
    }
    public static function countDirsRecursive(Ref3|Ref4|string $path, ?callable $filter = NULL): int {
        return static::$_veneerInstance->countDirsRecursive(...func_get_args());
    }
    public static function copyDir(Ref3|Ref4|string $path, string $destinationPath): Ref4 {
        return static::$_veneerInstance->copyDir(...func_get_args());
    }
    public static function copyDirTo(Ref3|Ref4|string $path, string $destinationDir, ?string $newName = NULL): Ref4 {
        return static::$_veneerInstance->copyDirTo(...func_get_args());
    }
    public static function renameDir(Ref3|Ref4|string $path, string $newName): Ref4 {
        return static::$_veneerInstance->renameDir(...func_get_args());
    }
    public static function moveDir(Ref3|Ref4|string $path, string $destinationPath): Ref4 {
        return static::$_veneerInstance->moveDir(...func_get_args());
    }
    public static function moveDirTo(Ref3|Ref4|string $path, string $destinationDir, ?string $newName = NULL): Ref4 {
        return static::$_veneerInstance->moveDirTo(...func_get_args());
    }
    public static function deleteDir(Ref3|Ref4|string $path): void {}
    public static function emptyOut(Ref3|Ref4|string $path): Ref4 {
        return static::$_veneerInstance->emptyOut(...func_get_args());
    }
    public static function merge(Ref3|Ref4|string $path, string $destination): Ref4 {
        return static::$_veneerInstance->merge(...func_get_args());
    }
};
