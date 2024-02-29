<?php

/**
 * @package Atlas
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Atlas\File;

interface GzOpenable
{
    public function gzOpen(
        string $mode
    ): Gz;
}
