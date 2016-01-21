# Change Log

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## Unreleased
### Added 
- More tests.

### Changed
- Improved unit tests for SSH command execution.
- Refactored main app file to be less confusing.
- Server's alias is listed when issuing the `ping` command.

### Fixed
- Errors no longer return a code of 0.

## [1.1.1] - 2015-10-18
### Fixed
- Display the correct version number in console.

## [1.1.0] - 2015-10-18
### Added
- New `edit` command. A shortcut to edit the servers.json file.
- Better feedback when using `mersey <servername> <projectname>`.
- Added and improved multiple tests.

## [1.0.0] - 2015-10-14
### Added
- First release.