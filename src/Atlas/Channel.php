<?php

/**
 * @package Atlas
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Atlas;

interface Channel extends DataProvider, DataReceiver
{
    public function getResource();
    public function close(): Channel;
}
