# Atlas — Package Specification

> **Cluster:** `io`
> **Language:** `php`
> **Milestone:** `m1`
> **Repo:** `https://github.com/decodelabs/atlas`
> **Role:** Filesystem

This document describes the purpose, contracts, and design of **Atlas** within the Decode Labs ecosystem.

It is aimed at:

- Developers **using** Atlas in their own applications or libraries.
- Contributors **maintaining or extending** Atlas.
- Tools and AI assistants that need to reason about its behaviour.

---

## 1. Overview

### 1.1 Purpose

Atlas provides an object-oriented, fluent interface for filesystem operations in PHP. It abstracts common file and directory operations (read, write, copy, move, scan) into a consistent API that works with both files and directories through a unified `Node` interface. The package aims to make filesystem interactions more intuitive and less error-prone than using raw PHP filesystem functions.

### 1.2 Non-Goals

Atlas does **not**:

- Provide network filesystem support (FTP, S3, etc.) — it focuses on local filesystem operations
- Implement file watching or change notifications — it only checks for changes on demand
- Handle file compression/decompression beyond gzip file streams
- Provide database-like querying or indexing of filesystem contents
- Manage file permissions beyond basic POSIX-style permissions

---

## 2. Role in the Ecosystem

### 2.1 Cluster & Positioning

- **Cluster:** `io` (see Chorus taxonomy)
- Atlas is a foundational I/O package that provides filesystem abstraction for the Decode Labs ecosystem. It sits at a low dependency level, depending only on core language utilities (Coercion, Deliverance, Enumerable, Exceptional, Nuance) and is used by many higher-level packages that need filesystem access.

### 2.2 Typical Usage Contexts

Typical places Atlas appears:

- Configuration file reading and writing
- Log file management
- Cache directory operations
- Asset file handling
- Temporary file creation and cleanup
- Directory scanning and filtering operations

Atlas is intended to be used whenever an application needs to interact with the local filesystem in a structured, object-oriented manner rather than using raw PHP filesystem functions.

---

## 3. Public Surface

> This section focuses on the conceptual API, not every symbol.

### 3.1 Key Types

The primary public types are:

- `DecodeLabs\Atlas`
  Static facade class providing convenient entry points for all filesystem operations. Methods return `File` or `Dir` objects based on the path provided.

- `DecodeLabs\Atlas\Node`
  Base interface representing either a file or directory. Provides common operations like `exists()`, `copy()`, `move()`, `delete()`, `setPermissions()`, and metadata access.

- `DecodeLabs\Atlas\File`
  Interface extending `Node` and `DecodeLabs\Deliverance\Channel` for file-specific operations. Provides file I/O, locking, seeking, and content manipulation methods.

- `DecodeLabs\Atlas\Dir`
  Interface extending `Node` for directory-specific operations. Provides directory scanning (with filtering), recursive traversal, child management, and bulk operations.

- `DecodeLabs\Atlas\Mode`
  Enum representing file open modes (read-only, read-write, write-truncate, write-append, etc.).

- `DecodeLabs\Atlas\Mutex`
  Interface for file-based mutex/locking operations.

- `DecodeLabs\Atlas\File\Local`
  Concrete implementation of `File` for local filesystem files.

- `DecodeLabs\Atlas\File\Memory`
  Concrete implementation of `File` for in-memory file operations.

- `DecodeLabs\Atlas\File\Gz`
  Concrete implementation of `File` for gzip-compressed files.

- `DecodeLabs\Atlas\Dir\Local`
  Concrete implementation of `Dir` for local filesystem directories.

### 3.2 Main Entry Points

The main usage pattern is through the static `Atlas` facade class:

