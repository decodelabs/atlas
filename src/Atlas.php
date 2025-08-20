<?php

/**
 * @package Atlas
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs;

use DateInterval;
use DecodeLabs\Atlas\Dir;
use DecodeLabs\Atlas\Dir\Local as LocalDir;
use DecodeLabs\Atlas\File;
use DecodeLabs\Atlas\File\GzLocal as GzFile;
use DecodeLabs\Atlas\File\GzOpenable;
use DecodeLabs\Atlas\File\Local as LocalFile;
use DecodeLabs\Atlas\File\Memory as MemoryFile;
use DecodeLabs\Atlas\Mode;
use DecodeLabs\Atlas\Mutex\Local as LocalMutex;
use DecodeLabs\Atlas\Node;
use Generator;
use Stringable;

class Atlas
{
    public static function newMutex(
        string $name,
        string $dir
    ): LocalMutex {
        return new LocalMutex($name, $dir);
    }

    public static function newTempFile(): File
    {
        if (!$resource = tmpfile()) {
            throw Exceptional::Runtime(
                message: 'Unable to open temp file'
            );
        }

        return new LocalFile($resource);
    }

    public static function createTempFile(
        ?string $data
    ): File {
        $file = self::newTempFile();
        $file->write($data);
        return $file;
    }

    public static function newMemoryFile(
        string $key = 'temp'
    ): MemoryFile {
        return MemoryFile::create($key);
    }

    public static function createMemoryFile(
        ?string $data,
        string $key = 'temp'
    ): MemoryFile {
        $file = self::newMemoryFile($key);
        $file->write($data);
        return $file;
    }


    public static function get(
        string|Stringable|Dir|File $path
    ): Dir|File {
        if ($node = self::normalizeInput($path, Node::class)) {
            return $node;
        }

        if (is_dir((string)$path)) {
            return self::getDir($path);
        } else {
            return self::getFile($path);
        }
    }

    public static function getExisting(
        string $path
    ): Dir|File|null {
        if (is_dir($path)) {
            return self::getExistingDir($path);
        } else {
            return self::getExistingFile($path);
        }
    }

    public static function hasChanged(
        string|Stringable|Dir|File $path,
        int $seconds = 30
    ): bool {
        return self::get($path)->hasChanged($seconds);
    }

    public static function setPermissions(
        string|Stringable|Dir|File $path,
        int $permissions
    ): Dir|File {
        return self::get($path)->setPermissions($permissions);
    }

    public static function setPermissionsRecursive(
        string|Stringable|Dir|File $path,
        int $permissions
    ): Dir|File {
        $node = self::get($path);

        if ($node instanceof Dir) {
            $node->setPermissionsRecursive($permissions);
        } else {
            $node->setPermissions($permissions);
        }

        return $node;
    }

    public static function setOwner(
        string|Stringable|Dir|File $path,
        int $owner
    ): Dir|File {
        return self::get($path)->setOwner($owner);
    }

    public static function setGroup(
        string|Stringable|Dir|File $path,
        int $group
    ): Dir|File {
        return self::get($path)->setGroup($group);
    }

    public static function copy(
        string|Stringable|Dir|File $path,
        string $destinationPath
    ): Dir|File {
        return self::get($path)->copy($destinationPath);
    }

    public static function copyTo(
        string|Stringable|Dir|File $path,
        string $destinationDir,
        ?string $newName = null
    ): Dir|File {
        return self::get($path)->copyTo($destinationDir, $newName);
    }

    public static function rename(
        string|Stringable|Dir|File $path,
        string $newName
    ): Dir|File {
        return self::get($path)->renameTo($newName);
    }

    public static function move(
        string|Stringable|Dir|File $path,
        string $destinationPath
    ): Dir|File {
        return self::get($path)->move($destinationPath);
    }

    public static function moveTo(
        string|Stringable|Dir|File $path,
        string $destinationDir,
        ?string $newName = null
    ): Dir|File {
        return self::get($path)->moveTo($destinationDir, $newName);
    }

    public static function delete(
        string|Stringable|Dir|File $path
    ): void {
        self::get($path)->delete();
    }



    public static function getFile(
        string|Stringable|File $path,
        string|Mode|null $mode = null
    ): File {
        if (($node = self::normalizeInput($path, File::class)) instanceof File) {
            if ($mode !== null) {
                $node->open($mode);
            }

            return $node;
        }

        return new LocalFile((string)$path, $mode);
    }

    public static function getGzFile(
        string|Stringable|File $path,
        string|Mode $mode
    ): File {
        $node = self::normalizeInput($path, File::class);

        if ($node instanceof GzOpenable) {
            return $node->gzOpen($mode);
        }

        return new GzFile((string)$path, $mode);
    }


    public static function getExistingFile(
        string|Stringable|File $path,
        string|Mode|null $mode = null
    ): ?File {
        if (($node = self::normalizeInput($path, File::class)) instanceof File) {
            if (!$node->exists()) {
                return null;
            }

            if ($mode !== null) {
                $node->open($mode);
            }

            return $node;
        }

        $file = new LocalFile((string)$path);

        if (!$file->exists()) {
            return null;
        }

        if ($mode !== null) {
            $file->open($mode);
        }

        return $file;
    }

    public static function createFile(
        string|Stringable|File $path,
        mixed $data
    ): File {
        return self::getFile($path)->putContents($data);
    }

    public static function getContents(
        string|Stringable|File $path
    ): string {
        return self::getFile($path)->getContents();
    }

    public static function hasFileChanged(
        string|Stringable|File $path,
        int $seconds = 30
    ): bool {
        return self::getFile($path)->hasChanged($seconds);
    }

    public static function hasFileChangedIn(
        string|Stringable|File $path,
        DateInterval|string|Stringable|int $timeout
    ): bool {
        return self::getFile($path)->hasChangedIn($timeout);
    }

    public static function setFilePermissions(
        string|Stringable|File $path,
        int $permissions
    ): File {
        $file = self::getFile($path);
        $file->setPermissions($permissions);
        return $file;
    }

    public static function setFileOwner(
        string|Stringable|File $path,
        int $owner
    ): File {
        $file = self::getFile($path);
        $file->setOwner($owner);
        return $file;
    }

    public static function setFileGroup(
        string|Stringable|File $path,
        int $group
    ): File {
        $file = self::getFile($path);
        $file->setGroup($group);
        return $file;
    }

    public static function copyFile(
        string|Stringable|File $path,
        string $destinationPath
    ): File {
        $file = self::getFile($path);
        return $file->copy($destinationPath);
    }

    public static function copyFileTo(
        string|Stringable|File $path,
        string $destinationDir,
        ?string $newName = null
    ): File {
        $file = self::getFile($path);
        return $file->copyTo($destinationDir, $newName);
    }

    public static function renameFile(
        string|Stringable|File $path,
        string $newName
    ): File {
        $file = self::getFile($path);
        $file->renameTo($newName);
        return $file;
    }

    public static function moveFile(
        string|Stringable|File $path,
        string $destinationPath
    ): File {
        $file = self::getFile($path);
        $file->move($destinationPath);
        return $file;
    }

    public static function moveFileTo(
        string|Stringable|File $path,
        string $destinationDir,
        ?string $newName = null
    ): File {
        $file = self::getFile($path);
        $file->moveTo($destinationDir, $newName);
        return $file;
    }

    public static function deleteFile(
        string|Stringable|File $path
    ): void {
        self::getFile($path)->delete();
    }



    public static function getDir(
        string|Stringable|Dir $path
    ): Dir {
        if (($node = self::normalizeInput($path, Dir::class)) instanceof Dir) {
            return $node;
        }

        return new LocalDir((string)$path);
    }

    public static function getExistingDir(
        string|Stringable|Dir $path
    ): ?Dir {
        if (($node = self::normalizeInput($path, Dir::class)) instanceof Dir) {
            return $node->exists() ? $node : null;
        }

        $dir = new LocalDir((string)$path);

        if (!$dir->exists()) {
            return null;
        }

        return $dir;
    }

    public static function createDir(
        string|Stringable|Dir $path,
        ?int $permissions = null
    ): Dir {
        return self::getDir($path)->ensureExists($permissions);
    }

    public static function createTempDir(): Dir
    {
        return self::createDir(sys_get_temp_dir() . 'decodelabs/temp/' . uniqid('x', true));
    }

    public static function hasDirChanged(
        string|Stringable|Dir $path,
        int $seconds = 30
    ): bool {
        return self::getDir($path)->hasChanged($seconds);
    }

    public static function hasDirChangedIn(
        string|Stringable|Dir $path,
        DateInterval|string|Stringable|int $timeout
    ): bool {
        return self::getDir($path)->hasChangedIn($timeout);
    }

    public static function setDirPermissions(
        string|Stringable|Dir $path,
        int $permissions
    ): Dir {
        $dir = self::getDir($path);
        $dir->setPermissions($permissions);
        return $dir;
    }

    public static function setDirPermissionsRecursive(
        string|Stringable|Dir $path,
        int $permissions
    ): Dir {
        $dir = self::getDir($path);
        $dir->setPermissionsRecursive($permissions);
        return $dir;
    }

    public static function setDirOwner(
        string|Stringable|Dir $path,
        int $owner
    ): Dir {
        $dir = self::getDir($path);
        $dir->setOwner($owner);
        return $dir;
    }

    public static function setDirOwnerRecursive(
        string|Stringable|Dir $path,
        int $owner
    ): Dir {
        $dir = self::getDir($path);
        $dir->setOwnerRecursive($owner);
        return $dir;
    }

    public static function setDirGroup(
        string|Stringable|Dir $path,
        int $group
    ): Dir {
        $dir = self::getDir($path);
        $dir->setGroup($group);
        return $dir;
    }

    public static function setDirGroupRecursive(
        string|Stringable|Dir $path,
        int $group
    ): Dir {
        $dir = self::getDir($path);
        $dir->setGroupRecursive($group);
        return $dir;
    }




    /**
     * @return Generator<string, Dir|File>
     */
    public static function scan(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return self::getDir($path)->scan($filter);
    }

    /**
     * @return array<string, Dir|File>
     */
    public static function list(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return self::getDir($path)->list($filter);
    }

    /**
     * @return Generator<string>
     */
    public static function scanNames(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return self::getDir($path)->scanNames($filter);
    }

    /**
     * @return array<string>
     */
    public static function listNames(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return self::getDir($path)->listNames($filter);
    }

    /**
     * @return Generator<string, string>
     */
    public static function scanPaths(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return self::getDir($path)->scanPaths($filter);
    }

    /**
     * @return array<string, string>
     */
    public static function listPaths(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return self::getDir($path)->listPaths($filter);
    }

    public static function countContents(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): int {
        return self::getDir($path)->countContents($filter);
    }


    /**
     * @return Generator<string, File>
     */
    public static function scanFiles(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return self::getDir($path)->scanFiles($filter);
    }

    /**
     * @return array<string, File>
     */
    public static function listFiles(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return self::getDir($path)->listFiles($filter);
    }

    /**
     * @return Generator<string>
     */
    public static function scanFileNames(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return self::getDir($path)->scanFileNames($filter);
    }

    /**
     * @return array<string>
     */
    public static function listFileNames(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return self::getDir($path)->listFileNames($filter);
    }

    /**
     * @return Generator<string, string>
     */
    public static function scanFilePaths(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return self::getDir($path)->scanFilePaths($filter);
    }

    /**
     * @return array<string, string>
     */
    public static function listFilePaths(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return self::getDir($path)->listFilePaths($filter);
    }

    public static function countFiles(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): int {
        return self::getDir($path)->countFiles($filter);
    }


    /**
     * @return Generator<string, Dir>
     */
    public static function scanDirs(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return self::getDir($path)->scanDirs($filter);
    }

    /**
     * @return array<string, Dir>
     */
    public static function listDirs(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return self::getDir($path)->listDirs($filter);
    }

    /**
     * @return Generator<string>
     */
    public static function scanDirNames(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return self::getDir($path)->scanDirNames($filter);
    }

    /**
     * @return array<string>
     */
    public static function listDirNames(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return self::getDir($path)->listDirNames($filter);
    }

    /**
     * @return Generator<string, string>
     */
    public static function scanDirPaths(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return self::getDir($path)->scanDirPaths($filter);
    }

    /**
     * @return array<string, string>
     */
    public static function listDirPaths(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return self::getDir($path)->listDirPaths($filter);
    }

    public static function countDirs(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): int {
        return self::getDir($path)->countDirs($filter);
    }


    /**
     * @return Generator<string, Dir|File>
     */
    public static function scanRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return self::getDir($path)->scanRecursive($filter);
    }

    /**
     * @return array<string, Dir|File>
     */
    public static function listRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return self::getDir($path)->listRecursive($filter);
    }

    /**
     * @return Generator<string>
     */
    public static function scanNamesRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return self::getDir($path)->scanNamesRecursive($filter);
    }

    /**
     * @return array<string>
     */
    public static function listNamesRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return self::getDir($path)->listNamesRecursive($filter);
    }

    /**
     * @return Generator<string, string>
     */
    public static function scanPathsRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return self::getDir($path)->scanPathsRecursive($filter);
    }

    /**
     * @return array<string, string>
     */
    public static function listPathsRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return self::getDir($path)->listPathsRecursive($filter);
    }

    public static function countContentsRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): int {
        return self::getDir($path)->countContentsRecursive($filter);
    }


    /**
     * @return Generator<string, File>
     */
    public static function scanFilesRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return self::getDir($path)->scanFilesRecursive($filter);
    }

    /**
     * @return array<string, File>
     */
    public static function listFilesRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return self::getDir($path)->listFilesRecursive($filter);
    }

    /**
     * @return Generator<string>
     */
    public static function scanFileNamesRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return self::getDir($path)->scanFileNamesRecursive($filter);
    }

    /**
     * @return array<string>
     */
    public static function listFileNamesRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return self::getDir($path)->listFileNamesRecursive($filter);
    }

    /**
     * @return Generator<string, string>
     */
    public static function scanFilePathsRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return self::getDir($path)->scanFilePathsRecursive($filter);
    }

    /**
     * @param string|Stringable|Dir $path
     * @return array<string, string>
     */
    public static function listFilePathsRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return self::getDir($path)->listFilePathsRecursive($filter);
    }

    public static function countFilesRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): int {
        return self::getDir($path)->countFilesRecursive($filter);
    }


    /**
     * @return Generator<string, Dir>
     */
    public static function scanDirsRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return self::getDir($path)->scanDirsRecursive($filter);
    }

    /**
     * @return array<string, Dir>
     */
    public static function listDirsRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return self::getDir($path)->listDirsRecursive($filter);
    }

    /**
     * @return Generator<string>
     */
    public static function scanDirNamesRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return self::getDir($path)->scanDirNamesRecursive($filter);
    }

    /**
     * @return array<string>
     */
    public static function listDirNamesRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return self::getDir($path)->listDirNamesRecursive($filter);
    }

    /**
     * @return Generator<string, string>
     */
    public static function scanDirPathsRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return self::getDir($path)->scanDirPathsRecursive($filter);
    }

    /**
     * @return array<string, string>
     */
    public static function listDirPathsRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return self::getDir($path)->listDirPathsRecursive($filter);
    }

    public static function countDirsRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): int {
        return self::getDir($path)->countDirsRecursive($filter);
    }






    public static function copyDir(
        string|Stringable|Dir $path,
        string $destinationPath
    ): Dir {
        $dir = self::getDir($path);
        return $dir->copy($destinationPath);
    }

    public static function copyDirTo(
        string|Stringable|Dir $path,
        string $destinationDir,
        ?string $newName = null
    ): Dir {
        $dir = self::getDir($path);
        return $dir->copyTo($destinationDir, $newName);
    }

    public static function renameDir(
        string|Stringable|Dir $path,
        string $newName
    ): Dir {
        $dir = self::getDir($path);
        $dir->renameTo($newName);
        return $dir;
    }

    public static function moveDir(
        string|Stringable|Dir $path,
        string $destinationPath
    ): Dir {
        $dir = self::getDir($path);
        $dir->move($destinationPath);
        return $dir;
    }

    public static function moveDirTo(
        string|Stringable|Dir $path,
        string $destinationDir,
        ?string $newName = null
    ): Dir {
        $dir = self::getDir($path);
        $dir->moveTo($destinationDir, $newName);
        return $dir;
    }

    public static function deleteDir(
        string|Stringable|Dir $path
    ): void {
        self::getDir($path)->delete();
    }

    public static function emptyOut(
        string|Stringable|Dir $path
    ): Dir {
        return self::getDir($path)->emptyOut();
    }

    public static function merge(
        string|Stringable|Dir $path,
        string $destination
    ): Dir {
        return self::getDir($path)->mergeInto($destination);
    }



    /**
     * @param class-string $type
     */
    protected static function normalizeInput(
        string|Stringable|Dir|File &$path,
        string $type
    ): Dir|File|null {
        // Extract Node
        if (
            (
                $path instanceof Dir ||
                $path instanceof File
            ) &&
            $path instanceof $type
        ) {
            return $path;
        }

        if ($path instanceof Node) {
            throw Exceptional::InvalidArgument(
                message: 'Item is not a ' . $type
            );
        }

        // Extract path
        if ($path instanceof Stringable) {
            $path = (string)$path;
        }


        // Check types
        if (
            $type === Dir::class &&
            is_file($path)
        ) {
            throw Exceptional::Runtime(
                message: 'Path is a File not a Dir',
                data: $path
            );
        } elseif (
            $type === File::class &&
            is_dir($path)
        ) {
            throw Exceptional::Runtime(
                message: 'Path is a Dir not a File',
                data: $path
            );
        }

        return null;
    }
}
