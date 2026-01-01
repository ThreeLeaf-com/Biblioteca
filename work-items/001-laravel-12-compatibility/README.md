# Update to be compatible with Laravel 12

**Issue/Ticket**: [GitHub #1](https://github.com/ThreeLeaf-com/Biblioteca/issues/1)  
**Status**: In Progress

**Branch Information:**
- **Original Branch**: `staging`
- **Work Branch**: `feature/issue-1-laravel-12-compatibility`
- **Issue Type**: Feature

## Overview

Update the Biblioteca package to be compatible with Laravel 12. This is a blocker for the Laravel 12 upgrade in the ThreeLeaf.com project (see [work-item #100](https://github.com/ThreeLeaf-com/ThreeLeaf.com/issues/100)).

The Biblioteca package currently supports Laravel 10 (specified in `composer.json` as `illuminate/database: ^v10.0` and `illuminate/support: ^10.0`). To enable the ThreeLeaf.com project to upgrade to Laravel 12, Biblioteca must be updated to support Laravel 12, including updating the version number, verifying composer commands work, and ensuring all tests pass.

## Status

- **In Progress** (2025-01-15)
- **Current Phase**: Implementation complete - all tests passing
- **Next Steps**: Commit changes, create release tag, and update Packagist

## Action Items

- [ ] Update package version number in `composer.json` to `2.0.0` (from 1.0.0)
- [ ] Update `composer.json` dependencies to Laravel 12:
  - [ ] `illuminate/database: ^v10.0` → `^12.0`
  - [ ] `illuminate/support: ^10.0` → `^12.0`
  - [ ] Update `orchestra/testbench` to Laravel 12 compatible version
  - [ ] Review and update `phpunit/phpunit` if needed
  - [ ] Review and update `darkaonline/l5-swagger` if needed
- [ ] Review Laravel 12 upgrade guide for breaking changes
- [ ] Review and update service provider (`src/Providers/BibliotecaServiceProvider.php`) for compatibility
- [ ] Review migrations for Laravel 12 compatibility
- [ ] Run `composer update` and verify it completes successfully
- [ ] Run `composer install` and verify it completes successfully
- [ ] Run all tests and ensure they pass
- [ ] Update `README.md` to reflect Laravel 12 compatibility
- [ ] Update `agents/.agent-config.json` metadata if applicable
- [ ] Create release tag `2.0.0` in Git
- [ ] Push tag to remote repository
- [ ] Verify Packagist auto-updates or manually trigger update at https://packagist.org/packages/threeleaf/biblioteca

## Acceptance Criteria

- [ ] Package version number updated to `2.0.0` in `composer.json`
- [ ] `composer.json` requires Laravel 12 packages (`illuminate/database: ^12.0`, `illuminate/support: ^12.0`)
- [ ] All dependencies updated to Laravel 12 compatible versions
- [ ] `composer update` runs successfully without errors
- [ ] `composer install` runs successfully without errors
- [ ] All existing tests pass with Laravel 12
- [ ] Documentation updated to reflect Laravel 12 compatibility
- [ ] Service provider and migrations reviewed for compatibility
- [ ] Release tag `2.0.0` created and pushed to repository
- [ ] Packagist updated with version 2.0.0 (https://packagist.org/packages/threeleaf/biblioteca)

## Documentation

- **Architecture**: (from `agents/.agent-config.json` → `standards.architecture`)
- **Security**: (from `agents/.agent-config.json` → `standards.security`)
- **Testing**: (from `agents/.agent-config.json` → `standards.testing`)
- **Laravel 12 Upgrade Guide**: https://laravel.com/docs/12.x/upgrade
- **Blocking Issue**: [ThreeLeaf.com #100](https://github.com/ThreeLeaf-com/ThreeLeaf.com/issues/100)

## Notes

### Technical Notes

**Key Files to Review/Update**:
- `composer.json` - Dependency versions and package version (update to 2.0.0)
- `src/Providers/BibliotecaServiceProvider.php` - Service provider compatibility
- `database/migrations/` - Migration compatibility
- `tests/` - Test compatibility with Laravel 12
- `README.md` - Documentation updates
- `agents/.agent-config.json` - Metadata updates (if applicable)

**Release Process**:
- Create Git tag `2.0.0` after all changes are complete and tested
- Push tag to remote repository
- Verify Packagist updates (https://packagist.org/packages/threeleaf/biblioteca) - may auto-update via webhook or require manual trigger

**Dependencies to Update**:
- `illuminate/database: ^v10.0` → `^12.0`
- `illuminate/support: ^10.0` → `^12.0`
- `orchestra/testbench: ^8.0` → Check for Laravel 12 compatible version
- `phpunit/phpunit: ^10.0` → Check for Laravel 12 compatible version
- `darkaonline/l5-swagger: ^8.6` → Check for Laravel 12 compatible version

**Testing Plan**:
- Run `composer update` to verify dependency resolution
- Run `composer install` to verify clean installation
- Run full test suite: `./vendor/bin/pest` (or equivalent test command)
- Verify all tests pass with Laravel 12

**Potential Breaking Changes**:
1. Laravel 12 may introduce breaking changes from Laravel 10
2. Service provider structure may need updates
3. Migration syntax may have changed
4. Test framework dependencies may need updates

### Open Questions

- [ ] What is the Laravel 12 compatible version of `orchestra/testbench`?
- [ ] What is the Laravel 12 compatible version of `darkaonline/l5-swagger`?
- [ ] Are there any Laravel 12 breaking changes that affect Biblioteca's code?
- [ ] Does the service provider need any updates for Laravel 12?
- [ ] Does Packagist auto-update from GitHub webhooks, or does it need manual triggering?

### Risks & Mitigation

**Risks**:
- Breaking changes in Laravel 12 may require code updates
- Test framework dependencies may need significant updates
- Third-party packages may not have Laravel 12 compatible versions yet

**Mitigation**:
- Review Laravel 12 upgrade guide thoroughly
- Test thoroughly with `composer update` and `composer install`
- Run full test suite after dependency updates
- Can revert version changes if critical issues found

