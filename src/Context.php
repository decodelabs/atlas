<?php

/**
 * @package Atlas
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Atlas;

use DateInterval;
use DecodeLabs\Atlas;
use DecodeLabs\Atlas\Dir\Local as LocalDir;
use DecodeLabs\Atlas\File\GzLocal as GzFile;
use DecodeLabs\Atlas\File\GzOpenable;
use DecodeLabs\Atlas\File\Local as LocalFile;
use DecodeLabs\Atlas\File\Memory as MemoryFile;
use DecodeLabs\Atlas\Mutex\Local as LocalMutex;
use DecodeLabs\Exceptional;
use DecodeLabs\Veneer;
use Generator;
use Stringable;

class Context
{
    /**
     * Create a new local Mutex
     */
    public function newMutex(
        string $name,
        string $dir
    ): LocalMutex {
        return new LocalMutex($name, $dir);
    }

    /**
     * Create a new empty temp file
     */
    public function newTempFile(): File
    {
        if (!$resource = tmpfile()) {
            throw Exceptional::Runtime(
                message: 'Unable to open temp file'
            );
        }

        return new LocalFile($resource);
    }

    /**
     * Create a new temp file
     */
    public function createTempFile(
        ?string $data
    ): File {
        $file = $this->newTempFile();
        $file->write($data);
        return $file;
    }

    /**
     * Create a new empty memory file
     */
    public function newMemoryFile(
        string $key = 'temp'
    ): MemoryFile {
        return MemoryFile::create($key);
    }

    /**
     * Create a new memory file with data
     */
    public function createMemoryFile(
        ?string $data,
        string $key = 'temp'
    ): MemoryFile {
        $file = $this->newMemoryFile($key);
        $file->write($data);
        return $file;
    }


    /**
     * Get node, return file or dir depending on what's on disk
     */
    public function get(
        string|Stringable|Dir|File $path
    ): Dir|File {
        if ($node = $this->normalizeInput($path, Node::class)) {
            return $node;
        }

        if (is_dir((string)$path)) {
            return $this->dir($path);
        } else {
            return $this->file($path);
        }
    }

    /**
     * Get existing node, return file or dir depending on what's on disk
     */
    public function getExisting(
        string $path
    ): Dir|File|null {
        if (is_dir($path)) {
            return $this->existingDir($path);
        } else {
            return $this->existingFile($path);
        }
    }

    /**
     * Check if modified time is within $seconds
     */
    public function hasChanged(
        string|Stringable|Dir|File $path,
        int $seconds = 30
    ): bool {
        return $this->get($path)->hasChanged($seconds);
    }

    /**
     * Set file permissions on file or dir
     */
    public function setPermissions(
        string|Stringable|Dir|File $path,
        int $permissions
    ): Dir|File {
        return $this->get($path)->setPermissions($permissions);
    }

    /**
     * Set file permissions on file or dir recursively
     */
    public function setPermissionsRecursive(
        string|Stringable|Dir|File $path,
        int $permissions
    ): Dir|File {
        $node = $this->get($path);

        if ($node instanceof Dir) {
            $node->setPermissionsRecursive($permissions);
        } else {
            $node->setPermissions($permissions);
        }

        return $node;
    }

    /**
     * Set owner for file or dir
     */
    public function setOwner(
        string|Stringable|Dir|File $path,
        int $owner
    ): Dir|File {
        return $this->get($path)->setOwner($owner);
    }

    /**
     * Set group for file or dir
     */
    public function setGroup(
        string|Stringable|Dir|File $path,
        int $group
    ): Dir|File {
        return $this->get($path)->setGroup($group);
    }

    /**
     * Copy file or dir to $destinationPath
     */
    public function copy(
        string|Stringable|Dir|File $path,
        string $destinationPath
    ): Dir|File {
        return $this->get($path)->copy($destinationPath);
    }

    /**
     * Copy file or dir to $destinationDir, rename basename to $newName if set
     */
    public function copyTo(
        string|Stringable|Dir|File $path,
        string $destinationDir,
        ?string $newName = null
    ): Dir|File {
        return $this->get($path)->copyTo($destinationDir, $newName);
    }

    /**
     * Rename basename of file or dir
     */
    public function rename(
        string|Stringable|Dir|File $path,
        string $newName
    ): Dir|File {
        return $this->get($path)->renameTo($newName);
    }

    /**
     * Move file or dir to $destinationPath
     */
    public function move(
        string|Stringable|Dir|File $path,
        string $destinationPath
    ): Dir|File {
        return $this->get($path)->move($destinationPath);
    }

    /**
     * Move file or dir to $destinationDir, rename basename to $newName if set
     */
    public function moveTo(
        string|Stringable|Dir|File $path,
        string $destinationDir,
        ?string $newName = null
    ): Dir|File {
        return $this->get($path)->moveTo($destinationDir, $newName);
    }

    /**
     * Delete file or dir
     */
    public function delete(
        string|Stringable|Dir|File $path
    ): void {
        $this->get($path)->delete();
    }



    /**
     * Load file from $path, open if $mode is set
     */
    public function file(
        string|Stringable|File $path,
        string|Mode|null $mode = null
    ): File {
        if (($node = $this->normalizeInput($path, File::class)) instanceof File) {
            if ($mode !== null) {
                $node->open($mode);
            }

            return $node;
        }

        return new LocalFile((string)$path, $mode);
    }

    /**
     * Load file from $path, open if $mode is set
     */
    public function gzFile(
        string|Stringable|File $path,
        string|Mode $mode
    ): File {
        $node = $this->normalizeInput($path, File::class);

        if ($node instanceof GzOpenable) {
            return $node->gzOpen($mode);
        }

        return new GzFile((string)$path, $mode);
    }


    /**
     * Load existing file from $path, open if $mode is set
     */
    public function existingFile(
        string|Stringable|File $path,
        string|Mode|null $mode = null
    ): ?File {
        if (($node = $this->normalizeInput($path, File::class)) instanceof File) {
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

    /**
     * Create a new file with $data
     */
    public function createFile(
        string|Stringable|File $path,
        mixed $data
    ): File {
        return $this->file($path)->putContents($data);
    }

    /**
     * Get contents of file at $path
     */
    public function getContents(
        string|Stringable|File $path
    ): string {
        return $this->file($path)->getContents();
    }

    /**
     * Check file last modified within $seconds
     */
    public function hasFileChanged(
        string|Stringable|File $path,
        int $seconds = 30
    ): bool {
        return $this->file($path)->hasChanged($seconds);
    }

    /**
     * Check file last modified within $time
     */
    public function hasFileChangedIn(
        string|Stringable|File $path,
        DateInterval|string|Stringable|int $timeout
    ): bool {
        return $this->file($path)->hasChangedIn($timeout);
    }

    /**
     * Set permissions of file
     */
    public function setFilePermissions(
        string|Stringable|File $path,
        int $permissions
    ): File {
        $file = $this->file($path);
        $file->setPermissions($permissions);
        return $file;
    }

    /**
     * Set owner of file
     */
    public function setFileOwner(
        string|Stringable|File $path,
        int $owner
    ): File {
        $file = $this->file($path);
        $file->setOwner($owner);
        return $file;
    }

    /**
     * Set group of file
     */
    public function setFileGroup(
        string|Stringable|File $path,
        int $group
    ): File {
        $file = $this->file($path);
        $file->setGroup($group);
        return $file;
    }

    /**
     * Copy file to $destinationPath
     */
    public function copyFile(
        string|Stringable|File $path,
        string $destinationPath
    ): File {
        $file = $this->file($path);
        return $file->copy($destinationPath);
    }

    /**
     * Copy file to $destinationDir, rename basename to $newName if set
     */
    public function copyFileTo(
        string|Stringable|File $path,
        string $destinationDir,
        ?string $newName = null
    ): File {
        $file = $this->file($path);
        return $file->copyTo($destinationDir, $newName);
    }

    /**
     * Rename basename of file
     */
    public function renameFile(
        string|Stringable|File $path,
        string $newName
    ): File {
        $file = $this->file($path);
        $file->renameTo($newName);
        return $file;
    }

    /**
     * Move file to $destinationPath
     */
    public function moveFile(
        string|Stringable|File $path,
        string $destinationPath
    ): File {
        $file = $this->file($path);
        $file->move($destinationPath);
        return $file;
    }

    /**
     * Move file to $destinationDir, rename basename to $newName if set
     */
    public function moveFileTo(
        string|Stringable|File $path,
        string $destinationDir,
        ?string $newName = null
    ): File {
        $file = $this->file($path);
        $file->moveTo($destinationDir, $newName);
        return $file;
    }

    /**
     * Delete file
     */
    public function deleteFile(
        string|Stringable|File $path
    ): void {
        $this->file($path)->delete();
    }



    /**
     * Load dir from path
     */
    public function dir(
        string|Stringable|Dir $path
    ): Dir {
        if (($node = $this->normalizeInput($path, Dir::class)) instanceof Dir) {
            return $node;
        }

        return new LocalDir((string)$path);
    }

    /**
     * Load existing dir from path
     */
    public function existingDir(
        string|Stringable|Dir $path
    ): ?Dir {
        if (($node = $this->normalizeInput($path, Dir::class)) instanceof Dir) {
            return $node->exists() ? $node : null;
        }

        $dir = new LocalDir((string)$path);

        if (!$dir->exists()) {
            return null;
        }

        return $dir;
    }

    /**
     * Ensure directory at $path exists with $permissions
     */
    public function createDir(
        string|Stringable|Dir $path,
        ?int $permissions = null
    ): Dir {
        return $this->dir($path)->ensureExists($permissions);
    }

    /**
     * Create system level temp dir
     */
    public function createTempDir(): Dir
    {
        return $this->createDir(sys_get_temp_dir() . 'decodelabs/temp/' . uniqid('x', true));
    }

    /**
     * Check last modified of dir within $seconds
     */
    public function hasDirChanged(
        string|Stringable|Dir $path,
        int $seconds = 30
    ): bool {
        return $this->dir($path)->hasChanged($seconds);
    }

    /**
     * Check dir last modified within $time
     */
    public function hasDirChangedIn(
        string|Stringable|Dir $path,
        DateInterval|string|Stringable|int $timeout
    ): bool {
        return $this->dir($path)->hasChangedIn($timeout);
    }

    /**
     * Set permissions on dir
     */
    public function setDirPermissions(
        string|Stringable|Dir $path,
        int $permissions
    ): Dir {
        $dir = $this->dir($path);
        $dir->setPermissions($permissions);
        return $dir;
    }

    /**
     * Set permissions on dir recursively
     */
    public function setDirPermissionsRecursive(
        string|Stringable|Dir $path,
        int $permissions
    ): Dir {
        $dir = $this->dir($path);
        $dir->setPermissionsRecursive($permissions);
        return $dir;
    }

    /**
     * Set owner of dir
     */
    public function setDirOwner(
        string|Stringable|Dir $path,
        int $owner
    ): Dir {
        $dir = $this->dir($path);
        $dir->setOwner($owner);
        return $dir;
    }

    /**
     * Set owner of dir recursively
     */
    public function setDirOwnerRecursive(
        string|Stringable|Dir $path,
        int $owner
    ): Dir {
        $dir = $this->dir($path);
        $dir->setOwnerRecursive($owner);
        return $dir;
    }

    /**
     * Set group of dir
     */
    public function setDirGroup(
        string|Stringable|Dir $path,
        int $group
    ): Dir {
        $dir = $this->dir($path);
        $dir->setGroup($group);
        return $dir;
    }

    /**
     * Set group of dir recursively
     */
    public function setDirGroupRecursive(
        string|Stringable|Dir $path,
        int $group
    ): Dir {
        $dir = $this->dir($path);
        $dir->setGroupRecursive($group);
        return $dir;
    }




    /**
     * Scan all children as File or Dir objects
     *
     * @return Generator<string, Dir|File>
     */
    public function scan(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return $this->dir($path)->scan($filter);
    }

    /**
     * List all children as File or Dir objects
     *
     * @return array<string, Dir|File>
     */
    public function list(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return $this->dir($path)->list($filter);
    }

    /**
     * Scan all children as names
     *
     * @return Generator<string>
     */
    public function scanNames(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return $this->dir($path)->scanNames($filter);
    }

    /**
     * List all children as names
     *
     * @return array<string>
     */
    public function listNames(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return $this->dir($path)->listNames($filter);
    }

    /**
     * Scan all children as paths
     *
     * @return Generator<string, string>
     */
    public function scanPaths(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return $this->dir($path)->scanPaths($filter);
    }

    /**
     * List all children as paths
     *
     * @return array<string, string>
     */
    public function listPaths(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return $this->dir($path)->listPaths($filter);
    }

    /**
     * Count all children
     */
    public function countContents(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): int {
        return $this->dir($path)->countContents($filter);
    }


    /**
     * Scan all files as File objects
     *
     * @return Generator<string, File>
     */
    public function scanFiles(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return $this->dir($path)->scanFiles($filter);
    }

    /**
     * List all files as File objects
     *
     * @return array<string, File>
     */
    public function listFiles(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return $this->dir($path)->listFiles($filter);
    }

    /**
     * Scan all files as names
     *
     * @return Generator<string>
     */
    public function scanFileNames(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return $this->dir($path)->scanFileNames($filter);
    }

    /**
     * List all files as names
     *
     * @return array<string>
     */
    public function listFileNames(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return $this->dir($path)->listFileNames($filter);
    }

    /**
     * Scan all files as paths
     *
     * @return Generator<string, string>
     */
    public function scanFilePaths(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return $this->dir($path)->scanFilePaths($filter);
    }

    /**
     * List all files as paths
     *
     * @return array<string, string>
     */
    public function listFilePaths(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return $this->dir($path)->listFilePaths($filter);
    }

    /**
     * Count all files
     */
    public function countFiles(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): int {
        return $this->dir($path)->countFiles($filter);
    }


    /**
     * Scan all dirs as Dir objects
     *
     * @return Generator<string, Dir>
     */
    public function scanDirs(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return $this->dir($path)->scanDirs($filter);
    }

    /**
     * List all dirs as Dir objects
     *
     * @return array<string, Dir>
     */
    public function listDirs(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return $this->dir($path)->listDirs($filter);
    }

    /**
     * Scan all dirs as names
     *
     * @return Generator<string>
     */
    public function scanDirNames(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return $this->dir($path)->scanDirNames($filter);
    }

    /**
     * List all dirs as names
     *
     * @return array<string>
     */
    public function listDirNames(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return $this->dir($path)->listDirNames($filter);
    }

    /**
     * Scan all dirs as paths
     *
     * @return Generator<string, string>
     */
    public function scanDirPaths(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return $this->dir($path)->scanDirPaths($filter);
    }

    /**
     * List all dirs as paths
     *
     * @return array<string, string>
     */
    public function listDirPaths(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return $this->dir($path)->listDirPaths($filter);
    }

    /**
     * Count all dirs
     */
    public function countDirs(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): int {
        return $this->dir($path)->countDirs($filter);
    }


    /**
     * Scan all children recursively as File or Dir objects
     *
     * @return Generator<string, Dir|File>
     */
    public function scanRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return $this->dir($path)->scanRecursive($filter);
    }

    /**
     * List all children recursively as File or Dir objects
     *
     * @return array<string, Dir|File>
     */
    public function listRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return $this->dir($path)->listRecursive($filter);
    }

    /**
     * Scan all children recursively as names
     *
     * @return Generator<string>
     */
    public function scanNamesRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return $this->dir($path)->scanNamesRecursive($filter);
    }

    /**
     * List all children recursively as names
     *
     * @return array<string>
     */
    public function listNamesRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return $this->dir($path)->listNamesRecursive($filter);
    }

    /**
     * Scan all children recursively as paths
     *
     * @return Generator<string, string>
     */
    public function scanPathsRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return $this->dir($path)->scanPathsRecursive($filter);
    }

    /**
     * List all children recursively as paths
     *
     * @return array<string, string>
     */
    public function listPathsRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return $this->dir($path)->listPathsRecursive($filter);
    }

    /**
     * Count all children recursively
     */
    public function countContentsRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): int {
        return $this->dir($path)->countContentsRecursive($filter);
    }


    /**
     * Scan all files recursively as File objects
     *
     * @return Generator<string, File>
     */
    public function scanFilesRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return $this->dir($path)->scanFilesRecursive($filter);
    }

    /**
     * List all files recursively as File objects
     *
     * @return array<string, File>
     */
    public function listFilesRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return $this->dir($path)->listFilesRecursive($filter);
    }

    /**
     * Scan all files recursively as names
     *
     * @return Generator<string>
     */
    public function scanFileNamesRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return $this->dir($path)->scanFileNamesRecursive($filter);
    }

    /**
     * List all files recursively as names
     *
     * @return array<string>
     */
    public function listFileNamesRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return $this->dir($path)->listFileNamesRecursive($filter);
    }

    /**
     * Scan all files recursively as paths
     *
     * @return Generator<string, string>
     */
    public function scanFilePathsRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return $this->dir($path)->scanFilePathsRecursive($filter);
    }

    /**
     * List all files recursively as paths
     *
     * @param string|Stringable|Dir $path
     * @return array<string, string>
     */
    public function listFilePathsRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return $this->dir($path)->listFilePathsRecursive($filter);
    }

    /**
     * Count all files recursively
     */
    public function countFilesRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): int {
        return $this->dir($path)->countFilesRecursive($filter);
    }


    /**
     * Scan all dirs recursively as Dir objects
     *
     * @return Generator<string, Dir>
     */
    public function scanDirsRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return $this->dir($path)->scanDirsRecursive($filter);
    }

    /**
     * List all dirs recursively as Dir objects
     *
     * @return array<string, Dir>
     */
    public function listDirsRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return $this->dir($path)->listDirsRecursive($filter);
    }

    /**
     * Scan all dirs recursively as names
     *
     * @return Generator<string>
     */
    public function scanDirNamesRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return $this->dir($path)->scanDirNamesRecursive($filter);
    }

    /**
     * List all dirs recursively as names
     *
     * @return array<string>
     */
    public function listDirNamesRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return $this->dir($path)->listDirNamesRecursive($filter);
    }

    /**
     * Scan all dirs recursively as paths
     *
     * @return Generator<string, string>
     */
    public function scanDirPathsRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return $this->dir($path)->scanDirPathsRecursive($filter);
    }

    /**
     * List all dirs recursively as paths
     *
     * @return array<string, string>
     */
    public function listDirPathsRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return $this->dir($path)->listDirPathsRecursive($filter);
    }

    /**
     * Count all dirs recursively
     */
    public function countDirsRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): int {
        return $this->dir($path)->countDirsRecursive($filter);
    }






    /**
     * Copy dir to $destinationPath
     */
    public function copyDir(
        string|Stringable|Dir $path,
        string $destinationPath
    ): Dir {
        $dir = $this->dir($path);
        return $dir->copy($destinationPath);
    }

    /**
     * Copy dir to $destinationDir, rename basename to $newName if set
     */
    public function copyDirTo(
        string|Stringable|Dir $path,
        string $destinationDir,
        ?string $newName = null
    ): Dir {
        $dir = $this->dir($path);
        return $dir->copyTo($destinationDir, $newName);
    }

    /**
     * Rename basename of dir
     */
    public function renameDir(
        string|Stringable|Dir $path,
        string $newName
    ): Dir {
        $dir = $this->dir($path);
        $dir->renameTo($newName);
        return $dir;
    }

    /**
     * Move dir to $destinationPath
     */
    public function moveDir(
        string|Stringable|Dir $path,
        string $destinationPath
    ): Dir {
        $dir = $this->dir($path);
        $dir->move($destinationPath);
        return $dir;
    }

    /**
     * Move dir to $destinationDir, rename basename to $newName if set
     */
    public function moveDirTo(
        string|Stringable|Dir $path,
        string $destinationDir,
        ?string $newName = null
    ): Dir {
        $dir = $this->dir($path);
        $dir->moveTo($destinationDir, $newName);
        return $dir;
    }

    /**
     * Delete dir and contents
     */
    public function deleteDir(
        string|Stringable|Dir $path
    ): void {
        $this->dir($path)->delete();
    }

    /**
     * Delete contents of dir
     */
    public function emptyOut(
        string|Stringable|Dir $path
    ): Dir {
        return $this->dir($path)->emptyOut();
    }

    /**
     * Merge contents of dir into $destination dir
     */
    public function merge(
        string|Stringable|Dir $path,
        string $destination
    ): Dir {
        return $this->dir($path)->mergeInto($destination);
    }



    /**
     * Normalize node input
     *
     * @param class-string $type
     */
    protected function normalizeInput(
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

// Register the Veneer facade
Veneer\Manager::getGlobalManager()->register(
    Context::class,
    Atlas::class
);
