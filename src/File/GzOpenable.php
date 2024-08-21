<?php

/**
 * @package Atlas
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Atlas\File;

use DecodeLabs\Atlas\Mode;

interface GzOpenable
{
    public function gzOpen(
        string|Mode $mode
    ): Gz;
}
