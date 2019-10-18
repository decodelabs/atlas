<?php
/**
 * This file is part of the Atlas package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Atlas\Node;

use DecodeLabs\Atlas\Node;

use DecodeLabs\Atlas\Dir;
use DecodeLabs\Atlas\Dir\Local as LocalDir;

use DecodeLabs\Glitch;

trait LocalTrait
{
    protected $path;

    /**
     * Get basename of item
     */
    public function getName(): string
    {
        return basename($this->getPath());
    }

    /**
     * Get fs path to node
     */
    public function getPath(): string
    {
        return $this->path;
    }


    /**
     * Normalize dots in a path
     */
    protected function normalizePath(string $path): string
    {
        $root = ($path[0] === '/') ? '/' : '';
        $parts = explode('/', trim($path, '/'));
        $output = [];

        foreach ($parts as $part) {
            if (($part == '.') || strlen($part) === 0) {
                continue;
            }

            if ($part == '..') {
                array_pop($output);
            } else {
                $output[] = $part;
            }
        }

        return $root.implode('/', $output);
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
            throw Glitch::ENotFound('Source node does not exist', null, $this);
        }

        if (file_exists($path)) {
            throw Glitch::EAlreadyExists('Destination file already exists', null, $path);
        }

        (new LocalDir(dirname($path)))->ensureExists();

        if (!symlink($this->path, $path)) {
            throw Glitch::EIo('Unable to copy symlink: '.$path);
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

        if (false === ($output = filemtime($this->path))) {
            $output = null;
        }

        return $output;
    }

    /**
     * Compare last modified
     */
    public function hasChanged(int $seconds=30): bool
    {
        if (!$this->exists()) {
            return false;
        }

        return time() - $this->getLastModified() < $seconds;
    }

    /**
     * Compare with interval string
     */
    public function hasChangedIn(string $timeout): bool
    {
        if (preg_match('/^[0-9]+$/', $timeout)) {
            return $this->hasChanged((int)$timeout);
        }

        $date = new \DateTime('now');
        $interval = \DateInterval::createFromDateString($timeout);
        $ts = $date->sub($interval)->getTimestamp();

        if (!$mod = $this->getLastModified()) {
            return false;
        }

        return $mod > $ts;
    }



    /**
     * Set permissions on file
     */
    public function setPermissions(int $mode): Node
    {
        if (!$this->exists()) {
            throw Glitch::ENotFound('Cannot set permissions, file does not exist', null, $this);
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

        if (false === ($output = fileperms($this->getPath()))) {
            $output = null;
        }

        return $output;
    }

    /**
     * Get permissions of node as octal string
     */
    public function getPermissionsOct(): ?string
    {
        if (null === ($perms = $this->getPermissions())) {
            return null;
        }

        return decoct($perms & 0777);
    }

    /**
     * Get permissions of node as resource string
     * Taken from PHP manual
     */
    public function getPermissionsString(): ?string
    {
        if (null === ($perms = $this->getPermissions())) {
            return null;
        }

        switch ($perms & 0xF000) {
            case 0xC000: // socket
                $info = 's';
                break;

            case 0xA000: // symbolic link
                $info = 'l';
                break;

            case 0x8000: // regular
                $info = 'r';
                break;

            case 0x6000: // block special
                $info = 'b';
                break;

            case 0x4000: // directory
                $info = 'd';
                break;

            case 0x2000: // character special
                $info = 'c';
                break;

            case 0x1000: // FIFO pipe
                $info = 'p';
                break;

            default: // unknown
                $info = 'u';
                break;
        }

        // Owner
        $info .= (($perms & 0x0100) ? 'r' : '-');
        $info .= (($perms & 0x0080) ? 'w' : '-');
        $info .= (($perms & 0x0040) ?
                    (($perms & 0x0800) ? 's' : 'x') :
                    (($perms & 0x0800) ? 'S' : '-'));

        // Group
        $info .= (($perms & 0x0020) ? 'r' : '-');
        $info .= (($perms & 0x0010) ? 'w' : '-');
        $info .= (($perms & 0x0008) ?
                    (($perms & 0x0400) ? 's' : 'x') :
                    (($perms & 0x0400) ? 'S' : '-'));

        // World
        $info .= (($perms & 0x0004) ? 'r' : '-');
        $info .= (($perms & 0x0002) ? 'w' : '-');
        $info .= (($perms & 0x0001) ?
                    (($perms & 0x0200) ? 't' : 'x') :
                    (($perms & 0x0200) ? 'T' : '-'));

        return $info;
    }


    /**
     * Set owner of file
     */
    public function setOwner(int $owner): Node
    {
        if (!$this->exists()) {
            throw Glitch::ENotFound('Cannot set owner, file does not exist', null, $this);
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

        if (false === ($output = fileowner($this->getPath()))) {
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
            throw Glitch::ENotFound('Cannot set owner, file does not exist', null, $this);
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

        if (false === ($output = filegroup($this->getPath()))) {
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
            throw Glitch::EIo('Unable to follow symlink target: '.$this->getPath());
        }

        if (!symlink($target->getPath(), $path)) {
            throw Glitch::EIo('Unable to copy symlink: '.$path);
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
            throw Glitch::EInvalidArgument('New name is invalid: '.$newName, null, $this);
        }

        return $newName;
    }

    /**
     * Get path as string
     */
    public function __toString(): string
    {
        return $this->getPath();
    }
}
