<?php

/**
 * @package Atlas
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Atlas\Mutex;

use DecodeLabs\Atlas\File\Local as LocalFile;
use DecodeLabs\Atlas\Mutex;
use DecodeLabs\Atlas\MutexTrait;

use DecodeLabs\Glitch\Dumpable;

class Local implements
    Mutex,
    Dumpable
{
    use MutexTrait {
        MutexTrait::__construct as private __mutexConstruct;
    }

    protected LocalFile $file;

    public function __construct(
        string $name,
        string $dir
    ) {
        $this->__mutexConstruct($name);
        $this->file = new LocalFile($dir . '/' . $name . '.lock');
    }


    protected function acquireLock(
        bool $blocking
    ): bool {
        if ($this->file->exists()) {
            return false;
        }

        $this->file->open('c');
        return $this->file->lockExclusive(!$blocking);
    }

    protected function releaseLock(): void
    {
        $this->file->unlock()->close()->delete();
    }


    public function glitchDump(): iterable
    {
        yield 'properties' => [
            '*name' => $this->name,
            '*file' => $this->file
        ];

        yield 'metaList' => [
            'counter' => $this->counter,
            'locked' => $this->isLocked()
        ];
    }
}
