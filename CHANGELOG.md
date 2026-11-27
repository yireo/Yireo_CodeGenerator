# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 27 November 2026
### Added
- `yireo:code:generate` command that generates test classes for a module, from a module name, a scenario and a `--type` option (unit or integration)
- Generators can be extended via DI types
- Support for PHP 8.2 up to 8.5 (PHP 8.1 is no longer supported)

### Fixed
- The command validates the module name, type and scenario, and refuses to run in production mode
- Generated files are written only as `.php` files inside the module's `Test/<Type>/` folder, and values are exported as escaped PHP literals
- Class stubs only accept valid class names inside their own module and can no longer create arbitrary classes via the ObjectManager
- composer.json declares `ext-pcre` and no longer requires the unused `nette/utils`
