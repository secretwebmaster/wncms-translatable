# Changelog
All notable changes to this project will be documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [v1.4.0] - 2026-04-08
### Changed
- Upgrade package baseline to PHP 8.4 and Laravel 13 (`illuminate/database` / `illuminate/support` ^13.0)
- Upgrade test stack to Orchestra Testbench 11 and PHPUnit 12
- Set branch alias to `1.4.x-dev`
- Add migration publish tag support via `translatable-migrations`

### Fixed
- Do not abort model `save()` / `update()` when skipping default-locale translation persistence
- Align package tests with monorepo execution and current PHPUnit metadata requirements
- Verify translation relation cleanup and locale-aware attribute access under Illuminate 13
