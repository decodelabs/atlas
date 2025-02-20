## v0.12.4 (2025-02-20)
* Upgraded Coercion dependency
* Tidied boolean logic

## v0.12.3 (2025-02-13)
* Upgraded PHPStan to v2
* Updated dependencies
* Improved exception syntax
* Added PHP8.4 to CI workflow
* Made PHP8.4 minimum version

## v0.12.2 (2025-02-07)
* Fixed implicit nullable arguments in interfaces

## v0.12.1 (2025-02-07)
* Fixed implicit nullable arguments
* Added @phpstan-require-implements constraints
* Fixed read length constraint

## v0.12.0 (2024-08-21)
* Converted Mode to enum
* Updated Veneer dependency and Stub

## v0.11.4 (2024-07-17)
* Updated Veneer dependency

## v0.11.3 (2024-04-29)
* Fixed Veneer stubs in gitattributes

## v0.11.2 (2024-03-21)
* Catch chmod issues in ensureExists()

## v0.11.1 (2024-02-29)
* Allow DateInterval formats for hasChangedIn()
* Rewind data file in putContents()
* Made PHP8.1 minimum version
* Refactored package file structure

## v0.11.0 (2023-10-16)
* Moved HTTP plugin to Hydro package

## v0.10.7 (2023-10-16)
* Updated HTTP Message dependency
* Fixed timestamp type check

## v0.10.6 (2023-09-26)
* Converted phpstan doc comments to generic
* Ensure dir exists before rmdir()
* Fixed PHP8.1 testing
* Migrated to use effigy in CI workflow

## v0.10.5 (2022-09-27)
* Updated Veneer stub
* Updated composer check script

## v0.10.4 (2022-09-27)
* Converted Veneer plugins to load with Attributes

## v0.10.3 (2022-09-08)
* Added Gz File support

## v0.10.2 (2022-09-08)
* Updated Collections dependency
* Updated CI environment

## v0.10.1 (2022-08-23)
* Added concrete types to all members

## v0.10.0 (2022-08-22)
* Removed PHP7 compatibility
* Updated ECS to v11
* Updated PHPUnit to v9

## v0.9.4 (2022-03-10)
* Transitioned from Travis to GHA
* Updated PHPStan and ECS dependencies

## v0.9.3 (2021-10-20)
* Updated Veneer dependency

## v0.9.2 (2021-05-11)
* Added Veneer IDE support stub

## v0.9.1 (2021-05-01)
* Improved return type hints
* Added check for incorrect fs types

## v0.9.0 (2021-04-09)
* Moved mime plugin to Typify
* Moved transfer interfaces to Deliverance
* Moved fs functions to root

## v0.8.1 (2021-04-08)
* Updated for max PHPStan conformance

## v0.8.0 (2021-03-18)
* Enabled PHP8 testing
* Moved EventLoop structure to Eventful library
* Applied full PSR12 standards
* Added PSR12 check to Travis build

## v0.7.9 (2020-10-06)
* Removed Systemic dependency

## v0.7.8 (2020-10-05)
* Improved readme
* Updated PHPStan

## v0.7.7 (2020-10-05)
* Updated to Veneer 0.6

## v0.7.6 (2020-10-02)
* Updated glitch-support

## v0.7.5 (2020-10-02)
* Removed Glitch dependency

## v0.7.4 (2020-09-30)
* Switched to Exceptional for exception generation

## v0.7.3 (2020-09-25)
* Switched to Glitch Dumpable interface

## v0.7.2 (2020-09-24)
* Updated Composer dependency handling

## v0.7.1 (2019-11-06)
* Added basic HTTP support plugin
* Fixed signal handling in Select() EventLoop
* Improved Memory File construction
* Fixed copy\* return values in fs plugin
* Fixed output type check in fs copy\*()
* Updated Veneer dependency

## v0.7.0 (2019-10-26)
* Added mime type plugin
* Added temp and memory file creation
* Added readFrom() and readChar() to streams
* Split Channel interface into lower level DataProvider / Receiver components
* Improved symbolic link handling in Fs functions
* Added scanPaths and listPaths dir helpers
* Improved PHPStan setup

## v0.6.1 (2019-10-16)
* Added PHPStan support
* Bugfixes and updates from max level PHPStan scan

## v0.6.0 (2019-10-11)
* Added event loop system
* Added receiver proxy Channel
* Fixed Broker write and writeError handlers

## v0.5.0 (2019-10-06)
* Ported initial channel structure from Df
* Ported initial filesystem access functions from Df
* Ported Mutex from Df
* Added Io Broker (originally from r7)