```php
use DecodeLabs\Atlas;

// Get a file or directory (auto-detects type)
$node = Atlas::get('/path/to/file_or_dir');

// Work with files
$file = Atlas::getFile('my/file.txt');
$content = $file->getContents();
$file->putContents('new content');

// Work with directories
$dir = Atlas::getDir('my/dir');
foreach ($dir->scanFiles() as $name => $file) {
    // Process files
}

// Fluent operations
Atlas::get('/path/to/file')
    ->copyTo('/destination/dir')
    ->setPermissions(0644);
```

Key concepts:
- **Unified interface**: Both files and directories implement `Node`, allowing polymorphic operations
- **Lazy instantiation**: Objects are created without requiring the filesystem entity to exist
- **Fluent API**: Most operations return the object itself, enabling method chaining
- **Generator-based scanning**: Directory scanning uses generators for memory efficiency
- **Channel integration**: Files implement `Deliverance\Channel` for streaming I/O

---

## 4. Dependencies

### 4.1 Direct Decode Labs Dependencies

From `composer.json`:

- `decodelabs/coercion`
  Used for type coercion in file operations and path handling.

- `decodelabs/deliverance`
  Files implement the `Channel` interface from Deliverance for streaming I/O operations.

- `decodelabs/enumerable`
  The `Mode` enum uses `Enumerable` traits for enum utilities.

- `decodelabs/exceptional`
  All error conditions throw exceptions via the Exceptional package.

- `decodelabs/nuance`
  Used for debugging and entity representation (Dumpable interface).

**Optional integration:**

None required.

### 4.2 External Dependencies

None required for runtime operation beyond standard PHP filesystem functions.

See `composer.json` for supported PHP versions.

---

## 5. Behaviour & Contracts

### 5.1 Invariants

- All `Node` objects (files and directories) are immutable in terms of their path — operations that modify location return new objects or update the existing object's internal state, but the original path reference remains valid
- File and directory objects can be created for non-existent paths — existence is checked lazily when operations require it
- Directory scanning operations (`scan*` methods) return generators for memory efficiency, while `list*` methods return arrays
- All I/O operations on files respect the open mode — attempting to write to a read-only file will fail
- Recursive operations on directories process children depth-first

### 5.2 Input & Output Contracts

**Path handling:**
- Methods accepting paths accept `string|Stringable|Dir|File` — if a `Node` object is passed, it is used directly; otherwise, a new object is created
- Paths are normalized (trailing slashes removed for directories)
- Relative paths are resolved relative to the current working directory

**File operations:**
- `getFile()` creates a file object without requiring the file to exist
- `getExistingFile()` returns `null` if the file does not exist
- File I/O operations require the file to be opened with an appropriate mode
- File locking is advisory (flock-based) and can be non-blocking

**Directory operations:**
- `getDir()` creates a directory object without requiring the directory to exist
- `getExistingDir()` returns `null` if the directory does not exist
- `ensureExists()` creates the directory (and parent directories) if it doesn't exist
- Scanning operations accept an optional filter callback: `callable(string $name, Dir|File $node): bool`
- Recursive scanning operations traverse subdirectories depth-first

**Return values:**
- Methods that modify the node return `$this` for fluent chaining
- Methods that create new nodes return the new node object
- Generator methods yield `Generator<string, Dir|File>` where the key is the name and value is the node object

---

## 6. Error Handling

### 6.1 Exception Types

Atlas throws exceptions via the Exceptional package:

- `DecodeLabs\Exceptional\Io`: Thrown for filesystem I/O errors (file not found, permission denied, disk full, etc.)
- `DecodeLabs\Exceptional\Runtime`: Thrown for runtime errors (invalid state, type mismatches, etc.)
- `DecodeLabs\Exceptional\InvalidArgument`: Thrown for invalid arguments (wrong node type, invalid mode, etc.)

### 6.2 Error Strategy

Atlas follows a fail-fast error strategy. Operations that cannot complete successfully throw exceptions immediately rather than returning error codes or false. This ensures that errors are not silently ignored and forces explicit error handling. The package uses the Exceptional pattern for consistent exception handling across the Decode Labs ecosystem.

---

