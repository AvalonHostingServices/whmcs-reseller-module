# WHMCS Reseller Module

A comprehensive WHMCS server module for reselling Avalon Hosting Services' hosting products. This module integrates seamlessly with WHMCS to enable resellers to manage hosting provisioning, suspension, and termination.

![Version](https://img.shields.io/badge/version-1.0.0-blue)
![License](https://img.shields.io/badge/license-GPLv3-green)
![WHMCS](https://img.shields.io/badge/WHMCS-8.0+-blue)

## Features

- 🚀 **Automated Provisioning** - Seamlessly provision hosting accounts
- 🛑 **Account Management** - Suspend and terminate accounts
- 🔄 **Real-time Synchronization** - Keep account data synchronized
- 🔐 **Secure Integration** - Enterprise-grade security
- 📊 **Admin Dashboard** - Comprehensive management interface
- 🌐 **Multi-server Support** - Manage multiple reseller accounts

## Requirements

- **WHMCS Version:** 8.0 or higher
- **PHP Version:** 7.4 or higher
- **cPanel/WHM** (for cPanel integration)

## Installation

1. Download the latest release from the [Releases](https://github.com/AvalonHostingServices/whmcs-reseller-module/releases) page
2. Extract the module files to your WHMCS `modules/servers/` directory
3. Log in to your WHMCS admin area
4. Navigate to **System Settings** > **Products/Services** > **Servers**
5. Create a new server and select "Products Reseller" as the server type
6. Configure your reseller credentials and save

## Configuration

### Server Setup

1. Go to **System Settings** > **Servers**
2. Create a new server with the following details:
   - **Name:** Your reseller account name
   - **Type:** Products Reseller Server
   - **Hostname:** Your reseller hostname
   - **Port:** Your API port
   - **Username/Password:** Your credentials

### Creating Reseller Products

1. Create a new product/service in WHMCS
2. Set the server type to "Products Reseller"
3. Assign your configured reseller server
4. Configure package mappings and billing settings

## Usage

### For Resellers

Once installed and configured, resellers can:
- Automatically provision hosting accounts through WHMCS orders
- Receive automated suspension/unsuspension notices
- View account status and management options

### For Administrators

Administrators have access to:
- Server management and configuration
- Account status monitoring
- Error logs and debugging tools
- Bulk account operations (coming in v1.1.0)

## Documentation

Full documentation is available at: [https://docs.avalon.hosting/](https://docs.avalon.hosting/)

Key guides:
- [Installation Guide](https://docs.avalon.hosting/whmcs-reseller-module/installation)
- [Configuration Guide](https://docs.avalon.hosting/whmcs-reseller-module/configuration)
- [API Reference](https://docs.avalon.hosting/whmcs-reseller-module/api)
- [Troubleshooting](https://docs.avalon.hosting/whmcs-reseller-module/troubleshooting)

## Support

### Getting Help

- 📧 **Email:** support@avalon.hosting
- 📋 **Issue Tracker:** [GitHub Issues](https://github.com/AvalonHostingServices/whmcs-reseller-module/issues)
- 💬 **Discussions:** [GitHub Discussions](https://github.com/AvalonHostingServices/whmcs-reseller-module/discussions)

### Security

Please report security vulnerabilities privately to [security@avalon.hosting](mailto:security@avalon.hosting) instead of using the public issue tracker. See [SECURITY.md](SECURITY.md) for more details.

## Contributing

We welcome contributions from the community! Before contributing, please review:

- [CONTRIBUTING.md](CONTRIBUTING.md) - Contribution guidelines
- [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md) - Community code of conduct

## Changelog

All notable changes are documented in [CHANGELOG.md](CHANGELOG.md).

### Version 1.0.0 - Initial Release

Initial stable release featuring:
- Core provisioning functionality
- cPanel integration
- Administrative dashboard
- Comprehensive error handling
- Full API support

## License

This project is licensed under the GNU General Public License v3.0 - see the [LICENSE.md](LICENSE.md) file for details.

## Credits

Developed by **Avalon Hosting Services**

- Website: [https://avalon.hosting/](https://avalon.hosting/)
- GitHub: [@AvalonHostingServices](https://github.com/AvalonHostingServices)

## Community

- ⭐ Star our repository if you find it helpful!
- 🐛 Report bugs and suggest features via [GitHub Issues](https://github.com/AvalonHostingServices/whmcs-reseller-module/issues)
- 💡 Join discussions in our [community forum](https://github.com/AvalonHostingServices/whmcs-reseller-module/discussions)

---

**Version 1.0.0** | Last updated: March 2026
