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
