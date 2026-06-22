# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-06-22

### Changed

- Added art, summary and licence section to README

## [0.8.2] - 2026-04-04

### Changed

- Switched to composer to determine package version

### Removed

- Removed version property from composer.json

## [0.8.1] - 2026-04-04

### Changed

- Updated minimum saloon version to address multiple CVEs (#51)

## [0.8.0] - 2026-03-04

### Added

- Added `has*` methods to result classes (#47)

### Changes

- Improved handling of specific push receipt errors (#45)
- Improved collection support (#48)
- Updated minimum phpunit version to address CVE-2026-24765 (#49) 

## [0.7.0] - 2025-12-11

### Added

- Added `sendNotification` method to `ExpoPush` (#41)

### Changed

- Disallowed null in result errors (#43)

## [0.6.0] - 2025-11-02

### Removed

- Removed `final` keyword from `ExpoPush` (#37)

## [0.5.0] - 2025-11-02

### Added

- Added filter method to collection classes (#35)

### Changed

- Made `FailedPushTicket` properties public (#33)

## [0.4.0] - 2025-10-29

### Added

- Added object support to push message data (#30)

## [0.3.0] - 2025-10-20

### Added

- Added data deserialisation for `PushMessage` and `PushToken` (#28)

## [0.2.0] - 2025-06-21

### Added

- Added support for PHP 8.2 and 8.3 (#25)

## [0.1.1] - 2025-06-01

### Fixed

- Corrected package name in composer.json

## [0.1.0] - 2025-06-01

### Added

- ExpoPushConnector (#1)
- SendPushNotifications request (#2)
- GetReceipts request (#5)
- ExpoPush service (#22)
- Request body compression support (#12)
- Request concurrency handling (#3)
- Rate limit handling (#14)
- GitHub testing workflow (#15)
- LICENSE (#16)
- README (#16)
- CHANGELOG (#16)

[1.0.0]: https://github.com/dru1x/expo-server-sdk-php/compare/v0.8.2...v1.0.0
[0.8.2]: https://github.com/dru1x/expo-server-sdk-php/compare/v0.8.1...v0.8.2
[0.8.1]: https://github.com/dru1x/expo-server-sdk-php/compare/v0.8.0...v0.8.1
[0.8.0]: https://github.com/dru1x/expo-server-sdk-php/compare/v0.7.0...v0.8.0
[0.7.0]: https://github.com/dru1x/expo-server-sdk-php/compare/v0.6.0...v0.7.0
[0.6.0]: https://github.com/dru1x/expo-server-sdk-php/compare/v0.5.0...v0.6.0
[0.5.0]: https://github.com/dru1x/expo-server-sdk-php/compare/v0.4.0...v0.5.0
[0.4.0]: https://github.com/dru1x/expo-server-sdk-php/compare/v0.3.0...v0.4.0
[0.3.0]: https://github.com/dru1x/expo-server-sdk-php/compare/v0.2.0...v0.3.0
[0.2.0]: https://github.com/dru1x/expo-server-sdk-php/compare/v0.1.1...v0.2.0
[0.1.1]: https://github.com/dru1x/expo-server-sdk-php/compare/v0.1.0...v0.1.1
[0.1.0]: https://github.com/dru1x/expo-server-sdk-php/releases/tag/v0.0.1