## 7. Configuration & Extensibility

### 7.1 Configuration

No runtime configuration is required. All operations use sensible defaults:
- Directory creation uses `0777` permissions if not specified
- File operations use binary mode by default (via `Mode` enum)
- Temporary files and directories are created in the system temp directory

### 7.2 Extension Points

Atlas supports extension via:

- **Custom file implementations**: Implement the `File` interface to support custom filesystem backends (e.g., in-memory, network, etc.)
- **Custom directory implementations**: Implement the `Dir` interface for custom directory backends
- **Filter callbacks**: All scanning operations accept filter callbacks for custom filtering logic
- **Trait composition**: The `NodeTrait`, `Node\LocalTrait`, and `Dir\ScannerTrait` can be used to build custom implementations

The package is designed to be extended for custom filesystem backends while maintaining the same public API.

---

## 8. Interactions with Other Packages

Atlas is designed to be used by many packages in the Decode Labs ecosystem:

- **`decodelabs/dovetail`**
  Uses Atlas for reading and writing configuration files.

- **`decodelabs/chronicle`**
  Uses Atlas for log file management.

- **`decodelabs/stash`**
  Uses Atlas for cache file operations.

- **`decodelabs/genesis`**
  Uses Atlas for application file structure management.

- **`decodelabs/greenleaf`**
  Uses Atlas for route file scanning.

- **`decodelabs/clip`**
  Uses Atlas for CLI file operations.

Design assumptions:

- Atlas is available early in the application stack (low dependency level)
- Filesystem operations are synchronous and blocking
- The local filesystem is POSIX-compatible (Unix-style permissions and paths)
- Safe to use from any layer of the application (no framework dependencies)

---

## 9. Usage Examples

### 9.1 Basic File Operations

```php
use DecodeLabs\Atlas;

// Read a file
$content = Atlas::getContents('/path/to/file.txt');

// Write a file
Atlas::createFile('/path/to/file.txt', 'content');

// Copy a file
Atlas::getFile('source.txt')
    ->copyTo('/destination/dir', 'renamed.txt');

// Check if file changed
if (Atlas::hasFileChanged('/path/to/file.txt', 30)) {
    // File was modified in last 30 seconds
}
```

### 9.2 Directory Scanning

```php
use DecodeLabs\Atlas;

// Scan all files in a directory
foreach (Atlas::scanFiles('/path/to/dir') as $name => $file) {
    echo "Found file: $name\n";
}

// Recursive scan with filtering
$phpFiles = Atlas::listFilesRecursive('/src', function($name, $file) {
    return str_ends_with($name, '.php');
});

// Count files
$count = Atlas::countFiles('/path/to/dir');
```

### 9.3 File I/O with Modes

```php
use DecodeLabs\Atlas;
use DecodeLabs\Atlas\Mode;

// Open file for reading
$file = Atlas::getFile('data.txt', Mode::ReadOnly);
$content = $file->getContents();
$file->close();

// Open file for writing (truncates)
$file = Atlas::getFile('output.txt', Mode::WriteTruncate);
$file->write('Hello, World!');
$file->flush();
$file->close();

// Append to file
$file = Atlas::getFile('log.txt', Mode::WriteAppend);
$file->write("New log entry\n");
$file->close();
```

### 9.4 Gzip File Operations

```php
use DecodeLabs\Atlas;
use DecodeLabs\Atlas\Mode;

// Read gzip file
$gzFile = Atlas::getGzFile('data.gz', Mode::ReadOnly);
$content = $gzFile->getContents();
$gzFile->close();

// Write gzip file
$gzFile = Atlas::getGzFile('output.gz', Mode::WriteTruncate);
$gzFile->write('compressed content');
$gzFile->close();
```

### 9.5 Directory Management

