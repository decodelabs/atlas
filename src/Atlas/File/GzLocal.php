<?php

/**
 * Atlas
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Atlas\File;

class GzLocal extends Local implements Gz
{
    use GzTrait;
}
