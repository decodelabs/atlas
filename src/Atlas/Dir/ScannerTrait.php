<?php

/**
 * @package Atlas
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Atlas\Dir;

use DecodeLabs\Atlas\Dir;
use DecodeLabs\Atlas\File;

use Generator;
use Traversable;

trait ScannerTrait
{
    public function scan(
        ?callable $filter = null
    ): Generator {
        /**
         * @var Generator<string, Dir|File>
         */
        return $this->scanRaw(true, true, $filter, true);
    }

    public function list(
        ?callable $filter = null
    ): array {
        return iterator_to_array($this->scan($filter));
    }

    public function scanNames(
        ?callable $filter = null
    ): Generator {
        /** @var Generator<string> */
        return $this->scanRaw(true, true, $filter, null);
    }

    public function listNames(
        ?callable $filter = null
    ): array {
        return iterator_to_array($this->scanNames($filter));
    }

    public function scanPaths(
        ?callable $filter = null
    ): Generator {
        /** @var Generator<string, string> */
        return $this->scanRaw(true, true, $filter, false);
    }

    public function listPaths(
        ?callable $filter = null
    ): array {
        return iterator_to_array($this->scanPaths($filter));
    }

    public function countContents(
        ?callable $filter = null
    ): int {
        return $this->countGenerator($this->scanRaw(true, true, $filter, null));
    }


    public function scanFiles(
        ?callable $filter = null
    ): Generator {
        /** @var Generator<string, File> */
        return $this->scanRaw(true, false, $filter, true);
    }

    public function listFiles(
        ?callable $filter = null
    ): array {
        return iterator_to_array($this->scanFiles($filter));
    }

    public function scanFileNames(
        ?callable $filter = null
    ): Generator {
        /** @var Generator<string> */
        return $this->scanRaw(true, false, $filter, null);
    }

    public function listFileNames(
        ?callable $filter = null
    ): array {
        return iterator_to_array($this->scanFileNames($filter));
    }

    public function scanFilePaths(
        ?callable $filter = null
    ): Generator {
        /** @var Generator<string, string> */
        return $this->scanRaw(true, false, $filter, false);
    }

    public function listFilePaths(
        ?callable $filter = null
    ): array {
        return iterator_to_array($this->scanFilePaths($filter));
    }



    public function countFiles(
        ?callable $filter = null
    ): int {
        return $this->countGenerator($this->scanRaw(true, false, $filter, null));
    }


    public function scanDirs(
        ?callable $filter = null
    ): Generator {
        /** @var Generator<string, Dir> */
        return $this->scanRaw(false, true, $filter, true);
    }

    public function listDirs(
        ?callable $filter = null
    ): array {
        return iterator_to_array($this->scanDirs($filter));
    }

    public function scanDirNames(
        ?callable $filter = null
    ): Generator {
        /** @var Generator<string> */
        return $this->scanRaw(false, true, $filter, null);
    }

    public function listDirNames(
        ?callable $filter = null
    ): array {
        return iterator_to_array($this->scanDirNames($filter));
    }

    public function scanDirPaths(
        ?callable $filter = null
    ): Generator {
        /** @var Generator<string, string> */
        return $this->scanRaw(false, true, $filter, false);
    }

    public function listDirPaths(
        ?callable $filter = null
    ): array {
        return iterator_to_array($this->scanDirPaths($filter));
    }

    public function countDirs(
        ?callable $filter = null
    ): int {
        return $this->countGenerator($this->scanRaw(false, true, $filter, null));
    }


    /**
     * @return Generator<int|string, Dir|File|string>
     */
    protected function scanRaw(
        bool $files,
        bool $dirs,
        ?callable $filter = null,
        ?bool $wrap = true
    ): Generator {
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
            } elseif (
                $item->isFile() ||
                $item->isLink()
            ) {
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

            if (
                $filter &&
                !$filter($key, $output)
            ) {
                continue;
            }

            if ($wrap === null) {
                yield $key;
            } else {
                yield $key => $output;
            }
        }
    }

    abstract protected function getScannerIterator(
        bool $files,
        bool $dirs
    ): Traversable;



    public function scanRecursive(
        ?callable $filter = null
    ): Generator {
        /** @var Generator<string, Dir|File> */
        return $this->scanRawRecursive(true, true, $filter, true);
    }

    public function listRecursive(
        ?callable $filter = null
    ): array {
        return iterator_to_array($this->scanRecursive($filter));
    }

    public function scanNamesRecursive(
        ?callable $filter = null
    ): Generator {
        /** @var Generator<string> */
        return $this->scanRawRecursive(true, true, $filter, null);
    }

    public function listNamesRecursive(
        ?callable $filter = null
    ): array {
        return iterator_to_array($this->scanNamesRecursive($filter));
    }

    public function scanPathsRecursive(
        ?callable $filter = null
    ): Generator {
        /** @var Generator<string, string> */
        return $this->scanRawRecursive(true, true, $filter, false);
    }

    public function listPathsRecursive(
        ?callable $filter = null
    ): array {
        return iterator_to_array($this->scanPathsRecursive($filter));
    }

    public function countContentsRecursive(
        ?callable $filter = null
    ): int {
        return $this->countGenerator($this->scanRawRecursive(true, true, $filter, null));
    }


    public function scanFilesRecursive(
        ?callable $filter = null
    ): Generator {
        /** @var Generator<string, File> */
        return $this->scanRawRecursive(true, false, $filter, true);
    }

    public function listFilesRecursive(
        ?callable $filter = null
    ): array {
        return iterator_to_array($this->scanFilesRecursive($filter));
    }

    public function scanFileNamesRecursive(
        ?callable $filter = null
    ): Generator {
        /** @var Generator<string> */
        return $this->scanRawRecursive(true, false, $filter, null);
    }

    public function listFileNamesRecursive(
        ?callable $filter = null
    ): array {
        return iterator_to_array($this->scanFileNamesRecursive($filter));
    }

    public function scanFilePathsRecursive(
        ?callable $filter = null
    ): Generator {
        /** @var Generator<string, string> */
        return $this->scanRawRecursive(true, false, $filter, false);
    }

    public function listFilePathsRecursive(
        ?callable $filter = null
    ): array {
        return iterator_to_array($this->scanFilePathsRecursive($filter));
    }

    public function countFilesRecursive(
        ?callable $filter = null
    ): int {
        return $this->countGenerator($this->scanRawRecursive(true, false, $filter, null));
    }


    public function scanDirsRecursive(
        ?callable $filter = null
    ): Generator {
        /** @var Generator<string, Dir> */
        return $this->scanRawRecursive(false, true, $filter, true);
    }

    public function listDirsRecursive(
        ?callable $filter = null
    ): array {
        return iterator_to_array($this->scanDirsRecursive($filter));
    }

    public function scanDirNamesRecursive(
        ?callable $filter = null
    ): Generator {
        /** @var Generator<string> */
        return $this->scanRawRecursive(false, true, $filter, null);
    }

    public function listDirNamesRecursive(
        ?callable $filter = null
    ): array {
        return iterator_to_array($this->scanDirNamesRecursive($filter));
    }

    public function scanDirPathsRecursive(
        ?callable $filter = null
    ): Generator {
        /** @var Generator<string, string> */
        return $this->scanRawRecursive(false, true, $filter, false);
    }

    public function listDirPathsRecursive(
        ?callable $filter = null
    ): array {
        return iterator_to_array($this->scanDirPathsRecursive($filter));
    }


    public function countDirsRecursive(
        ?callable $filter = null
    ): int {
        return $this->countGenerator($this->scanRawRecursive(false, true, $filter, null));
    }



    /**
     * @return Generator<int|string, Dir|File|string>
     */
    protected function scanRawRecursive(
        bool $files,
        bool $dirs,
        ?callable $filter = null,
        ?bool $wrap = true
    ): Generator {
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
            } elseif (
                $item->isFile() ||
                $item->isLink()
            ) {
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

            $key = $item->getSubPathname();

            if (
                $filter &&
                !$filter($key, $output)
            ) {
                continue;
            }

            if ($wrap === null) {
                yield $key;
            } else {
                yield $key => $output;
            }
        }
    }

    abstract protected function getRecursiveScannerIterator(
        bool $files,
        bool $dirs
    ): Traversable;




    /**
     * @template TKey
     * @template TValue
     * @param Generator<TKey, TValue> $generator
     */
    protected function countGenerator(
        Generator $generator
    ): int {
        $output = 0;

        foreach ($generator as $item) {
            if ($item !== null) {
                $output++;
            }
        }

        return $output;
    }

    abstract protected function wrapFile(
        string $path
    ): File;
}
