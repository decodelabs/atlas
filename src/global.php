<?php
/**
 * This file is part of the Veneer package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);

/**
 * global helpers
 */
namespace DecodeLabs\Atlas
{
    use DecodeLabs\Atlas;
    use DecodeLabs\Atlas\Context;
    use DecodeLabs\Veneer;

    // Register the Veneer facade
    Veneer::register(Context::class, Atlas::class);
}
