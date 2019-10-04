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

        return filemtime($this->path);
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
     * Get permissions of node
     */
    public function getPermissions(): ?int
    {
        if (!$this->exists()) {
            return null;
        }

        return fileperms($this->getPath());
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
     * Get owner of node
     */
    public function getOwner(): ?int
    {
        if (!$this->exists()) {
            return null;
        }

        return fileowner($this->getPath());
    }

    /**
     * Get group of node
     */
    public function getGroup(): ?int
    {
        if (!$this->exists()) {
            return null;
        }

        return filegroup($this->getPath());
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
            throw Glitch::EInvalidArgument('New name is invalid: '.$name, null, $this);
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
