<?php
/**
 * This is a stub file for IDE compatibility only.
 * It should not be included in your projects.
 */
namespace DecodeLabs;
use DecodeLabs\Veneer\Proxy;
use DecodeLabs\Veneer\ProxyTrait;
use DecodeLabs\Atlas\Context as Inst;
class Atlas implements Proxy { use ProxyTrait; 
const VENEER = 'DecodeLabs\Atlas';
const VENEER_TARGET = Inst::class;
const PLUGINS = Inst::PLUGINS;
public static $http;};
