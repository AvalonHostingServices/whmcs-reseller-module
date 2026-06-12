# Changelog

All notable changes to the WHMCS Reseller Module will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-03-24

### Added
- Initial stable release of WHMCS Reseller Module
- Core provisioning functionality for hosting accounts
- cPanel/WHM integration support
- Account management features (suspend, unsuspend, terminate)
- Comprehensive admin dashboard
- Error logging and debugging tools
- API support for third-party integrations
- AJAX-based account management functions
- Hook system for extensibility
- Automated account status synchronization
- Email notifications for account events
- Full documentation and API reference

### Features
- Seamless WHMCS integration
- Real-time account provisioning
- Multi-server support
- Secure credential management
- Transaction logging
- Template-based configuration

### Security
- Secure API authentication
- Input validation and sanitization
- Protection against common vulnerabilities
- Session management and CSRF protection

### Documentation
- Complete installation guide
- Configuration tutorials
- API documentation
- Troubleshooting guide
- FAQ section

---

## [1.4.0] - 2026-06-12

### Added
- Product import/sync workflow with advanced pricing management
- Bulk operations support for product management
- Enhanced admin dashboard for product mapping and configuration
- Delete product mapping API action for automatic cleanup
- Comprehensive agent instructions (AGENTS.md) for AI-assisted development
- Modal-based UI for product import/sync workflow
- Improved pricing calculation with margin support

### Enhanced
- Refined prs_hooks.php with improved modal styling and AJAX handlers
- Updated module metadata for v1.4.0 release
- Enhanced documentation and API reference
- Better error handling and logging throughout the module
- Improved WHMCS ORM integration

### Fixed
- Product deletion now properly cleans up associated mappings
- Better handling of custom field updates
- Improved API request error messages

---

## Unreleased

### Planned for v1.5.0
- Bulk account operations
- Advanced reporting and analytics
- Enhanced webhook support
- Additional server type integrations
- Performance optimizations
- Improved caching mechanisms

### Planned for v2.0.0
- Multi-language support
- Custom field mapping
- Advanced automation features
- Mobile-friendly admin interface

---

For installation instructions, see [README.md](README.md)
For security vulnerabilities, see [SECURITY.md](SECURITY.md)
