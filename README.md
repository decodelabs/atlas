# Atlas

[![PHP from Packagist](https://img.shields.io/packagist/php-v/decodelabs/atlas?style=flat-square)](https://packagist.org/packages/decodelabs/atlas)
[![Latest Version](https://img.shields.io/packagist/v/decodelabs/atlas.svg?style=flat-square)](https://packagist.org/packages/decodelabs/atlas)
[![Total Downloads](https://img.shields.io/packagist/dt/decodelabs/atlas.svg?style=flat-square)](https://packagist.org/packages/decodelabs/atlas)
[![Build Status](https://img.shields.io/travis/decodelabs/atlas/develop.svg?style=flat-square)](https://travis-ci.org/decodelabs/atlas)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-44CC11.svg?longCache=true&style=flat-square)](https://github.com/phpstan/phpstan)
[![License](https://img.shields.io/packagist/l/decodelabs/atlas?style=flat-square)](https://packagist.org/packages/decodelabs/atlas)

Easy filesystem and io functions for PHP

## Installation

Install via Composer:

```bash
composer install decodelabs/atlas
```


## Usage

### Importing

Atlas uses a [Veneer Facade](https://github.com/decodelabs/veneer) so you don't _need_ to add any <code>use</code> declarations to your code, the class will be aliased into whatever namespace you are working in.

However, if you want to avoid filling your namespace with class aliases, you can import the Facade with:

```php
use DecodeLabs\Atlas;
```


### Basic local filesystem functions

There are many standard filesystem functions represented by either <code>File</code> or <code>Dir</code> objects.
See [Fs.php](./src/Atlas/Plugins/Fs.php), [File/Local.php](./src/Atlas/File/Local.php) and [Dir/Local.php](./src/Atlas/Dir/Local.php) for the full list.

```php
Atlas::$fs->get('/path/to/dir_or_file')
    ->copyTo('/another/path/');

Atlas::$fs->createDir('some/dir/path', 0770);

Atlas::$fs->getFile('my/file')
    ->renameTo('file.txt')
    ->setOwner('user');
```


### Dir scanning

Scan the contents of a folder with optional filtering..
Replace "scan" for "list" to return an array rather than a <code>Generator</code>:

```php
foreach(Atlas::$fs->scan('my/dir') as $name => $fileOrDir) {
    // All files and dirs in my/dir
}

foreach(Atlas::$fs->scanDirs('my/dir') as $name => $dir) {
    // All dirs in my/dir
}

foreach(Atlas::$fs->listFilesRecursive('my/dir', function($name, $file) {
    // Return true if you want the file to be output
    return $name !== 'BadFile.php';
}) as $name => $file) {
    // All files in all dirs in my/dir
}
```

See [Fs.php](./src/Atlas/Plugins/Fs.php) or [Dir/Local.php](./src/Atlas/Dir/ScannerTrait.php) for all scanning options.


### Channels

Channels represent simple in / out handlers and can be written to and read from:

```php
$stream = Atlas::openStream('path/to/file');
$stream->writeLine('Hello world');

$stream = Atlas::openCliOutputStream(); // Same as Atlas::openStream(STDOUT);

$buffer = Atlas::newBuffer();
$buffer->write('Some text to buffer');
echo $buffer->read(6); // "Some t"
```


### IO Broker

Channels can be grouped together and managed by an <code>IO Broker</code> -

```php
// Create a CLI IO handler
$broker = new Atlas::newBroker()
    ->addInputProvider(Atlas::openStream(STDIN))
    ->addOutputReceiver(Atlas::openStream(STDOUT))
    ->addErrorReceiver(Atlas::openStream(STDERR));

// Shortcut to the above:
$broker = Atlas::newCliBroker();


// Read line from CLI
$broker->setReadBlocking(true);
$text = $broker->readLine();

// Write it back to output
$broker->writeLine('INPUT: '.$text);
```

Once grouped, the Channels in an IO broker can be used as the interface between many different information sources; see [Systemic Unix process launcher](https://github.com/decodelabs/systemic/blob/develop/src/Systemic/Process/Launcher/Unix.php) for an example of an IO Broker managing input and output with <code>proc_open()</code>.


### Event loop

Listen for events on IO, Signals and Timers and respond accordingly.
If php's Event extension is available, that will be used, otherwise a basic <code>select()</code> loop fills in the gaps.

```php
$broker = Atlas::newCliBroker();

$eventLoop = Atlas::newEventLoop()

    // Run every 2 seconds
    ->bindTimer('timer1', 2, function() use($broker) {
        $broker->writeLine('Timer 1');
    })

    // Listen for reads, but frozen - won't activate until unfrozen
    ->bindStreamReadFrozen($input = $broker->getFirstInputReceiver(), function() use($broker) {
        $broker->writeLine('You said: '.$broker->readLine());
    })

    // Run once after 1 second
    ->bindTimerOnce('timer2', 1, function($binding) use($broker, $input) {
        $broker->writeLine('Timer 2');

        // Unfreeze io reads
        $binding->eventLoop->unfreeze($intput);
    })

    // Check if we want to bail every second
    ->setCycleHandler(function(int $cycles) {
        if($cycles > 10) {
            return false;
        }
    });


/*
Outputs something like:

Timer 2
Timer 1
Timer 1
You said: Hello world
Timer 1
*/
```

### Mime types

Detect a mime type for a file path:

```php
echo Atlas::$mime->detect(__FILE__);
// application/x-php
```

Get known extensions for a type:

```php
$exts = Atlas::$mime->getExtensions('text/plain');
// txt, text, conf, def, list, log, in
```

Suggest an extension for a mime type:

```php
echo Atlas::$mime->suggestExtension('text/plain');
//txt
```

## Licensing
Atlas is licensed under the MIT License. See [LICENSE](./LICENSE) for the full license text.
