<?php
/**
 * This file is part of the Atlas package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Atlas\Plugins;

use DecodeLabs\Veneer\FacadePlugin;

use DecodeLabs\Atlas\Node;
use DecodeLabs\Atlas\File;
use DecodeLabs\Atlas\File\Local as LocalFile;
use DecodeLabs\Atlas\Dir;
use DecodeLabs\Atlas\Dir\Local as LocalDir;

use Generator;

class Fs implements FacadePlugin
{
    /**
     * Get node, return file or dir depending on what's on disk
     */
    public function get(string $path): Node
    {
        if (is_dir($path)) {
            return $this->dir($path);
        } else {
            return $this->file($path);
        }
    }

    /**
     * Check if modified time is within $seconds
     */
    public function hasChanged(string $path, int $seconds=30): bool
    {
        return $this->get($path)->hasChanged($seconds);
    }

    /**
     * Set file permissions on file or dir
     */
    public function setPermissions(string $path, int $permissions): Node
    {
        return $this->get($path)->setPermissions($permissions);
    }

    /**
     * Set file permissions on file or dir recursively
     */
    public function setPermissionsRecursive(string $path, int $permissions): Node
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
     */
    public function setOwner(string $path, int $owner): Node
    {
        return $this->get($path)->setOwner($owner);
    }

    /**
     * Set group for file or dir
     */
    public function setGroup(string $path, int $group): Node
    {
        return $this->get($path)->setGroup($group);
    }

    /**
     * Copy file or dir to $destinationPath
     */
    public function copy(string $path, string $destinationPath): Node
    {
        return $this->get($path)->copy($destinationPath);
    }

    /**
     * Copy file or dir to $destinationDir, rename basename to $newName if set
     */
    public function copyTo(string $path, string $destinationDir, string $newName=null): Node
    {
        return $this->get($path)->copyTo($destinationDir, $newName);
    }

    /**
     * Rename basename of file or dir
     */
    public function rename(string $path, string $newName): Node
    {
        return $this->get($path)->renameTo($newName);
    }

    /**
     * Move file or dir to $destinationPath
     */
    public function move(string $path, string $destinationPath): Node
    {
        return $this->get($path)->move($destinationPath);
    }

    /**
     * Move file or dir to $destinationDir, rename basename to $newName if set
     */
    public function moveTo(string $path, string $destinationDir, string $newName=null): Node
    {
        return $this->get($path)->moveTo($destinationDir, $newName);
    }

    /**
     * Delete file or dir
     */
    public function delete(string $path): void
    {
        $this->get($path)->delete();
    }



    /**
     * Load file from $path, open if $mode is set
     */
    public function file(string $path, string $mode=null): File
    {
        return new LocalFile($path, $mode);
    }

    /**
     * Create a new file with $data
     */
    public function createFile(string $path, $data): File
    {
        return $this->file($path)->putContents($data);
    }

    /**
     * Get contents of file at $path
     */
    public function getContents(string $path)
    {
        return $this->file($path)->getContents();
    }

    /**
     * Check file last modified within $seconds
     */
    public function hasFileChanged(string $path, int $seconds=30): bool
    {
        return $this->file($path)->hasChanged($seconds);
    }

    /**
     * Set permissions of file
     */
    public function setFilePermissions(string $path, int $permissions): File
    {
        $file = $this->file($path);
        $file->setPermissions($permissions);
        return $file;
    }

    /**
     * Set owner of file
     */
    public function setFileOwner(string $path, int $owner): File
    {
        $file = $this->file($path);
        $file->setOwner($owner);
        return $file;
    }

    /**
     * Set group of file
     */
    public function setFileGroup(string $path, int $group): File
    {
        $file = $this->file($path);
        $file->setGroup($group);
        return $file;
    }

    /**
     * Copy file to $destinationPath
     */
    public function copyFile(string $path, string $destinationPath): File
    {
        $file = $this->file($path);
        $file->copy($destinationPath);
        return $file;
    }

    /**
     * Copy file to $destinationDir, rename basename to $newName if set
     */
    public function copyFileTo(string $path, string $destinationDir, string $newName=null): File
    {
        $file = $this->file($path);
        $file->copyTo($destinationDir, $newName);
        return $file;
    }

    /**
     * Rename basename of file
     */
    public function renameFile(string $path, string $newName): File
    {
        $file = $this->file($path);
        $file->renameTo($newName);
        return $file;
    }

    /**
     * Move file to $destinationPath
     */
    public function moveFile(string $path, string $destinationPath): File
    {
        $file = $this->file($path);
        $file->move($destinationPath);
        return $file;
    }

    /**
     * Move file to $destinationDir, rename basename to $newName if set
     */
    public function moveFileTo(string $path, string $destinationDir, string $newName=null): File
    {
        $file = $this->file($path);
        $file->moveTo($destinationDir, $newName);
        return $file;
    }

    /**
     * Delete file
     */
    public function deleteFile(string $path): void
    {
        $this->file($path)->delete();
    }



    /**
     * Load dir from path
     */
    public function dir(string $path): Dir
    {
        return new LocalDir($path);
    }

    /**
     * Ensure directory at $path exists with $permissions
     */
    public function createDir(string $path, int $permissions=null): Dir
    {
        return $this->dir($path)->ensureExists($permissions);
    }

    /**
     * Check last modified of dir within $seconds
     */
    public function hasDirChanged(string $path, int $seconds=30): bool
    {
        return $this->dir($path)->hasChanged($seconds);
    }

    /**
     * Set permissions on dir
     */
    public function setDirPermissions(string $path, int $permissions): Dir
    {
        $dir = $this->dir($path);
        $dir->setPermissions($permissions);
        return $dir;
    }

    /**
     * Set permissions on dir recursively
     */
    public function setDirPermissionsRecursive(string $path, int $permissions): Dir
    {
        $dir = $this->dir($path);
        $dir->setPermissionsRecursive($permissions);
        return $dir;
    }

    /**
     * Set owner of dir
     */
    public function setDirOwner(string $path, int $owner): Dir
    {
        $dir = $this->dir($path);
        $dir->setOwner($owner);
        return $dir;
    }

    /**
     * Set owner of dir recursively
     */
    public function setDirOwnerRecursive(string $path, int $owner): Dir
    {
        $dir = $this->dir($path);
        $dir->setOwnerRecursive($owner);
        return $dir;
    }

    /**
     * Set group of dir
     */
    public function setDirGroup(string $path, int $group): Dir
    {
        $dir = $this->dir($path);
        $dir->setGroup($group);
        return $dir;
    }

    /**
     * Set group of dir recursively
     */
    public function setDirGroupRecursive(string $path, int $group): Dir
    {
        $dir = $this->dir($path);
        $dir->setGroupRecursive($group);
        return $dir;
    }




    /**
     * Scan all children as File or Dir objects
     */
    public function scan(string $path, callable $filter=null): Generator
    {
        return $this->dir($path)->scan($filter);
    }

    /**
     * List all children as File or Dir objects
     */
    public function list(string $path, callable $filter=null): array
    {
        return $this->dir($path)->list($filter);
    }

    /**
     * Scan all children as names
     */
    public function scanNames(string $path, callable $filter=null): Generator
    {
        return $this->dir($path)->scanNames($filter);
    }

    /**
     * List all children as names
     */
    public function listNames(string $path, callable $filter=null): array
    {
        return $this->dir($path)->listNames($filter);
    }

    /**
     * Count all children
     */
    public function countContents(string $path, callable $filter=null): int
    {
        return $this->dir($path)->countContents($filter);
    }


    /**
     * Scan all files as File objects
     */
    public function scanFiles(string $path, callable $filter=null): Generator
    {
        return $this->dir($path)->scanFiles($filter);
    }

    /**
     * List all files as File objects
     */
    public function listFiles(string $path, callable $filter=null): array
    {
        return $this->dir($path)->listFiles($filter);
    }

    /**
     * Scan all files as names
     */
    public function scanFileNames(string $path, callable $filter=null): Generator
    {
        return $this->dir($path)->scanFileNames($filter);
    }

    /**
     * List all files as names
     */
    public function listFileNames(string $path, callable $filter=null): array
    {
        return $this->dir($path)->listFileNames($filter);
    }

    /**
     * Count all files
     */
    public function countFiles(string $path, callable $filter=null): int
    {
        return $this->dir($path)->countFiles($filter);
    }


    /**
     * Scan all dirs as Dir objects
     */
    public function scanDirs(string $path, callable $filter=null): Generator
    {
        return $this->dir($path)->scanDirs($filter);
    }

    /**
     * List all dirs as Dir objects
     */
    public function listDirs(string $path, callable $filter=null): array
    {
        return $this->dir($path)->listDirs($filter);
    }

    /**
     * Scan all dirs as names
     */
    public function scanDirNames(string $path, callable $filter=null): Generator
    {
        return $this->dir($path)->scanDirNames($filter);
    }

    /**
     * List all dirs as names
     */
    public function listDirNames(string $path, callable $filter=null): array
    {
        return $this->dir($path)->listDirNames($filter);
    }

    /**
     * Count all dirs
     */
    public function countDirs(string $path, callable $filter=null): int
    {
        return $this->dir($path)->countDirs($filter);
    }


    /**
     * Scan all children recursively as File or Dir objects
     */
    public function scanRecursive(string $path, callable $filter=null): Generator
    {
        return $this->dir($path)->scanRecursive($filter);
    }

    /**
     * List all children recursively as File or Dir objects
     */
    public function listRecursive(string $path, callable $filter=null): array
    {
        return $this->dir($path)->listRecursive($filter);
    }

    /**
     * Scan all children recursively as names
     */
    public function scanNamesRecursive(string $path, callable $filter=null): Generator
    {
        return $this->dir($path)->scanNamesRecursive($filter);
    }

    /**
     * List all children recursively as names
     */
    public function listNamesRecursive(string $path, callable $filter=null): array
    {
        return $this->dir($path)->listNamesRecursive($filter);
    }

    /**
     * Count all children recursively
     */
    public function countContentsRecursive(string $path, callable $filter=null): int
    {
        return $this->dir($path)->countContentsRecursive($filter);
    }


    /**
     * Scan all files recursively as File objects
     */
    public function scanFilesRecursive(string $path, callable $filter=null): Generator
    {
        return $this->dir($path)->scanFilesRecursive($filter);
    }

    /**
     * List all files recursively as File objects
     */
    public function listFilesRecursive(string $path, callable $filter=null): array
    {
        return $this->dir($path)->listFilesRecursive($filter);
    }

    /**
     * Scan all files recursively as names
     */
    public function scanFileNamesRecursive(string $path, callable $filter=null): Generator
    {
        return $this->dir($path)->scanFileNamesRecursive($filter);
    }

    /**
     * List all files recursively as names
     */
    public function listFileNamesRecursive(string $path, callable $filter=null): array
    {
        return $this->dir($path)->listFileNamesRecursive($filter);
    }

    /**
     * Count all files recursively
     */
    public function countFilesRecursive(string $path, callable $filter=null): int
    {
        return $this->dir($path)->countFilesRecursive($filter);
    }


    /**
     * Scan all dirs recursively as Dir objects
     */
    public function scanDirsRecursive(string $path, callable $filter=null): Generator
    {
        return $this->dir($path)->scanDirsRecursive($filter);
    }

    /**
     * List all dirs recursively as Dir objects
     */
    public function listDirsRecursive(string $path, callable $filter=null): array
    {
        return $this->dir($path)->listDirsRecursive($filter);
    }

    /**
     * Scan all dirs recursively as names
     */
    public function scanDirNamesRecursive(string $path, callable $filter=null): Generator
    {
        return $this->dir($path)->scanDirNamesRecursive($filter);
    }

    /**
     * List all dirs recursively as names
     */
    public function listDirNamesRecursive(string $path, callable $filter=null): array
    {
        return $this->dir($path)->listDirNamesRecursive($filter);
    }

    /**
     * Count all dirs recursively
     */
    public function countDirsRecursive(string $path, callable $filter=null): int
    {
        return $this->dir($path)->countDirsRecursive($filter);
    }






    /**
     * Copy dir to $destinationPath
     */
    public function copyDir(string $path, string $destinationPath): Dir
    {
        $dir = $this->dir($path);
        $dir->copy($destinationPath);
        return $dir;
    }

    /**
     * Copy dir to $destinationDir, rename basename to $newName if set
     */
    public function copyDirTo(string $path, string $destinationDir, string $newName=null): Dir
    {
        $dir = $this->dir($path);
        $dir->copyTo($destinationDir, $newName);
        return $dir;
    }

    /**
     * Rename basename of dir
     */
    public function renameDir(string $path, string $newName): Dir
    {
        $dir = $this->dir($path);
        $dir->renameTo($newName);
        return $dir;
    }

    /**
     * Move dir to $destinationPath
     */
    public function moveDir(string $path, string $destinationPath): Dir
    {
        $dir = $this->dir($path);
        $dir->move($destinationPath);
        return $dir;
    }

    /**
     * Move dir to $destinationDir, rename basename to $newName if set
     */
    public function moveDirTo(string $path, string $destinationDir, string $newName=null): Dir
    {
        $dir = $this->dir($path);
        $dir->moveTo($destinationDir, $newName);
        return $dir;
    }

    /**
     * Delete dir and contents
     */
    public function deleteDir(string $path): void
    {
        $this->dir($path)->delete();
    }

    /**
     * Delete contents of dir
     */
    public function emptyOut(string $path): Dir
    {
        return $this->dir($path)->emptyOut();
    }

    /**
     * Merge contents of dir into $destination dir
     */
    public function merge(string $path, string $destination): Dir
    {
        return $this->dir($path)->mergeInto($destination);
    }
}
