<?php

/**
 * @package Atlas
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Atlas;

use DateInterval;
use DateTime;
use DecodeLabs\Coercion;
use Stringable;

/**
 * @phpstan-require-implements Node
 */
trait NodeTrait
{
    /**
     * Get path as string
     */
    public function __toString(): string
    {
        return $this->getPath();
    }


    /**
     * Get basename of item
     */
    public function getName(): string
    {
        return basename($this->getPath());
    }

    /**
     * Normalize dots in a path
     */
    protected function normalizePath(
        string $path
    ): string {
        $root = $path[0] === '/' ? '/' : '';
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

        return $root . implode('/', $output);
    }

    /**
     * Compare last modified
     */
    public function hasChanged(
        int $seconds = 30
    ): bool {
        if (!$this->exists()) {
            return false;
        }

        return time() - $this->getLastModified() < $seconds;
    }

    /**
     * Compare with interval string
     */
    public function hasChangedIn(
        DateInterval|string|Stringable|int $timeout
    ): bool {
        if (is_int($timeout)) {
            return $this->hasChanged((int)$timeout);
        }

        $date = new DateTime('now');
        $interval = Coercion::asDateInterval($timeout);
        $ts = $date->sub($interval)->getTimestamp();

        if (!$mod = $this->getLastModified()) {
            return false;
        }

        return $mod > $ts;
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
        $info .= ($perms & 0x0100 ? 'r' : '-');
        $info .= ($perms & 0x0080 ? 'w' : '-');
        $info .= ($perms & 0x0040 ?
                    ($perms & 0x0800 ? 's' : 'x') :
                    ($perms & 0x0800 ? 'S' : '-'));

        // Group
        $info .= ($perms & 0x0020 ? 'r' : '-');
        $info .= ($perms & 0x0010 ? 'w' : '-');
        $info .= ($perms & 0x0008 ?
                    ($perms & 0x0400 ? 's' : 'x') :
                    ($perms & 0x0400 ? 'S' : '-'));

        // World
        $info .= ($perms & 0x0004 ? 'r' : '-');
        $info .= ($perms & 0x0002 ? 'w' : '-');
        $info .= ($perms & 0x0001 ?
                    ($perms & 0x0200 ? 't' : 'x') :
                    ($perms & 0x0200 ? 'T' : '-'));

        return $info;
    }
}
