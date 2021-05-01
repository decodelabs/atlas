<?php

/**
 * @package Atlas
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Atlas;

use DecodeLabs\Atlas\Dir\Local as LocalDir;
use DecodeLabs\Atlas\File\Local as LocalFile;
use DecodeLabs\Atlas\File\Memory as MemoryFile;
use DecodeLabs\Atlas\Mutex\Local as LocalMutex;
use DecodeLabs\Atlas\Plugins\Http as HttpPlugin;


use DecodeLabs\Exceptional;

use DecodeLabs\Veneer\Plugin\AccessTarget as VeneerPluginAccessTarget;
use DecodeLabs\Veneer\Plugin\AccessTargetTrait as VeneerPluginAccessTargetTrait;
use DecodeLabs\Veneer\Plugin as VeneerPlugin;
use DecodeLabs\Veneer\Plugin\Provider as VeneerPluginProvider;
use DecodeLabs\Veneer\Plugin\ProviderTrait as VeneerPluginProviderTrait;

use Generator;
use Stringable;

/**
 * @property HttpPlugin $http
 */
class Context implements VeneerPluginProvider, VeneerPluginAccessTarget
{
    use VeneerPluginProviderTrait;
    use VeneerPluginAccessTargetTrait;

    public const PLUGINS = [
        'http'
    ];


    /**
     * Stub to get empty plugin list to avoid broken targets
     */
    public function getVeneerPluginNames(): array
    {
        return static::PLUGINS;
    }


    /**
     * Load factory plugins
     */
    public function loadVeneerPlugin(string $name): VeneerPlugin
    {
        if (!in_array($name, self::PLUGINS)) {
            throw Exceptional::InvalidArgument(
                $name . ' is not a recognised Veneer plugin'
            );
        }

        $class = '\\DecodeLabs\\Atlas\\Plugins\\' . ucfirst($name);
        return new $class($this);
    }


    /**
     * Create a new local Mutex
     */
    public function newMutex(string $name, string $dir): LocalMutex
    {
        return new LocalMutex($name, $dir);
    }

    /**
     * Create a new empty temp file
     */
    public function newTempFile(): File
    {
        if (!$resource = tmpfile()) {
            throw Exceptional::Runtime('Unable to open temp file');
        }

        return new LocalFile($resource);
    }

    /**
     * Create a new temp file
     */
    public function createTempFile(?string $data): File
    {
        $file = $this->newTempFile();
        $file->write($data);
        return $file;
    }

    /**
     * Create a new empty memory file
     */
    public function newMemoryFile(string $key = 'temp'): MemoryFile
    {
        return MemoryFile::create($key);
    }

    /**
     * Create a new memory file with data
     */
    public function createMemoryFile(?string $data, string $key = 'temp'): MemoryFile
    {
        $file = $this->newMemoryFile($key);
        $file->write($data);
        return $file;
    }


    /**
     * Get node, return file or dir depending on what's on disk
     *
     * @param string|Stringable|Dir|File $path
     * @return Dir|File
     */
    public function get($path): Node
    {
        if ($node = $this->normalizeInput($path, Node::class)) {
            return $node;
        }

        if (is_dir($path)) {
            return $this->dir($path);
        } else {
            return $this->file($path);
        }
    }

    /**
     * Get existing node, return file or dir depending on what's on disk
     *
     * @return Dir|File|null
     */
    public function getExisting(string $path): ?Node
    {
        if (is_dir($path)) {
            return $this->existingDir($path);
        } else {
            return $this->existingFile($path);
        }
    }

    /**
     * Check if modified time is within $seconds
     *
     * @param string|Stringable|Dir|File $path
     */
    public function hasChanged($path, int $seconds = 30): bool
    {
        return $this->get($path)->hasChanged($seconds);
    }

    /**
     * Set file permissions on file or dir
     *
     * @param string|Stringable|Dir|File $path
     * @return Dir|File
     */
    public function setPermissions($path, int $permissions): Node
    {
        return $this->get($path)->setPermissions($permissions);
    }

