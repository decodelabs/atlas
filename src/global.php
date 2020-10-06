<?php

/**
 * @package Atlas
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

/**
 * global helpers
 */
namespace DecodeLabs\Atlas
{
    use DecodeLabs\Atlas;
    use DecodeLabs\Veneer;

    // Register the Veneer facade
    Veneer::register(Context::class, Atlas::class);
}
