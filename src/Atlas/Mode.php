<?php

/**
 * @package Atlas
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Atlas;

use DecodeLabs\Enumerable\Backed\ValueString;
use DecodeLabs\Enumerable\Backed\ValueStringTrait;

enum Mode: string implements ValueString
{
    use ValueStringTrait;

    case ReadOnly = 'rb';
    case ReadWrite = 'r+b';
    case WriteTruncate = 'wb';
    case ReadWriteTruncate = 'w+b';
    case WriteAppend = 'ab';
    case ReadWriteAppend = 'a+b';
    case WriteNew = 'xb';
    case ReadWriteNew = 'x+b';
}