    /**
     * Set file permissions on file or dir recursively
     *
     * @param string|Stringable|Dir|File $path
     * @return Dir|File
     */
    public function setPermissionsRecursive($path, int $permissions): Node
    {
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
     *
     * @param string|Stringable|Dir|File $path
     * @return Dir|File
     */
    public function setOwner($path, int $owner): Node
    {
        return $this->get($path)->setOwner($owner);
    }

    /**
     * Set group for file or dir
     *
     * @param string|Stringable|Dir|File $path
     * @return Dir|File
     */
    public function setGroup($path, int $group): Node
    {
        return $this->get($path)->setGroup($group);
    }

    /**
     * Copy file or dir to $destinationPath
     *
     * @param string|Stringable|Dir|File $path
     * @return Dir|File
     */
    public function copy($path, string $destinationPath): Node
    {
        return $this->get($path)->copy($destinationPath);
    }

    /**
     * Copy file or dir to $destinationDir, rename basename to $newName if set
     *
     * @param string|Stringable|Dir|File $path
     * @return Dir|File
     */
    public function copyTo($path, string $destinationDir, string $newName = null): Node
    {
        return $this->get($path)->copyTo($destinationDir, $newName);
    }

    /**
     * Rename basename of file or dir
     *
     * @param string|Stringable|Dir|File $path
     * @return Dir|File
     */
    public function rename($path, string $newName): Node
    {
        return $this->get($path)->renameTo($newName);
    }

    /**
     * Move file or dir to $destinationPath
     *
     * @param string|Stringable|Dir|File $path
     * @return Dir|File
     */
    public function move($path, string $destinationPath): Node
    {
        return $this->get($path)->move($destinationPath);
    }

    /**
     * Move file or dir to $destinationDir, rename basename to $newName if set
     *
     * @param string|Stringable|Dir|File $path
     * @return Dir|File
     */
    public function moveTo($path, string $destinationDir, string $newName = null): Node
    {
        return $this->get($path)->moveTo($destinationDir, $newName);
    }

    /**
     * Delete file or dir
     *
     * @param string|Stringable|Dir|File $path
     */
    public function delete($path): void
    {
        $this->get($path)->delete();
    }



    /**
     * Load file from $path, open if $mode is set
     *
     * @param string|Stringable|File $path
     */
    public function file($path, string $mode = null): File
    {
        if (($node = $this->normalizeInput($path, File::class)) instanceof File) {
            if ($mode !== null) {
                $node->open($mode);
            }

            return $node;
        }

        return new LocalFile($path, $mode);
    }

    /**
     * Load existing file from $path, open if $mode is set
     *
     * @param string|Stringable|File $path
     */
    public function existingFile($path, string $mode = null): ?File
    {
        if (($node = $this->normalizeInput($path, File::class)) instanceof File) {
            if (!$node->exists()) {
                return null;
            }

            if ($mode !== null) {
                $node->open($mode);
            }

            return $node;
        }

        $file = new LocalFile($path);

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
     *
     * @param string|Stringable|File $path
     * @param mixed $data
     */
    public function createFile($path, $data): File
    {
        return $this->file($path)->putContents($data);
    }

    /**
     * Get contents of file at $path
     *
     * @param string|Stringable|File $path
     */
    public function getContents($path): string
    {
        return $this->file($path)->getContents();
    }

    /**
     * Check file last modified within $seconds
     *
     * @param string|Stringable|File $path
     */
    public function hasFileChanged($path, int $seconds = 30): bool
    {
        return $this->file($path)->hasChanged($seconds);
    }

    /**
     * Check file last modified within $time
     *
     * @param string|Stringable|File $path
     */
    public function hasFileChangedIn($path, string $timeout): bool
    {
        return $this->file($path)->hasChangedIn($timeout);
    }

    /**
     * Set permissions of file
     *
     * @param string|Stringable|File $path
     */
    public function setFilePermissions($path, int $permissions): File
    {
        $file = $this->file($path);
        $file->setPermissions($permissions);
        return $file;
    }

    /**
     * Set owner of file
     *
     * @param string|Stringable|File $path
     */
    public function setFileOwner($path, int $owner): File
    {
        $file = $this->file($path);
        $file->setOwner($owner);
        return $file;
    }

    /**
     * Set group of file
     *
     * @param string|Stringable|File $path
     */
    public function setFileGroup($path, int $group): File
    {
        $file = $this->file($path);
        $file->setGroup($group);
        return $file;
    }

    /**
     * Copy file to $destinationPath
     *
     * @param string|Stringable|File $path
     */
    public function copyFile($path, string $destinationPath): File
    {
        $file = $this->file($path);
        $output = $file->copy($destinationPath);

        if (!$output instanceof File) {
            throw Exceptional::UnexpectedValue(
                'Output of file copy() was not a file',
                null,
                $output
            );
        }

        return $output;
    }

    /**
     * Copy file to $destinationDir, rename basename to $newName if set
     *
     * @param string|Stringable|File $path
     */
    public function copyFileTo($path, string $destinationDir, string $newName = null): File
    {
        $file = $this->file($path);
        $output = $file->copyTo($destinationDir, $newName);

        if (!$output instanceof File) {
            throw Exceptional::UnexpectedValue(
                'Output of file copy() was not a file',
                null,
                $output
            );
        }

        return $output;
    }

    /**
     * Rename basename of file
     *
     * @param string|Stringable|File $path
     */
    public function renameFile($path, string $newName): File
    {
        $file = $this->file($path);
        $file->renameTo($newName);
        return $file;
    }

    /**
     * Move file to $destinationPath
     *
     * @param string|Stringable|File $path
     */
    public function moveFile($path, string $destinationPath): File
    {
        $file = $this->file($path);
        $file->move($destinationPath);
        return $file;
    }

    /**
     * Move file to $destinationDir, rename basename to $newName if set
     *
     * @param string|Stringable|File $path
     */
    public function moveFileTo($path, string $destinationDir, string $newName = null): File
    {
        $file = $this->file($path);
        $file->moveTo($destinationDir, $newName);
        return $file;
    }

    /**
     * Delete file
     *
     * @param string|Stringable|File $path
     */
    public function deleteFile($path): void
    {
        $this->file($path)->delete();
    }



    /**
     * Load dir from path
     *
     * @param string|Stringable|Dir $path
     */
    public function dir($path): Dir
    {
        if (($node = $this->normalizeInput($path, Dir::class)) instanceof Dir) {
            return $node;
        }

        return new LocalDir($path);
    }

    /**
     * Load existing dir from path
     *
     * @param string|Stringable|Dir $path
     */
    public function existingDir($path): ?Dir
    {
        if (($node = $this->normalizeInput($path, Dir::class)) instanceof Dir) {
            return $node->exists() ? $node : null;
        }

        $dir = new LocalDir($path);

        if (!$dir->exists()) {
            return null;
        }

        return $dir;
    }

    /**
     * Ensure directory at $path exists with $permissions
     *
     * @param string|Stringable|Dir $path
     */
    public function createDir($path, int $permissions = null): Dir
    {
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
     *
     * @param string|Stringable|Dir $path
     */
    public function hasDirChanged($path, int $seconds = 30): bool
    {
        return $this->dir($path)->hasChanged($seconds);
    }

    /**
     * Check dir last modified within $time
     *
     * @param string|Stringable|Dir $path
     */
    public function hasDirChangedIn($path, string $timeout): bool
    {
        return $this->dir($path)->hasChangedIn($timeout);
    }

    /**
     * Set permissions on dir
     *
     * @param string|Stringable|Dir $path
     */
    public function setDirPermissions($path, int $permissions): Dir
    {
        $dir = $this->dir($path);
        $dir->setPermissions($permissions);
        return $dir;
    }

    /**
     * Set permissions on dir recursively
     *
     * @param string|Stringable|Dir $path
     */
    public function setDirPermissionsRecursive($path, int $permissions): Dir
    {
        $dir = $this->dir($path);
        $dir->setPermissionsRecursive($permissions);
        return $dir;
    }

    /**
     * Set owner of dir
     *
     * @param string|Stringable|Dir $path
     */
    public function setDirOwner($path, int $owner): Dir
    {
        $dir = $this->dir($path);
        $dir->setOwner($owner);
        return $dir;
    }

    /**
     * Set owner of dir recursively
     *
     * @param string|Stringable|Dir $path
     */
    public function setDirOwnerRecursive($path, int $owner): Dir
    {
        $dir = $this->dir($path);
        $dir->setOwnerRecursive($owner);
        return $dir;
    }

    /**
     * Set group of dir
     *
     * @param string|Stringable|Dir $path
     */
    public function setDirGroup($path, int $group): Dir
    {
        $dir = $this->dir($path);
        $dir->setGroup($group);
        return $dir;
    }

    /**
     * Set group of dir recursively
     *
     * @param string|Stringable|Dir $path
     */
    public function setDirGroupRecursive($path, int $group): Dir
    {
        $dir = $this->dir($path);
        $dir->setGroupRecursive($group);
        return $dir;
    }




    /**
     * Scan all children as File or Dir objects
     *
     * @param string|Stringable|Dir $path
     * @return Generator<string, Dir|File>
     */
    public function scan($path, callable $filter = null): Generator
    {
        return $this->dir($path)->scan($filter);
    }

    /**
     * List all children as File or Dir objects
     *
     * @param string|Stringable|Dir $path
     * @return array<string, Dir|File>
     */
    public function list($path, callable $filter = null): array
    {
        return $this->dir($path)->list($filter);
    }

    /**
     * Scan all children as names
     *
     * @param string|Stringable|Dir $path
     * @return Generator<string>
     */
    public function scanNames($path, callable $filter = null): Generator
    {
        return $this->dir($path)->scanNames($filter);
    }

    /**
     * List all children as names
     *
     * @param string|Stringable|Dir $path
     * @return array<string>
     */
    public function listNames($path, callable $filter = null): array
    {
        return $this->dir($path)->listNames($filter);
    }

    /**
     * Scan all children as paths
     *
     * @param string|Stringable|Dir $path
     * @return Generator<string, string>
     */
    public function scanPaths($path, callable $filter = null): Generator
    {
        return $this->dir($path)->scanPaths($filter);
    }

    /**
     * List all children as paths
     *
     * @param string|Stringable|Dir $path
     * @return array<string, string>
     */
    public function listPaths($path, callable $filter = null): array
    {
        return $this->dir($path)->listPaths($filter);
    }

    /**
     * Count all children
     *
     * @param string|Stringable|Dir $path
     */
    public function countContents($path, callable $filter = null): int
    {
        return $this->dir($path)->countContents($filter);
    }


    /**
     * Scan all files as File objects
     *
     * @param string|Stringable|Dir $path
     * @return Generator<string, File>
     */
    public function scanFiles($path, callable $filter = null): Generator
    {
        return $this->dir($path)->scanFiles($filter);
    }

    /**
     * List all files as File objects
     *
     * @param string|Stringable|Dir $path
     * @return array<string, File>
     */
    public function listFiles($path, callable $filter = null): array
    {
        return $this->dir($path)->listFiles($filter);
    }

    /**
     * Scan all files as names
     *
     * @param string|Stringable|Dir $path
     * @return Generator<string>
     */
    public function scanFileNames($path, callable $filter = null): Generator
    {
        return $this->dir($path)->scanFileNames($filter);
    }

    /**
     * List all files as names
     *
     * @param string|Stringable|Dir $path
     * @return array<string>
     */
    public function listFileNames($path, callable $filter = null): array
    {
        return $this->dir($path)->listFileNames($filter);
    }

    /**
     * Scan all files as paths
     *
     * @param string|Stringable|Dir $path
     * @return Generator<string, string>
     */
    public function scanFilePaths($path, callable $filter = null): Generator
    {
        return $this->dir($path)->scanFilePaths($filter);
    }

    /**
     * List all files as paths
     *
     * @param string|Stringable|Dir $path
     * @return array<string, string>
     */
    public function listFilePaths($path, callable $filter = null): array
    {
        return $this->dir($path)->listFilePaths($filter);
    }

    /**
     * Count all files
     *
     * @param string|Stringable|Dir $path
     */
    public function countFiles($path, callable $filter = null): int
    {
        return $this->dir($path)->countFiles($filter);
    }


    /**
     * Scan all dirs as Dir objects
     *
     * @param string|Stringable|Dir $path
     * @return Generator<string, Dir>
     */
    public function scanDirs($path, callable $filter = null): Generator
    {
        return $this->dir($path)->scanDirs($filter);
    }

    /**
     * List all dirs as Dir objects
     *
     * @param string|Stringable|Dir $path
     * @return array<string, Dir>
     */
    public function listDirs($path, callable $filter = null): array
    {
        return $this->dir($path)->listDirs($filter);
    }

    /**
     * Scan all dirs as names
     *
     * @param string|Stringable|Dir $path
     * @return Generator<string>
     */
    public function scanDirNames($path, callable $filter = null): Generator
    {
        return $this->dir($path)->scanDirNames($filter);
    }

    /**
     * List all dirs as names
     *
     * @param string|Stringable|Dir $path
     * @return array<string>
     */
    public function listDirNames($path, callable $filter = null): array
    {
        return $this->dir($path)->listDirNames($filter);
    }

    /**
     * Scan all dirs as paths
     *
     * @param string|Stringable|Dir $path
     * @return Generator<string, string>
     */
    public function scanDirPaths($path, callable $filter = null): Generator
    {
        return $this->dir($path)->scanDirPaths($filter);
    }

    /**
     * List all dirs as paths
     *
     * @param string|Stringable|Dir $path
     * @return array<string, string>
     */
    public function listDirPaths($path, callable $filter = null): array
    {
        return $this->dir($path)->listDirPaths($filter);
    }

    /**
     * Count all dirs
     *
     * @param string|Stringable|Dir $path
     */
    public function countDirs($path, callable $filter = null): int
    {
        return $this->dir($path)->countDirs($filter);
    }


    /**
     * Scan all children recursively as File or Dir objects
     *
     * @param string|Stringable|Dir $path
     * @return Generator<string, Dir|File>
     */
    public function scanRecursive($path, callable $filter = null): Generator
    {
        return $this->dir($path)->scanRecursive($filter);
    }

    /**
     * List all children recursively as File or Dir objects
     *
     * @param string|Stringable|Dir $path
     * @return array<string, Dir|File>
     */
    public function listRecursive($path, callable $filter = null): array
    {
        return $this->dir($path)->listRecursive($filter);
    }

    /**
     * Scan all children recursively as names
     *
     * @param string|Stringable|Dir $path
     * @return Generator<string>
     */
    public function scanNamesRecursive($path, callable $filter = null): Generator
    {
        return $this->dir($path)->scanNamesRecursive($filter);
    }

    /**
     * List all children recursively as names
     *
     * @param string|Stringable|Dir $path
     * @return array<string>
     */
    public function listNamesRecursive($path, callable $filter = null): array
    {
        return $this->dir($path)->listNamesRecursive($filter);
    }

    /**
     * Scan all children recursively as paths
     *
     * @param string|Stringable|Dir $path
     * @return Generator<string, string>
     */
    public function scanPathsRecursive($path, callable $filter = null): Generator
    {
        return $this->dir($path)->scanPathsRecursive($filter);
    }

    /**
     * List all children recursively as paths
     *
     * @param string|Stringable|Dir $path
     * @return array<string, string>
     */
    public function listPathsRecursive($path, callable $filter = null): array
    {
        return $this->dir($path)->listPathsRecursive($filter);
    }

    /**
     * Count all children recursively
     *
     * @param string|Stringable|Dir $path
     */
    public function countContentsRecursive($path, callable $filter = null): int
    {
        return $this->dir($path)->countContentsRecursive($filter);
    }


    /**
     * Scan all files recursively as File objects
     *
     * @param string|Stringable|Dir $path
     * @return Generator<string, File>
     */
    public function scanFilesRecursive($path, callable $filter = null): Generator
    {
        return $this->dir($path)->scanFilesRecursive($filter);
    }

    /**
     * List all files recursively as File objects
     *
     * @param string|Stringable|Dir $path
     * @return array<string, File>
     */
    public function listFilesRecursive($path, callable $filter = null): array
    {
        return $this->dir($path)->listFilesRecursive($filter);
    }

    /**
     * Scan all files recursively as names
     *
     * @param string|Stringable|Dir $path
     * @return Generator<string>
     */
    public function scanFileNamesRecursive($path, callable $filter = null): Generator
    {
        return $this->dir($path)->scanFileNamesRecursive($filter);
    }

    /**
     * List all files recursively as names
     *
     * @param string|Stringable|Dir $path
     * @return array<string>
     */
    public function listFileNamesRecursive($path, callable $filter = null): array
    {
        return $this->dir($path)->listFileNamesRecursive($filter);
    }

    /**
     * Scan all files recursively as paths
     *
     * @param string|Stringable|Dir $path
     * @return Generator<string, string>
     */
    public function scanFilePathsRecursive($path, callable $filter = null): Generator
    {
        return $this->dir($path)->scanFilePathsRecursive($filter);
    }

    /**
     * List all files recursively as paths
     *
     * @param string|Stringable|Dir $path
     * @return array<string, string>
     */
    public function listFilePathsRecursive($path, callable $filter = null): array
    {
        return $this->dir($path)->listFilePathsRecursive($filter);
    }

    /**
     * Count all files recursively
     *
     * @param string|Stringable|Dir $path
     */
    public function countFilesRecursive($path, callable $filter = null): int
    {
        return $this->dir($path)->countFilesRecursive($filter);
    }


    /**
     * Scan all dirs recursively as Dir objects
     *
     * @param string|Stringable|Dir $path
     * @return Generator<string, Dir>
     */
    public function scanDirsRecursive($path, callable $filter = null): Generator
    {
        return $this->dir($path)->scanDirsRecursive($filter);
    }

    /**
     * List all dirs recursively as Dir objects
     *
     * @param string|Stringable|Dir $path
     * @return array<string, Dir>
     */
    public function listDirsRecursive($path, callable $filter = null): array
    {
        return $this->dir($path)->listDirsRecursive($filter);
    }

    /**
     * Scan all dirs recursively as names
     *
     * @param string|Stringable|Dir $path
     * @return Generator<string>
     */
    public function scanDirNamesRecursive($path, callable $filter = null): Generator
    {
        return $this->dir($path)->scanDirNamesRecursive($filter);
    }

    /**
     * List all dirs recursively as names
     *
     * @param string|Stringable|Dir $path
     * @return array<string>
     */
    public function listDirNamesRecursive($path, callable $filter = null): array
    {
        return $this->dir($path)->listDirNamesRecursive($filter);
    }

    /**
     * Scan all dirs recursively as paths
     *
     * @param string|Stringable|Dir $path
     * @return Generator<string, string>
     */
    public function scanDirPathsRecursive($path, callable $filter = null): Generator
    {
        return $this->dir($path)->scanDirPathsRecursive($filter);
    }

    /**
     * List all dirs recursively as paths
     *
     * @param string|Stringable|Dir $path
     * @return array<string, string>
     */
    public function listDirPathsRecursive($path, callable $filter = null): array
    {
        return $this->dir($path)->listDirPathsRecursive($filter);
    }

    /**
     * Count all dirs recursively
     *
     * @param string|Stringable|Dir $path
     */
    public function countDirsRecursive($path, callable $filter = null): int
    {
        return $this->dir($path)->countDirsRecursive($filter);
    }






    /**
     * Copy dir to $destinationPath
     *
     * @param string|Stringable|Dir $path
     */
    public function copyDir($path, string $destinationPath): Dir
    {
        $dir = $this->dir($path);
        $output = $dir->copy($destinationPath);

        if (!$output instanceof Dir) {
            throw Exceptional::UnexpectedValue(
                'Output of dir copy() was not a dir',
                null,
                $output
            );
        }

        return $output;
    }

    /**
     * Copy dir to $destinationDir, rename basename to $newName if set
     *
     * @param string|Stringable|Dir $path
     */
    public function copyDirTo($path, string $destinationDir, string $newName = null): Dir
    {
        $dir = $this->dir($path);
        $output = $dir->copyTo($destinationDir, $newName);

        if (!$output instanceof Dir) {
            throw Exceptional::UnexpectedValue(
                'Output of dir copy() was not a dir',
                null,
                $output
            );
        }

        return $output;
    }

    /**
     * Rename basename of dir
     *
     * @param string|Stringable|Dir $path
     */
    public function renameDir($path, string $newName): Dir
    {
        $dir = $this->dir($path);
        $dir->renameTo($newName);
        return $dir;
    }

    /**
     * Move dir to $destinationPath
     *
     * @param string|Stringable|Dir $path
     */
    public function moveDir($path, string $destinationPath): Dir
    {
        $dir = $this->dir($path);
        $dir->move($destinationPath);
        return $dir;
    }

    /**
     * Move dir to $destinationDir, rename basename to $newName if set
     *
     * @param string|Stringable|Dir $path
     */
    public function moveDirTo($path, string $destinationDir, string $newName = null): Dir
    {
        $dir = $this->dir($path);
        $dir->moveTo($destinationDir, $newName);
        return $dir;
    }

    /**
     * Delete dir and contents
     *
     * @param string|Stringable|Dir $path
     */
    public function deleteDir($path): void
    {
        $this->dir($path)->delete();
    }

    /**
     * Delete contents of dir
     *
     * @param string|Stringable|Dir $path
     */
    public function emptyOut($path): Dir
    {
        return $this->dir($path)->emptyOut();
    }

    /**
     * Merge contents of dir into $destination dir
     *
     * @param string|Stringable|Dir $path
     */
    public function merge($path, string $destination): Dir
    {
        return $this->dir($path)->mergeInto($destination);
    }



    /**
     * Normalize node input
     *
     * @param string|Stringable|Dir|File $path
     * @param class-string $type
     * @return Dir|File|null
     */
    protected function normalizeInput(&$path, string $type): ?Node
    {
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
                'Item is not a ' . $type
            );
        }

        // Extract path
        if (
            is_object($path) &&
            method_exists($path, '__toString')
        ) {
            $path = (string)$path;
        }

        if (!is_string($path)) {
            throw Exceptional::InvalidArgument(
                'Invalid filesystem node input',
                null,
                $path
            );
        }


        // Check types
        if (
            $type === Dir::class &&
            is_file($path)
        ) {
            throw Exceptional::Runtime(
                'Path is a File not a Dir',
                null,
                $path
            );
        } elseif (
            $type === File::class &&
            is_dir($path)
        ) {
            throw Exceptional::Runtime(
                'Path is a Dir not a File',
                null,
                $path
            );
        }

        return null;
    }
}
