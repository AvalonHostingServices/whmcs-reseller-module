# WHMCS Reseller Module - Version 1.4.0

A professional WHMCS server module for reselling hosting products. This directory contains the v1.4.0 release.

## Directory Structure

```
modules/
└── servers/
    └── products_reseller_server/
        ├── ajax_functions.php       # AJAX handlers for admin interface
        ├── hooks.php                # WHMCS hooks and events
        ├── pr_server_classes.php    # Core module classes
        ├── products_reseller_server.php  # Main module file
        ├── whmcs.json              # Module metadata
        ├── hooks/
        │   └── prs_hooks.php        # Additional hooks
        ├── images/
        │   └── cpanel/              # cPanel-related images
        └── templates/
            └── cpanel.tpl           # cPanel template
```

## Installation

1. Extract the `modules` folder to your WHMCS root directory
2. Log in to WHMCS Admin
3. Navigate to **System Settings** > **Servers**
4. Create a new server and select "Products Reseller Server"
5. Configure your credentials and save

## Quick Start

See [README.md](../README.md) for complete documentation and setup instructions.

## Features in v1.4.0

- ✅ Automated account provisioning
- ✅ Account suspension/unsuspension
- ✅ Account termination
- ✅ Real-time synchronization
- ✅ Error logging and debugging
- ✅ Admin dashboard
- ✅ Secure API integration
- ✅ Product import/sync workflow
- ✅ Advanced pricing management
- ✅ Bulk operations support

## API Actions

### Product Management

**Delete_Product_Mapping**
- Called automatically when a product is deleted from WHMCS
- Cleans up mappings between WHMCS products and reseller products
- Parameters: `server_id`, `reseller_product_id`
- Response: Status confirmation

### Account Lifecycle

The module handles all standard account operations:
- CreateAccount
- SuspendAccount
- UnsuspendAccount
- TerminateAccount
- ChangePackage
- ChangePassword
- CreateSSOSession

See [../API.md](../API.md) for complete API reference.

## Version

**1.4.0** - Enhanced features and admin workflow (June 2026)

For more information, see [CHANGELOG.md](../CHANGELOG.md)
