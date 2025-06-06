# Atlas

[![PHP from Packagist](https://img.shields.io/packagist/php-v/decodelabs/atlas?style=flat)](https://packagist.org/packages/decodelabs/atlas)
[![Latest Version](https://img.shields.io/packagist/v/decodelabs/atlas.svg?style=flat)](https://packagist.org/packages/decodelabs/atlas)
[![Total Downloads](https://img.shields.io/packagist/dt/decodelabs/atlas.svg?style=flat)](https://packagist.org/packages/decodelabs/atlas)
[![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/decodelabs/atlas/integrate.yml?branch=develop)](https://github.com/decodelabs/atlas/actions/workflows/integrate.yml)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-44CC11.svg?longCache=true&style=flat)](https://github.com/phpstan/phpstan)
[![License](https://img.shields.io/packagist/l/decodelabs/atlas?style=flat)](https://packagist.org/packages/decodelabs/atlas)

### Easy filesystem and IO functions for PHP

Atlas provides an easy and accessible interface to file system interaction. Read, write, copy and move files without breaking a sweat.

---

## Installation

Install via Composer:

```bash
composer require decodelabs/atlas
```

## Usage

### Basic local filesystem functions

There are many standard filesystem functions represented by either `File` or `Dir` objects.
See [Context.php](./src/Context.php), [File.php](./src/File.php) and [Dir.php](./src/Dir.php) and [Node.php](./src/Node.php) for the full list.

```php
use DecodeLabs\Atlas;

Atlas::get('/path/to/dir_or_file')
    ->copyTo('/another/path/');

Atlas::createDir('some/dir/path', 0770);

Atlas::file('my/file')
    ->renameTo('file.txt')
    ->setOwner('user');

Atlas::gzFile('my/file.gz', 'w')
    ->write('hello world')
    ->close();
```


### Dir scanning

Scan the contents of a folder with optional filtering..
Replace "scan" for "list" to return an array rather than a `Generator`:

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

See [Dir/ScannerTrait.php](./src/Dir/ScannerTrait.php) for all scanning options.


## Licensing
Atlas is licensed under the MIT License. See [LICENSE](./LICENSE) for the full license text.
