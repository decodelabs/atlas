<?php

/**
 * @package Atlas
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Atlas\Dir;

use DecodeLabs\Atlas\Dir;
use DecodeLabs\Atlas\File;
use DecodeLabs\Atlas\File\Local as LocalFile;
use DecodeLabs\Atlas\Mode;
use DecodeLabs\Atlas\Node;
use DecodeLabs\Atlas\Node\LocalTrait;
use DecodeLabs\Exceptional;
use DecodeLabs\Glitch\Dumpable;
use DecodeLabs\Glitch\Proxy;
use DirectoryIterator;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;
use Traversable;

class Local implements
    Dir,
    Dumpable
{
    /**
     * @use LocalTrait<Dir>
     */
    use LocalTrait;
    use ScannerTrait;

    /**
     * Init with path
     */
    public function __construct(
        string $path
    ) {
        $this->path = rtrim($path, '/');
    }

    /**
     * Does this dir exist?
     */
    public function exists(): bool
    {
        return is_dir($this->path);
    }

    /**
     * Create dir if it doesn't exist
     */
    public function ensureExists(
        ?int $permissions = null
    ): Dir {
        if (!is_dir($this->path)) {
            if (file_exists($this->path)) {
                throw Exceptional::Io(
                    message: 'Dir destination exists as file',
                    data: $this
                );
            }

            if ($permissions === null) {
                $permissions = 0777;
            }

            if (!mkdir($this->path, $permissions, true)) {
                throw Exceptional::Io(
                    message: 'Unable to mkdir',
                    data: $this
                );
            }
        } else {
            if ($permissions !== null) {
                try {
                    chmod($this->path, $permissions);
                } catch (Throwable $e) {
                }
            }
        }

        return $this;
    }


    /**
     * Is this a file?
     */
    public function isFile(): bool
    {
        return false;
    }

    /**
     * Is this a dir?
     */
    public function isDir(): bool
    {
        return true;
    }



    /**
     * Does this dir contain anything?
     */
    public function isEmpty(): bool
    {
        if (!$this->exists()) {
            return true;
        }

        foreach (new DirectoryIterator($this->path) as $item) {
            if ($item->isDot()) {
                continue;
            }

            if (
                $item->isFile() ||
                $item->isLink() ||
                $item->isDir()
            ) {
                return false;
            }
        }

        return true;
    }


    /**
     * Set permission on dir and children if $recursive
     */
    public function setPermissionsRecursive(
        int $mode
    ): Dir {
        if (!$this->exists()) {
            throw Exceptional::NotFound(
                message: 'Cannot set permissions, dir does not exist',
                data: $this
            );
        }

        chmod($this->path, $mode);

        if ($this->isLink()) {
            return $this;
        }

        foreach ($this->scanRaw(true, true) as $item) {
            if ($item instanceof Dir) {
                $item->setPermissionsRecursive($mode);
            } elseif ($item instanceof Node) {
                $item->setPermissions($mode);
            }
        }

        return $this;
    }

    /**
     * Set owner on dir and children if $recursive
     */
    public function setOwnerRecursive(
        int $owner
    ): Dir {
        if (!$this->exists()) {
            throw Exceptional::NotFound(
                message: 'Cannot set owner, dir does not exist',
                data: $this
            );
        }

        chown($this->path, $owner);

        if ($this->isLink()) {
            return $this;
        }

        foreach ($this->scanRaw(true, true) as $item) {
            if ($item instanceof Dir) {
                $item->setOwnerRecursive($owner);
            } elseif ($item instanceof Node) {
                $item->setOwner($owner);
            }
        }

        return $this;
    }

    /**
     * Set group on dir and children if $recursive
     */
    public function setGroupRecursive(
        int $group
    ): Dir {
        if (!$this->exists()) {
            throw Exceptional::NotFound(
                message: 'Cannot set group, dir does not exist',
                data: $this
            );
        }

        chgrp($this->path, $group);

        if ($this->isLink()) {
            return $this;
        }

        foreach ($this->scanRaw(true, true) as $item) {
            if ($item instanceof Dir) {
                $item->setGroupRecursive($group);
            } elseif ($item instanceof Node) {
                $item->setGroup($group);
            }
        }

        return $this;
    }


    /**
     * Get iterator for flat Directory scanning
     *
     * @return Traversable<DirectoryIterator>
     */
    protected function getScannerIterator(
        bool $files,
        bool $dirs
    ): Traversable {
        return new DirectoryIterator($this->path);
    }


    /**
     * Get iterator for recursive Directory scanning
     *
     * @return Traversable<RecursiveIteratorIterator<RecursiveDirectoryIterator>>
     */
    protected function getRecursiveScannerIterator(
        bool $files,
        bool $dirs
    ): Traversable {
        return new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $this->path,
                FilesystemIterator::KEY_AS_PATHNAME |
                FilesystemIterator::CURRENT_AS_SELF |
                FilesystemIterator::SKIP_DOTS
            ),
            $dirs ?
                RecursiveIteratorIterator::SELF_FIRST :
                RecursiveIteratorIterator::LEAVES_ONLY
        );
    }



    /**
     * Get a child File or Dir if it exists
     */
    public function getChild(
        string $name
    ): Dir|File|null {
        $path = $this->path . '/' . ltrim($name, '/');

        if (is_dir($path)) {
            return new self($path);
        } elseif (is_file($path) || is_link($path)) {
            return new LocalFile($path);
        }

        return null;
    }

    /**
     * Is there an existing child by $name?
     */
    public function hasChild(
        string $name
    ): bool {
        $path = $this->path . '/' . ltrim($name, '/');
        return file_exists($path);
    }

    /**
     * Ensure a child item is deleted
     */
    public function deleteChild(
        string $name
    ): Dir {
        if ($child = $this->getChild($name)) {
            $child->delete();
        }

        return $this;
    }


    /**
     * Create a dir as a child
     */
    public function createDir(
        string $name,
        ?int $permissions = null
    ): Dir {
        return $this->getDir($name)->ensureExists($permissions);
    }

    /**
     * Does child dir exist?
     */
    public function hasDir(
        string $name
    ): bool {
        return $this->getDir($name)->exists();
    }

    /**
     * Get a child dir
     */
    public function getDir(
        string $name
    ): Dir {
        return new self($this->path . '/' . ltrim($name, '/'));
    }

    /**
     * Get a child dir if it exists
     */
    public function getExistingDir(
        string $name
    ): ?Dir {
        $output = new self($this->path . '/' . ltrim($name, '/'));

        if (!$output->exists()) {
            $output = null;
        }

        return $output;
    }

    /**
     * Delete child if its a dir
     */
    public function deleteDir(
        string $name
    ): Dir {
        if ($dir = $this->getExistingDir($name)) {
            $dir->delete();
        }

        return $this;
    }


    /**
     * Create a file with content
     */
    public function createFile(
        string $name,
        string $content
    ): File {
        return $this->getFile($name)->putContents($content);
    }

    /**
     * Open a child file
     */
    public function openFile(
        string $name,
        string|Mode $mode
    ): File {
        return $this->getFile($name)->open($mode);
    }

    /**
     * Does child file exist?
     */
    public function hasFile(
        string $name
    ): bool {
        return $this->getFile($name)->exists();
    }

    /**
     * Get a child file
     */
    public function getFile(
        string $name
    ): File {
        return $this->wrapFile($this->path . '/' . ltrim($name, '/'));
    }

    /**
     * Get a child file if it exists
     */
    public function getExistingFile(
        string $name
    ): ?File {
        $output = $this->wrapFile($this->path . '/' . ltrim($name, '/'));

        if (!$output->exists()) {
            $output = null;
        }

        return $output;
    }

    /**
     * Delete child if its a file
     */
    public function deleteFile(
        string $name
    ): Dir {
        if ($file = $this->getExistingFile($name)) {
            $file->delete();
        }

        return $this;
    }


    /**
     * Copy dir to $destinationPath
     */
    public function copy(
        string $path
    ): Dir {
        if (file_exists($path)) {
            throw Exceptional::AlreadyExists(
                message: 'Destination dir already exists',
                data: $this
            );
        }

        if ($this->isLink()) {
            return $this->copySymlink($path);
        } else {
            return $this->mergeInto($path);
        }
    }


    /**
     * Move dir to $destinationDir, rename basename to $newName if set
     */
    public function move(
        string $path
    ): Dir {
        if (!$this->exists()) {
            throw Exceptional::NotFound(
                message: 'Source dir does not exist',
                data: $this
            );
        }

        (new Local(dirname($path)))->ensureExists();

        if (file_exists($path)) {
            throw Exceptional::AlreadyExists(
                message: 'Destination file already exists',
                data: $path
            );
        }

        if (!rename($this->path, $path)) {
            throw Exceptional::Io(
                message: 'Unable to rename dir',
                data: $this
            );
        }

        $this->path = $path;
        return $this;
    }


    /**
     * Recursively delete dir and its children
     */
    public function delete(): void
    {
        if (!$this->exists()) {
            return;
        }

        if ($this->isLink()) {
            unlink($this->path);
            return;
        }

        foreach ($this->scanRaw(true, true) as $item) {
            if ($item instanceof Node) {
                $item->delete();
            }
        }

        /** @phpstan-ignore-next-line */
        if ($this->exists()) {
            rmdir($this->path);
        }
    }

    /**
     * Recursively delete all children
     */
    public function emptyOut(): Dir
    {
        if (!$this->exists()) {
            return $this;
        }

        foreach ($this->scanRaw(true, true) as $item) {
            if ($item instanceof Node) {
                $item->delete();
            }
        }

        return $this;
    }

    /**
     * Merge this dir and its contents into another dir
     */
    public function mergeInto(
        string $destination
    ): Dir {
        if (!$this->exists()) {
            throw Exceptional::NotFound(
                message: 'Source dir does not exist',
                data: $this
            );
        }

        $destination = new self($destination);
        $destination->ensureExists($this->getPermissions());

        /** @var string $subPath */
        foreach ($this->scanRecursive() as $subPath => $item) {
            if ($item instanceof self) {
                // Dir
                if ($item->isLink()) {
                    $item->copySymlink($destination->getPath() . '/' . $subPath);
                } else {
                    $destination->createDir($subPath, $item->getPermissions());
                }
            } else {
                // File
                $item->copy($destination->getPath() . '/' . $subPath)
                    ->setPermissions((int)$item->getPermissions());
            }
        }

        return $this;
    }


    /**
     * Wrap a file path into File object
     */
    protected function wrapFile(
        string $path
    ): File {
        return new LocalFile($path);
    }


    /**
     * Export for dump inspection
     */
    public function glitchDump(): iterable
    {
        yield 'definition' => Proxy::normalizePath($this->path);

        yield 'metaList' => [
            'exists' => $this->exists(),
            'permissions' => $this->getPermissionsOct() . ' : ' . $this->getPermissionsString()
        ];
    }
}
