<?php

/**
 * @package Atlas
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Atlas;

interface Mode
{
    public const READ_ONLY = 'rb';
    public const READ_WRITE = 'r+b';
    public const WRITE_TRUNCATE = 'wb';
    public const READ_WRITE_TRUNCATE = 'w+b';
    public const WRITE_APPEND = 'ab';
    public const READ_WRITE_APPEND = 'a+b';
    public const WRITE_NEW = 'xb';
    public const READ_WRITE_NEW = 'x+b';
}
