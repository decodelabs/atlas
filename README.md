# Atlas

[![PHP from Packagist](https://img.shields.io/packagist/php-v/decodelabs/atlas?style=flat-square)](https://packagist.org/packages/decodelabs/atlas)
[![Latest Version](https://img.shields.io/packagist/v/decodelabs/atlas.svg?style=flat-square)](https://packagist.org/packages/decodelabs/atlas)
[![Total Downloads](https://img.shields.io/packagist/dt/decodelabs/atlas.svg?style=flat-square)](https://packagist.org/packages/decodelabs/atlas)
[![Build Status](https://img.shields.io/travis/com/decodelabs/atlas/main.svg?style=flat-square)](https://travis-ci.com/decodelabs/atlas)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-44CC11.svg?longCache=true&style=flat-square)](https://github.com/phpstan/phpstan)
[![License](https://img.shields.io/packagist/l/decodelabs/atlas?style=flat-square)](https://packagist.org/packages/decodelabs/atlas)

Easy filesystem and io functions for PHP

## Installation

Install via Composer:

```bash
composer require decodelabs/atlas
```

### PHP version

_Please note, the final v1 releases of all Decode Labs libraries will target **PHP8** or above._

Current support for earlier versions of PHP will be phased out in the coming months.


## Usage

### Importing

Atlas uses [Veneer](https://github.com/decodelabs/veneer) to provide a unified frontage under <code>DecodeLabs\Atlas</code>.
You can access all the primary functionality via this static frontage without compromising testing and dependency injection.


### Basic local filesystem functions

There are many standard filesystem functions represented by either <code>File</code> or <code>Dir</code> objects.
See [Fs.php](./src/Atlas/Plugins/Fs.php), [File/Local.php](./src/Atlas/File/Local.php) and [Dir/Local.php](./src/Atlas/Dir/Local.php) for the full list.

```php
use DecodeLabs\Atlas;

Atlas::get('/path/to/dir_or_file')
    ->copyTo('/another/path/');

Atlas::createDir('some/dir/path', 0770);

Atlas::getFile('my/file')
    ->renameTo('file.txt')
    ->setOwner('user');
```


### Dir scanning

Scan the contents of a folder with optional filtering..
Replace "scan" for "list" to return an array rather than a <code>Generator</code>:

```php
use DecodeLabs\Atlas;

foreach(Atlas::scan('my/dir') as $name => $fileOrDir) {
    // All files and dirs in my/dir
}

foreach(Atlas::scanDirs('my/dir') as $name => $dir) {
    // All dirs in my/dir
}

foreach(Atlas::listFilesRecursive('my/dir', function($name, $file) {
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
use DecodeLabs\Atlas;

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
use DecodeLabs\Atlas;

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


### Mime types

Looking for the mime type detection stuff that used to be here?

This has been moved to its own project, [Typify](https://github.com/decodelabs/typify/).

## Licensing
Atlas is licensed under the MIT License. See [LICENSE](./LICENSE) for the full license text.
