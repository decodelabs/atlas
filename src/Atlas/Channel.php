<?php

/**
 * @package Atlas
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Atlas;

interface Channel extends DataProvider, DataReceiver
{
    /**
     * @return resource|object|null
     */
    public function getResource();

    /**
     * @return $this
     */
    public function close(): Channel;
}
