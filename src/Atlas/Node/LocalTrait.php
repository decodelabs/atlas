<?php
/**
 * This file is part of the Atlas package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Atlas\Node;

use DecodeLabs\Atlas\Node;
use DecodeLabs\Atlas\NodeTrait;

use DecodeLabs\Atlas\Dir;
use DecodeLabs\Atlas\Dir\Local as LocalDir;

use DecodeLabs\Exceptional;

trait LocalTrait
{
    use NodeTrait;

    protected $path;


    /**
     * Get fs path to node
     */
    public function getPath(): string
    {
        return $this->path;
    }


    /**
     * Is this a symbolic link?
     */
    public function isLink(): bool
    {
        return is_link($this->path);
    }

    /**
     * Get item pointed to by link
     */
    public function getLinkTarget(): ?Node
    {
        if (!$this->isLink()) {
            return null;
        }

        $path = readlink($this->path);

        if (substr($path, 0, 1) == '.') {
            $path = dirname($this->path).'/'.$path;
        }

        return new self($this->normalizePath($path));
    }

    /**
     * Create a symlink to this node
     */
    public function createLink(string $path): Node
    {
        if (!$this->exists()) {
            throw Exceptional::NotFound(
                'Source node does not exist', null, $this
            );
        }

        if (file_exists($path)) {
            throw Exceptional::AlreadyExists(
                'Destination file already exists', null, $path
            );
        }

        (new LocalDir(dirname($path)))->ensureExists();

        if (!symlink($this->path, $path)) {
            throw Exceptional::Io(
                'Unable to copy symlink: '.$path
            );
        }

        return new self($path);
    }

    /**
     * Clear stat cache for file / dir
     */
    public function clearStatCache(): Node
    {
        clearstatcache(true, $this->getPath());
        return $this;
    }

    /**
     * Get mtime of file
     */
    public function getLastModified(): ?int
    {
        if (!$this->exists()) {
            return null;
        }

        try {
            if (false === ($output = filemtime($this->path))) {
                $output = null;
            }
        } catch (\ErrorException $e) {
            $output = null;
        }

        return $output;
    }


    /**
     * Set permissions on file
     */
    public function setPermissions(int $mode): Node
    {
        if (!$this->exists()) {
            throw Exceptional::NotFound(
                'Cannot set permissions, file does not exist', null, $this
            );
        }

        chmod($this->path, $mode);
        return $this;
    }

    /**
     * Get permissions of node
     */
    public function getPermissions(): ?int
    {
        if (!$this->exists()) {
            return null;
        }

        try {
            if (false === ($output = fileperms($this->getPath()))) {
                $output = null;
            }
        } catch (\ErrorException $e) {
            $output = null;
        }

        return $output;
    }


    /**
     * Set owner of file
     */
    public function setOwner(int $owner): Node
    {
        if (!$this->exists()) {
            throw Exceptional::NotFound(
                'Cannot set owner, file does not exist', null, $this
            );
        }

        chown($this->path, $owner);
        return $this;
    }

    /**
     * Get owner of node
     */
    public function getOwner(): ?int
    {
        if (!$this->exists()) {
            return null;
        }

        try {
            if (false === ($output = fileowner($this->getPath()))) {
                $output = null;
            }
        } catch (\ErrorException $e) {
            $output = null;
        }

        return $output;
    }

    /**
     * Set group of file
     */
    public function setGroup(int $group): Node
    {
        if (!$this->exists()) {
            throw Exceptional::NotFound(
                'Cannot set owner, file does not exist', null, $this
            );
        }

        chgrp($this->path, $group);
        return $this;
    }

    /**
     * Get group of node
     */
    public function getGroup(): ?int
    {
        if (!$this->exists()) {
            return null;
        }

        try {
            if (false === ($output = filegroup($this->getPath()))) {
                $output = null;
            }
        } catch (\ErrorException $e) {
            $output = null;
        }

        return $output;
    }


    /**
     * Get parent Dir object
     */
    public function getParent(): ?Dir
    {
        if (($path = dirname($this->path)) == $this->path) {
            return null;
        }

        return new LocalDir($path);
    }


    /**
     * Copy symlink
     */
    protected function copySymlink(string $path): Node
    {
        (new LocalDir(dirname($path)))->ensureExists();

        if (!$target = $this->getLinkTarget()) {
            throw Exceptional::Io(
                'Unable to follow symlink target: '.$this->getPath()
            );
        }

        if (!symlink($target->getPath(), $path)) {
            throw Exceptional::Io(
                'Unable to copy symlink: '.$path
            );
        }

        return new self($path);
    }


    /**
     * Copy file to $destinationDir, rename basename to $newName if set
     */
    public function copyTo(string $destinationDir, string $newName=null): Node
    {
        $newName = $this->normalizeNewName($newName);
        $destination = rtrim($destinationDir, '/').'/'.$newName;
        return $this->copy($destination);
    }

    /**
     * Rename file within current dir
     */
    public function renameTo(string $newName): Node
    {
        return $this->moveTo(dirname($this->path), $newName);
    }

    /**
     * Move file to $destinationDir, rename basename to $newName if set
     */
    public function moveTo(string $destinationDir, string $newName=null): Node
    {
        $newName = $this->normalizeNewName($newName);
        $destination = rtrim($destinationDir, '/').'/'.$newName;
        return $this->move($destination);
    }

    /**
     * Normalize new name for copy / move functions
     */
    protected function normalizeNewName(?string $newName): string
    {
        if ($newName === null) {
            $newName = basename($this->path);
        }

        if ($newName == '' || $newName === '..' || $newName === '.' || strstr($newName, '/')) {
            throw Exceptional::InvalidArgument(
                'New name is invalid: '.$newName, null, $this
            );
        }

        return $newName;
    }
}
