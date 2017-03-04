# Change Log

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## Unreleased
### Changed
- Improved CS and added better comments/DocBlocks

## [2.0.0] - 2016-03-29
### Added
- Projects have access to global scripts defined in scripts.json.
- Create new servers with an interactive prompt using the `add` command.
- Migrate from version 1 of Mersey to version 2 by running `mersey migrate 1-2`

### Changed
- **Breaking Change**: Scripts are now stored as objects not elements of an object.
- Refactored large part of Mersey.
- Server's alias is listed when issuing the `ping` command.
- Errors in config files give more detailed messages.
- Servers are in a `server:` command namespace. This does not break the syntax `mersey serverone superproject`.

### Fixed
- Errors no longer return status code of 0.

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