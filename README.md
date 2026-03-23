# WHMCS Reseller Module

WHMCS server module for Avalon Hosting Services resellers.

This module connects WHMCS to the Avalon reseller API so services can be provisioned and managed from normal WHMCS service actions.

![Version](https://img.shields.io/badge/version-1.0.0-blue)
![WHMCS](https://img.shields.io/badge/WHMCS-8.0+-blue)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-blue)

## Version

- Current stable version: **1.0.0**
- Release channel: GitHub Releases

## Key Capabilities

- Automatic account provisioning
- Suspend and unsuspend services
- Terminate services
- Change package
- Change password
- cPanel SSO support (when upstream server type is cPanel)
- Product import and mapping helpers in WHMCS admin

## Requirements

- WHMCS 8.0 or newer
- PHP 7.4 or newer (must be compatible with your installed WHMCS 8 release)
- PHP extensions: `curl`, `json`, `openssl`
- Outbound HTTPS connectivity from the WHMCS server to the reseller API endpoint
- Active reseller access from Avalon Hosting Services
- API endpoint and API key from your reseller settings
- File write permission for WHMCS hooks directory (`includes/hooks`) during module initialization

## What Is In A Release Package

Each release package contains only:

```text
modules/
   servers/
      products_reseller_server/
```

This is intentional so resellers can extract directly into WHMCS root.

## Installation (Recommended)

1. Download the latest file from [Releases](https://github.com/AvalonHostingServices/whmcs-reseller-module/releases).
2. Upload the downloaded ZIP to your server.
3. Extract the ZIP in your WHMCS root so folder path becomes:
    `modules/servers/products_reseller_server/`
4. Open WHMCS admin.
5. Go to **System Settings > Servers**.
6. Create a new server and select **Products Reseller for WHMCS**.
7. Save server settings and assign it to your products.

## Server Configuration

When server type is **Products Reseller for WHMCS**, labels are adapted in WHMCS admin:

- **Username field** is used as **API Endpoint**
- **Password field** is used as **API Key**

Use credentials from your reseller settings page.

## Product Setup In WHMCS

1. Create or edit a WHMCS product.
2. Set module type to **Products Reseller for WHMCS**.
3. Choose the mapped upstream product from module config options.
4. Save and test with a new order.

## API Documentation

Detailed API action reference, request format, and examples are available in [API.md](API.md).

## Troubleshooting

- **Module type not visible in WHMCS**:
   Confirm files are extracted to `modules/servers/products_reseller_server/`.
- **Connection test fails**:
   Verify API endpoint, API key, and outbound HTTPS access.
- **Action returns error text in WHMCS**:
   Check **Utilities > Logs > Module Log** in WHMCS.

## Documentation

- Installation and release process: [INSTALL.md](INSTALL.md)
- Release flow and tagging: [RELEASE_PROCESS.md](RELEASE_PROCESS.md)
- API action reference: [API.md](API.md)
- Changes by version: [CHANGELOG.md](CHANGELOG.md)

## Security

Do not report security issues in public issues.

- Report privately: [security@avalon.hosting](mailto:security@avalon.hosting)
- Security policy: [SECURITY.md](SECURITY.md)

## Support

- Email: support@avalon.hosting
- Issues: [GitHub Issues](https://github.com/AvalonHostingServices/whmcs-reseller-module/issues)
- Discussions: [GitHub Discussions](https://github.com/AvalonHostingServices/whmcs-reseller-module/discussions)

## Contributing

Contributions are welcome. Please review:

- [CONTRIBUTING.md](CONTRIBUTING.md)
- [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md)

All commits must be signed.

## License

See [LICENSE.md](LICENSE.md).

## Maintained By

Avalon Hosting Services

- Website: https://avalon.hosting/
- GitHub: https://github.com/AvalonHostingServices
