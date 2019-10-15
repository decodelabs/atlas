<?php
/**
 * This file is part of the Atlas package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Atlas;

/**
 * !IMPORTANT! This interface is not complete and exists for temporary compatibility
 */
interface Socket extends Channel
{
    public function getId(): string;
}
