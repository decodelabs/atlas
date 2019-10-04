<?php
/**
 * This file is part of the Atlas package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Atlas\Dir;

use DecodeLabs\Atlas\Node;
use DecodeLabs\Atlas\File;
use DecodeLabs\Atlas\Dir;

use Generator;
use Traversable;

trait ScannerTrait
{

    /**
     * Scan all children as File or Dir objects
     */
    public function scan(callable $filter=null): Generator
    {
        return $this->scanRaw(true, true, $filter, true);
    }

    /**
     * List all children as File or Dir objects
     */
    public function list(callable $filter=null): array
    {
        return iterator_to_array($this->scan($filter));
    }

    /**
     * Scan all children as names
     */
    public function scanNames(callable $filter=null): Generator
    {
        return $this->scanRaw(true, true, $filter, false);
    }

    /**
     * List all children as names
     */
    public function listNames(callable $filter=null): array
    {
        return iterator_to_array($this->scanNames($filter));
    }

    /**
     * Count all children
     */
    public function countContents(callable $filter=null): int
    {
        return $this->countGenerator($this->scanRaw(true, true, $filter, null));
    }


    /**
     * Scan all files as File objects
     */
    public function scanFiles(callable $filter=null): Generator
    {
        return $this->scanRaw(true, false, $filter, true);
    }

    /**
     * List all files as File objects
     */
    public function listFiles(callable $filter=null): array
    {
        return iterator_to_array($this->scanFiles($filter));
    }

    /**
     * Scan all files as names
     */
    public function scanFileNames(callable $filter=null): Generator
    {
        return $this->scanRaw(true, false, $filter, false);
    }

    /**
     * List all files as names
     */
    public function listFileNames(callable $filter=null): array
    {
        return iterator_to_array($this->scanFileNames($filter));
    }

    /**
     * Count all files
     */
    public function countFiles(callable $filter=null): int
    {
        return $this->countGenerator($this->scanRaw(true, false, $filter, null));
    }


    /**
     * Scan all dirs as Dir objects
     */
    public function scanDirs(callable $filter=null): Generator
    {
        return $this->scanRaw(false, true, $filter, true);
    }

    /**
     * List all dirs as Dir objects
     */
    public function listDirs(callable $filter=null): array
    {
        return iterator_to_array($this->scanDirs($filter));
    }

    /**
     * Scan all dirs as names
     */
    public function scanDirNames(callable $filter=null): Generator
    {
        return $this->scanRaw(false, true, $filter, false);
    }

    /**
     * List all dirs as names
     */
    public function listDirNames(callable $filter=null): array
    {
        return iterator_to_array($this->scanDirNames($filter));
    }

    /**
     * Count all dirs
     */
    public function countDirs(callable $filter=null): int
    {
        return $this->countGenerator($this->scanRaw(false, true, $filter, null));
    }


    /**
     * Raw scan generator
     */
    protected function scanRaw(bool $files, bool $dirs, callable $filter=null, ?bool $wrap=true): Generator
    {
        if (!$this->exists()) {
            return;
        }

        foreach ($this->getScannerIterator($files, $dirs) as $item) {
            if ($item->isDot()) {
                continue;
            } elseif ($item->isDir()) {
                if (!$dirs) {
                    continue;
                }

                $output = $item->getPathname();

                if ($wrap) {
                    $output = new self($output);
                }
            } elseif ($item->isFile() || $item->isLink()) {
                if (!$files) {
                    continue;
                }

                $output = $item->getPathname();

                if ($wrap) {
                    $output = $this->wrapFile($output);
                }
            } else {
                continue;
            }

            $key = $item->getFilename();

            if ($filter && !$filter($key, $output)) {
                continue;
            }

            if ($wrap === null) {
                yield $key;
            } else {
                yield $key => $output;
            }
        }
    }

    abstract protected function getScannerIterator(bool $files, bool $dirs): Traversable;




    /**
     * Scan all children recursively as File or Dir objects
     */
    public function scanRecursive(callable $filter=null): Generator
    {
        return $this->scanRawRecursive(true, true, $filter, true);
    }

    /**
     * List all children recursively as File or Dir objects
     */
    public function listRecursive(callable $filter=null): array
    {
        return iterator_to_array($this->scanRecursive($filter));
    }

    /**
     * Scan all children recursively as names
     */
    public function scanNamesRecursive(callable $filter=null): Generator
    {
        return $this->scanRawRecursive(true, true, $filter, false);
    }

    /**
     * List all children recursively as names
     */
    public function listNamesRecursive(callable $filter=null): array
    {
        return iterator_to_array($this->scanNamesRecursive($filter));
    }

    /**
     * Count all children recursively
     */
    public function countContentsRecursive(callable $filter=null): int
    {
        return $this->countGenerator($this->scanRawRecursive(true, true, $filter, null));
    }


    /**
     * Scan all files recursively as File objects
     */
    public function scanFilesRecursive(callable $filter=null): Generator
    {
        return $this->scanRawRecursive(true, false, $filter, true);
    }

    /**
     * List all files recursively as File objects
     */
    public function listFilesRecursive(callable $filter=null): array
    {
        return iterator_to_array($this->scanFilesRecursive($filter));
    }

    /**
     * Scan all files recursively as names
     */
    public function scanFileNamesRecursive(callable $filter=null): Generator
    {
        return $this->scanRawRecursive(true, false, $filter, false);
    }

    /**
     * List all files recursively as names
     */
    public function listFileNamesRecursive(callable $filter=null): array
    {
        return iterator_to_array($this->scanFileNamesRecursive($filter));
    }

    /**
     * Count all files recursively
     */
    public function countFilesRecursive(callable $filter=null): int
    {
        return $this->countGenerator($this->scanRawRecursive(true, false, $filter, null));
    }


    /**
     * Scan all dirs recursively as Dir objects
     */
    public function scanDirsRecursive(callable $filter=null): Generator
    {
        return $this->scanRawRecursive(false, true, $filter, true);
    }

    /**
     * List all dirs recursively as Dir objects
     */
    public function listDirsRecursive(callable $filter=null): array
    {
        return iterator_to_array($this->scanDirsRecursive($filter));
    }

    /**
     * Scan all dirs recursively as names
     */
    public function scanDirNamesRecursive(callable $filter=null): Generator
    {
        return $this->scanRawRecursive(false, true, $filter, false);
    }

    /**
     * List all dirs recursively as names
     */
    public function listDirNamesRecursive(callable $filter=null): array
    {
        return iterator_to_array($this->scanDirNamesRecursive($filter));
    }

    /**
     * Count all dirs recursively
     */
    public function countDirsRecursive(callable $filter=null): int
    {
        return $this->countGenerator($this->scanRawRecursive(false, true, $filter, null));
    }



    /**
     * Raw recursive scan generator
     */
    protected function scanRawRecursive(bool $files, bool $dirs, callable $filter=null, ?bool $wrap=true): Generator
    {
        if (!$this->exists()) {
            return;
        }

        foreach ($this->getRecursiveScannerIterator($files, $dirs) as $item) {
            if ($item->isDot()) {
                continue;
            } elseif ($item->isDir()) {
                if (!$dirs) {
                    continue;
                }

                $output = $item->getPathname();

                if ($wrap) {
                    $output = new self($output);
                }
            } elseif ($item->isFile() || $item->isLink()) {
                if (!$files) {
                    continue;
                }

                $output = $item->getPathname();

                if ($wrap) {
                    $output = new $this->wrapFile($output);
                }
            } else {
                continue;
            }

            $key = $item->getSubPathname();

            if ($filter && !$filter($key, $output)) {
                continue;
            }

            if ($wrap === null) {
                yield $key;
            } else {
                yield $key => $output;
            }
        }
    }

    abstract protected function getRecursiveScannerIterator(bool $files, bool $dirs): Traversable;




    /**
     * Get count of generator yields
     */
    protected function countGenerator(Generator $generator): int
    {
        $output = 0;

        foreach ($generator as $item) {
            $output++;
        }

        return $output;
    }

    abstract protected function wrapFile(string $path): File;
}
