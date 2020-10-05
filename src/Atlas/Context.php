<?php
/**
 * This file is part of the Atlas package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Atlas;

use DecodeLabs\Veneer\Plugin as VeneerPlugin;
use DecodeLabs\Veneer\Plugin\Provider as VeneerPluginProvider;
use DecodeLabs\Veneer\Plugin\ProviderTrait as VeneerPluginProviderTrait;
use DecodeLabs\Veneer\Plugin\AccessTarget as VeneerPluginAccessTarget;
use DecodeLabs\Veneer\Plugin\AccessTargetTrait as VeneerPluginAccessTargetTrait;

use DecodeLabs\Atlas\Channel;
use DecodeLabs\Atlas\Channel\Stream;
use DecodeLabs\Atlas\Channel\Buffer;

use DecodeLabs\Atlas\Mutex;
use DecodeLabs\Atlas\Mutex\Local as LocalMutex;

use DecodeLabs\Atlas\EventLoop;
use DecodeLabs\Atlas\EventLoop\Event as LibEventLoop;
use DecodeLabs\Atlas\EventLoop\Select as SelectEventLoop;

use DecodeLabs\Exceptional;

class Context implements VeneerPluginProvider, VeneerPluginAccessTarget
{
    use VeneerPluginProviderTrait;
    use VeneerPluginAccessTargetTrait;

    const PLUGINS = [
        'fs', 'mime', 'http'
    ];


    /**
     * Stub to get empty plugin list to avoid broken targets
     */
    public function getVeneerPluginNames(): array
    {
        return static::PLUGINS;
    }


    /**
     * Load factory plugins
     */
    public function loadVeneerPlugin(string $name): VeneerPlugin
    {
        if (!in_array($name, self::PLUGINS)) {
            throw Exceptional::InvalidArgument(
                $name.' is not a recognised Veneer plugin'
            );
        }

        $class = '\\DecodeLabs\\Atlas\\Plugins\\'.ucfirst($name);
        return new $class($this);
    }



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
     * Open a STDIN Channel
     */
    public function openCliInputStream(): Channel
    {
        if (!defined('STDIN')) {
            throw Exceptional::Runtime(
                'STDIN is only available on the CLI SAPI'
            );
        }

        return new Stream(\STDIN, 'r');
    }

    /**
     * Open a STDOUT Channel
     */
    public function openCliOutputStream(): Channel
    {
        if (!defined('STDOUT')) {
            throw Exceptional::Runtime(
                'STDOUT is only available on the CLI SAPI'
            );
        }

        return new Stream(\STDOUT, 'w');
    }

    /**
     * Open a STDERR Channel
     */
    public function openCliErrorStream(): Channel
    {
        if (!defined('STDERR')) {
            throw Exceptional::Runtime(
                'STDERR is only available on the CLI SAPI'
            );
        }

        return new Stream(\STDERR, 'w');
    }


    /**
     * Open HTTP input Channel
     */
    public function openHttpInputStream(): Channel
    {
        return new Stream('php://input', 'r');
    }

    /**
     * Open HTTP output Channel
     */
    public function openHttpOutputStream(): Channel
    {
        return new Stream('php://output', 'w');
    }



    /**
     * Create a new buffer Channel
     */
    public function newBuffer(?string $buffer=null): Buffer
    {
        return new Buffer($buffer);
    }


    /**
     * Create a new local Mutex
     */
    public function newMutex(string $name, string $dir): LocalMutex
    {
        return new LocalMutex($name, $dir);
    }


    /**
     * New IO Broker
     */
    public function newBroker(): Broker
    {
        return new Broker();
    }

    /**
     * Create STD IO Broker
     */
    public function newCliBroker(): Broker
    {
        return $this->newBroker()
            ->addInputProvider($this->openCliInputStream())
            ->addOutputReceiver($this->openCliOutputStream())
            ->addErrorReceiver($this->openCliErrorStream());
    }

    /**
     * Create HTTP IO Broker
     */
    public function newHttpBroker(): Broker
    {
        return $this->newBroker()
            ->addInputProvider($this->openHttpInputStream())
            ->addOutputReceiver($this->openHttpOutputStream());
    }


    /**
     * Create an event loop
     */
    public function newEventLoop(): EventLoop
    {
        if (extension_loaded('event')) {
            return new LibEventLoop();
        } else {
            return new SelectEventLoop();
        }
    }
}