```php
use DecodeLabs\Atlas;

// Create directory with permissions
$dir = Atlas::createDir('/path/to/dir', 0755);

// Check if directory is empty
if ($dir->isEmpty()) {
    // Directory is empty
}

// Get child file
$file = $dir->getFile('child.txt');

// Create subdirectory
$subdir = $dir->createDir('subdir');

// Empty directory (delete all contents)
$dir->emptyOut();

// Merge directory into another
$dir->mergeInto('/destination/dir');
```

---

## 10. Implementation Notes (For Contributors)

### 10.1 Internal Architecture

At a high level, Atlas:

- Uses interface-based design with concrete implementations (`Local`, `Memory`, `Gz`) for different filesystem backends
- Leverages PHP's SPL iterators (`DirectoryIterator`, `RecursiveDirectoryIterator`) for efficient directory scanning
- Implements the `Deliverance\Channel` interface for file I/O, enabling streaming operations
- Uses traits (`LocalTrait`, `ScannerTrait`) to share common implementation code between classes
- Provides both generator-based (`scan*`) and array-based (`list*`) methods for different use cases

Contributors should:

- Preserve the interface-based design — new backends should implement the interfaces, not extend concrete classes
- Maintain consistency between `File` and `Dir` APIs where operations are conceptually similar
- Use generators for scanning operations to maintain memory efficiency
- Follow the Exceptional pattern for all error conditions
- Ensure all operations are thread-safe where applicable (file locking, etc.)

### 10.2 Performance Considerations

- Directory scanning uses PHP's native iterators for efficiency
- Generator-based methods (`scan*`) are memory-efficient for large directories
- File operations use PHP's native stream functions for optimal performance
- Stat cache is cleared explicitly when needed to avoid stale metadata
- Recursive operations process depth-first to minimize memory usage

### 10.3 Gotchas & Historical Decisions

- **Path normalization**: Directories always have trailing slashes removed internally, but this is handled transparently
- **File vs Directory detection**: The `get()` method uses `is_dir()` to detect type, which can be ambiguous for non-existent paths — use `getFile()` or `getDir()` explicitly when the type is known
- **Resource management**: File resources are automatically closed in destructors, but explicit `close()` calls are recommended for clarity
- **Mode enum**: The `Mode` enum uses binary mode flags (`'rb'`, `'wb'`, etc.) to ensure cross-platform compatibility
- **Mutex implementation**: Mutex uses file-based locking, which is advisory and may not work across network filesystems

---

## 11. Testing & Quality

### 11.1 Testing Strategy

Tests should cover:

- Core file operations (read, write, copy, move, delete)
- Core directory operations (create, scan, recursive operations)
- File I/O modes and streaming operations
- Error conditions (permissions, non-existent paths, invalid operations)
- Edge cases (empty directories, symlinks, special characters in paths)
- Generator vs array methods consistency
- Recursive operations on nested directory structures

### 11.2 Quality Signals

From the Decode Labs package index (at time of writing):

- **Code:** 3.5
- **Readme:** 3
- **Docs:** 0
- **Tests:** 0

Atlas is a stable, foundational package with good code quality and a clear API. Documentation and test coverage are areas for improvement.

---

## 12. Roadmap & Future Ideas

Non-binding ideas:

- Add file watching/change notification support
- Support for additional compression formats beyond gzip
- Network filesystem backends (FTP, S3, etc.)
- Improved test coverage
- Performance optimizations for large directory scans
- Better symlink handling and support

---

## 13. References

- **Chorus docs:**
  - Architecture principles
  - Package taxonomy & clusters
  - Backwards compatibility strategy (once published)

- **Related packages:**
  - `decodelabs/deliverance` (I/O channel interface)
  - `decodelabs/coercion` (type coercion utilities)
  - `decodelabs/exceptional` (exception handling)

- **Repository:**
  - `https://github.com/decodelabs/atlas`

---

> This spec is intended to stay in sync with the **actual behaviour** of the package.
> When you make significant changes to the public surface or semantics, please update this document and, where applicable, add or update ADRs in Chorus.

