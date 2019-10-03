<?php
/**
 * This file is part of the Atlas package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Atlas;

use DecodeLabs\Veneer\FacadeTarget;
use DecodeLabs\Veneer\FacadeTargetTrait;

use DecodeLabs\Atlas\Channel;
use DecodeLabs\Atlas\Channel\Stream;
use DecodeLabs\Atlas\Channel\Buffer;

class Context implements FacadeTarget
{
    use FacadeTargetTrait;

    const FACADE = 'Atlas';

    /**
     * Open a stream Channel
     */
    public function openStream($path, string $mode='a+'): Channel
    {
        if ($path instanceof Channel) {
            return $path;
        }

        return new Stream($path, $mode);
    }

    /**
     * Create a new buffer Channel
     */
    public function newBuffer(?string $buffer=null): Buffer
    {
        return new Buffer($buffer);
    }
}
