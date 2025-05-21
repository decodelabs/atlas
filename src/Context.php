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
    public function newMutex(
        string $name,
        string $dir
    ): LocalMutex {
        return new LocalMutex($name, $dir);
    }

    public function newTempFile(): File
    {
        if (!$resource = tmpfile()) {
            throw Exceptional::Runtime(
                message: 'Unable to open temp file'
            );
        }

        return new LocalFile($resource);
    }

    public function createTempFile(
        ?string $data
    ): File {
        $file = $this->newTempFile();
        $file->write($data);
        return $file;
    }

    public function newMemoryFile(
        string $key = 'temp'
    ): MemoryFile {
        return MemoryFile::create($key);
    }

    public function createMemoryFile(
        ?string $data,
        string $key = 'temp'
    ): MemoryFile {
        $file = $this->newMemoryFile($key);
        $file->write($data);
        return $file;
    }


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

    public function getExisting(
        string $path
    ): Dir|File|null {
        if (is_dir($path)) {
            return $this->existingDir($path);
        } else {
            return $this->existingFile($path);
        }
    }

    public function hasChanged(
        string|Stringable|Dir|File $path,
        int $seconds = 30
    ): bool {
        return $this->get($path)->hasChanged($seconds);
    }

    public function setPermissions(
        string|Stringable|Dir|File $path,
        int $permissions
    ): Dir|File {
        return $this->get($path)->setPermissions($permissions);
    }

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

    public function setOwner(
        string|Stringable|Dir|File $path,
        int $owner
    ): Dir|File {
        return $this->get($path)->setOwner($owner);
    }

    public function setGroup(
        string|Stringable|Dir|File $path,
        int $group
    ): Dir|File {
        return $this->get($path)->setGroup($group);
    }

    public function copy(
        string|Stringable|Dir|File $path,
        string $destinationPath
    ): Dir|File {
        return $this->get($path)->copy($destinationPath);
    }

    public function copyTo(
        string|Stringable|Dir|File $path,
        string $destinationDir,
        ?string $newName = null
    ): Dir|File {
        return $this->get($path)->copyTo($destinationDir, $newName);
    }

    public function rename(
        string|Stringable|Dir|File $path,
        string $newName
    ): Dir|File {
        return $this->get($path)->renameTo($newName);
    }

    public function move(
        string|Stringable|Dir|File $path,
        string $destinationPath
    ): Dir|File {
        return $this->get($path)->move($destinationPath);
    }

    public function moveTo(
        string|Stringable|Dir|File $path,
        string $destinationDir,
        ?string $newName = null
    ): Dir|File {
        return $this->get($path)->moveTo($destinationDir, $newName);
    }

    public function delete(
        string|Stringable|Dir|File $path
    ): void {
        $this->get($path)->delete();
    }



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

    public function createFile(
        string|Stringable|File $path,
        mixed $data
    ): File {
        return $this->file($path)->putContents($data);
    }

    public function getContents(
        string|Stringable|File $path
    ): string {
        return $this->file($path)->getContents();
    }

    public function hasFileChanged(
        string|Stringable|File $path,
        int $seconds = 30
    ): bool {
        return $this->file($path)->hasChanged($seconds);
    }

    public function hasFileChangedIn(
        string|Stringable|File $path,
        DateInterval|string|Stringable|int $timeout
    ): bool {
        return $this->file($path)->hasChangedIn($timeout);
    }

    public function setFilePermissions(
        string|Stringable|File $path,
        int $permissions
    ): File {
        $file = $this->file($path);
        $file->setPermissions($permissions);
        return $file;
    }

    public function setFileOwner(
        string|Stringable|File $path,
        int $owner
    ): File {
        $file = $this->file($path);
        $file->setOwner($owner);
        return $file;
    }

    public function setFileGroup(
        string|Stringable|File $path,
        int $group
    ): File {
        $file = $this->file($path);
        $file->setGroup($group);
        return $file;
    }

    public function copyFile(
        string|Stringable|File $path,
        string $destinationPath
    ): File {
        $file = $this->file($path);
        return $file->copy($destinationPath);
    }

    public function copyFileTo(
        string|Stringable|File $path,
        string $destinationDir,
        ?string $newName = null
    ): File {
        $file = $this->file($path);
        return $file->copyTo($destinationDir, $newName);
    }

    public function renameFile(
        string|Stringable|File $path,
        string $newName
    ): File {
        $file = $this->file($path);
        $file->renameTo($newName);
        return $file;
    }

    public function moveFile(
        string|Stringable|File $path,
        string $destinationPath
    ): File {
        $file = $this->file($path);
        $file->move($destinationPath);
        return $file;
    }

    public function moveFileTo(
        string|Stringable|File $path,
        string $destinationDir,
        ?string $newName = null
    ): File {
        $file = $this->file($path);
        $file->moveTo($destinationDir, $newName);
        return $file;
    }

    public function deleteFile(
        string|Stringable|File $path
    ): void {
        $this->file($path)->delete();
    }



    public function dir(
        string|Stringable|Dir $path
    ): Dir {
        if (($node = $this->normalizeInput($path, Dir::class)) instanceof Dir) {
            return $node;
        }

        return new LocalDir((string)$path);
    }

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

    public function createDir(
        string|Stringable|Dir $path,
        ?int $permissions = null
    ): Dir {
        return $this->dir($path)->ensureExists($permissions);
    }

    public function createTempDir(): Dir
    {
        return $this->createDir(sys_get_temp_dir() . 'decodelabs/temp/' . uniqid('x', true));
    }

    public function hasDirChanged(
        string|Stringable|Dir $path,
        int $seconds = 30
    ): bool {
        return $this->dir($path)->hasChanged($seconds);
    }

    public function hasDirChangedIn(
        string|Stringable|Dir $path,
        DateInterval|string|Stringable|int $timeout
    ): bool {
        return $this->dir($path)->hasChangedIn($timeout);
    }

    public function setDirPermissions(
        string|Stringable|Dir $path,
        int $permissions
    ): Dir {
        $dir = $this->dir($path);
        $dir->setPermissions($permissions);
        return $dir;
    }

    public function setDirPermissionsRecursive(
        string|Stringable|Dir $path,
        int $permissions
    ): Dir {
        $dir = $this->dir($path);
        $dir->setPermissionsRecursive($permissions);
        return $dir;
    }

    public function setDirOwner(
        string|Stringable|Dir $path,
        int $owner
    ): Dir {
        $dir = $this->dir($path);
        $dir->setOwner($owner);
        return $dir;
    }

    public function setDirOwnerRecursive(
        string|Stringable|Dir $path,
        int $owner
    ): Dir {
        $dir = $this->dir($path);
        $dir->setOwnerRecursive($owner);
        return $dir;
    }

    public function setDirGroup(
        string|Stringable|Dir $path,
        int $group
    ): Dir {
        $dir = $this->dir($path);
        $dir->setGroup($group);
        return $dir;
    }

    public function setDirGroupRecursive(
        string|Stringable|Dir $path,
        int $group
    ): Dir {
        $dir = $this->dir($path);
        $dir->setGroupRecursive($group);
        return $dir;
    }




    /**
     * @return Generator<string, Dir|File>
     */
    public function scan(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return $this->dir($path)->scan($filter);
    }

    /**
     * @return array<string, Dir|File>
     */
    public function list(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return $this->dir($path)->list($filter);
    }

    /**
     * @return Generator<string>
     */
    public function scanNames(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return $this->dir($path)->scanNames($filter);
    }

    /**
     * @return array<string>
     */
    public function listNames(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return $this->dir($path)->listNames($filter);
    }

    /**
     * @return Generator<string, string>
     */
    public function scanPaths(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return $this->dir($path)->scanPaths($filter);
    }

    /**
     * @return array<string, string>
     */
    public function listPaths(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return $this->dir($path)->listPaths($filter);
    }

    public function countContents(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): int {
        return $this->dir($path)->countContents($filter);
    }


    /**
     * @return Generator<string, File>
     */
    public function scanFiles(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return $this->dir($path)->scanFiles($filter);
    }

    /**
     * @return array<string, File>
     */
    public function listFiles(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return $this->dir($path)->listFiles($filter);
    }

    /**
     * @return Generator<string>
     */
    public function scanFileNames(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return $this->dir($path)->scanFileNames($filter);
    }

    /**
     * @return array<string>
     */
    public function listFileNames(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return $this->dir($path)->listFileNames($filter);
    }

    /**
     * @return Generator<string, string>
     */
    public function scanFilePaths(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return $this->dir($path)->scanFilePaths($filter);
    }

    /**
     * @return array<string, string>
     */
    public function listFilePaths(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return $this->dir($path)->listFilePaths($filter);
    }

    public function countFiles(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): int {
        return $this->dir($path)->countFiles($filter);
    }


    /**
     * @return Generator<string, Dir>
     */
    public function scanDirs(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return $this->dir($path)->scanDirs($filter);
    }

    /**
     * @return array<string, Dir>
     */
    public function listDirs(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return $this->dir($path)->listDirs($filter);
    }

    /**
     * @return Generator<string>
     */
    public function scanDirNames(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return $this->dir($path)->scanDirNames($filter);
    }

    /**
     * @return array<string>
     */
    public function listDirNames(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return $this->dir($path)->listDirNames($filter);
    }

    /**
     * @return Generator<string, string>
     */
    public function scanDirPaths(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return $this->dir($path)->scanDirPaths($filter);
    }

    /**
     * @return array<string, string>
     */
    public function listDirPaths(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return $this->dir($path)->listDirPaths($filter);
    }

    public function countDirs(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): int {
        return $this->dir($path)->countDirs($filter);
    }


    /**
     * @return Generator<string, Dir|File>
     */
    public function scanRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return $this->dir($path)->scanRecursive($filter);
    }

    /**
     * @return array<string, Dir|File>
     */
    public function listRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return $this->dir($path)->listRecursive($filter);
    }

    /**
     * @return Generator<string>
     */
    public function scanNamesRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return $this->dir($path)->scanNamesRecursive($filter);
    }

    /**
     * @return array<string>
     */
    public function listNamesRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return $this->dir($path)->listNamesRecursive($filter);
    }

    /**
     * @return Generator<string, string>
     */
    public function scanPathsRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return $this->dir($path)->scanPathsRecursive($filter);
    }

    /**
     * @return array<string, string>
     */
    public function listPathsRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return $this->dir($path)->listPathsRecursive($filter);
    }

    public function countContentsRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): int {
        return $this->dir($path)->countContentsRecursive($filter);
    }


    /**
     * @return Generator<string, File>
     */
    public function scanFilesRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return $this->dir($path)->scanFilesRecursive($filter);
    }

    /**
     * @return array<string, File>
     */
    public function listFilesRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return $this->dir($path)->listFilesRecursive($filter);
    }

    /**
     * @return Generator<string>
     */
    public function scanFileNamesRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return $this->dir($path)->scanFileNamesRecursive($filter);
    }

    /**
     * @return array<string>
     */
    public function listFileNamesRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return $this->dir($path)->listFileNamesRecursive($filter);
    }

    /**
     * @return Generator<string, string>
     */
    public function scanFilePathsRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return $this->dir($path)->scanFilePathsRecursive($filter);
    }

    /**
     * @param string|Stringable|Dir $path
     * @return array<string, string>
     */
    public function listFilePathsRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return $this->dir($path)->listFilePathsRecursive($filter);
    }

    public function countFilesRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): int {
        return $this->dir($path)->countFilesRecursive($filter);
    }


    /**
     * @return Generator<string, Dir>
     */
    public function scanDirsRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return $this->dir($path)->scanDirsRecursive($filter);
    }

    /**
     * @return array<string, Dir>
     */
    public function listDirsRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return $this->dir($path)->listDirsRecursive($filter);
    }

    /**
     * @return Generator<string>
     */
    public function scanDirNamesRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return $this->dir($path)->scanDirNamesRecursive($filter);
    }

    /**
     * @return array<string>
     */
    public function listDirNamesRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return $this->dir($path)->listDirNamesRecursive($filter);
    }

    /**
     * @return Generator<string, string>
     */
    public function scanDirPathsRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): Generator {
        return $this->dir($path)->scanDirPathsRecursive($filter);
    }

    /**
     * @return array<string, string>
     */
    public function listDirPathsRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): array {
        return $this->dir($path)->listDirPathsRecursive($filter);
    }

    public function countDirsRecursive(
        string|Stringable|Dir $path,
        ?callable $filter = null
    ): int {
        return $this->dir($path)->countDirsRecursive($filter);
    }






    public function copyDir(
        string|Stringable|Dir $path,
        string $destinationPath
    ): Dir {
        $dir = $this->dir($path);
        return $dir->copy($destinationPath);
    }

    public function copyDirTo(
        string|Stringable|Dir $path,
        string $destinationDir,
        ?string $newName = null
    ): Dir {
        $dir = $this->dir($path);
        return $dir->copyTo($destinationDir, $newName);
    }

    public function renameDir(
        string|Stringable|Dir $path,
        string $newName
    ): Dir {
        $dir = $this->dir($path);
        $dir->renameTo($newName);
        return $dir;
    }

    public function moveDir(
        string|Stringable|Dir $path,
        string $destinationPath
    ): Dir {
        $dir = $this->dir($path);
        $dir->move($destinationPath);
        return $dir;
    }

    public function moveDirTo(
        string|Stringable|Dir $path,
        string $destinationDir,
        ?string $newName = null
    ): Dir {
        $dir = $this->dir($path);
        $dir->moveTo($destinationDir, $newName);
        return $dir;
    }

    public function deleteDir(
        string|Stringable|Dir $path
    ): void {
        $this->dir($path)->delete();
    }

    public function emptyOut(
        string|Stringable|Dir $path
    ): Dir {
        return $this->dir($path)->emptyOut();
    }

    public function merge(
        string|Stringable|Dir $path,
        string $destination
    ): Dir {
        return $this->dir($path)->mergeInto($destination);
    }



    /**
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
