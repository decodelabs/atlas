<?php
/**
 * This file is part of the Atlas package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Atlas;

use DecodeLabs\Atlas\DataReceiver;

interface DataSender
{
    public function setDataReceiver(DataReceiver $receiver): DataSender;
    public function getDataReceiver(): ?DataReceiver;
    public function sendData(): void;
}
