# Agent Instructions for WHMCS Reseller Module

## Project Overview

This is a **WHMCS server module** that integrates Avalon Hosting Services' reseller API with WHMCS. It enables automatic account provisioning and management (create, suspend, unsuspend, terminate, change package/password) directly from the WHMCS admin panel.

- **Language:** PHP 8.1+ (tested PHP 8.0–8.4)
- **Framework:** WHMCS 8.0+
- **License:** GPLv3
- **Status:** Production module for hosting resellers

## Architecture Overview

### Module Entry Points

```
WHMCS Admin Panel (browser)
    ↓
modules/servers/products_reseller_server/products_reseller_server.php (main module)
    ↓ (invokes lifecycle hooks)
pr_server_classes.php::ProductsReseller_Main (core API logic)
    ↓ (cURL POST JSON)
Reseller API Endpoint (https://{hostname}/{restpart}/modules/addons/products_reseller/api.php)
    ↓ (returns JSON response)
Result logged to WHMCS Module Log
    ↓ (on success)
prs_writeBackServiceData() updates tblhosting custom fields
```

### Core Components

| File                             | Purpose                       | Key Class/Functions                                                                                   |
| -------------------------------- | ----------------------------- | ----------------------------------------------------------------------------------------------------- |
| **products_reseller_server.php** | WHMCS module entry point      | `_MetaData()`, `_TestConnection()`, `_ConfigOptions()`, `_CreateAccount()`, `_SuspendAccount()`, etc. |
| **pr_server_classes.php**        | API communication engine      | `ProductsReseller_Main::send_request_to_api()` (all HTTP calls funnel through here)                   |
| **hooks.php**                    | WHMCS lifecycle customization | `prs_hooks()` (admin UI label remapping)                                                              |
| **ajax_functions.php**           | Admin UI AJAX handlers        | Product import/sync workflow                                                                          |
| **hooks/prs_hooks.php**          | Hook definitions              | Hook registration and event handling                                                                  |
| **templates/cpanel.tpl**         | cPanel-specific template      | Dynamic field rendering                                                                               |

### WHMCS Integration Points

The module implements these WHMCS function signatures (required by WHMCS):

- `_MetaData()` — Module display name, field labels, SSO label
- `_TestConnection()` — Validates API credentials against live endpoint
- `_ConfigOptions()` — Server config form UI (product dropdown, visibility toggles)
- `_CreateAccount()`, `_SuspendAccount()`, `_UnsuspendAccount()`, `_TerminateAccount()` — Account lifecycle actions
- `_ChangePackage()`, `_ChangePassword()` — Account modifications
- `_GetServiceSSO()` — cPanel SSO login link generation

All actions call `ProductsReseller_Main::send_request_to_api()` and return WHMCS-standard response format: `['status' => 'success'|'error', 'message' => '...']`

## Build & Deployment

### Local Development

**No build step required** — this is a PHP module, no compilation.

```bash
# Lint all PHP files before committing (required in PR)
php -l modules/servers/products_reseller_server/*.php
php -l modules/servers/products_reseller_server/hooks/*.php

# Test locally: install WHMCS 8.0+, extract module to modules/servers/products_reseller_server/
# Then create server in WHMCS Admin and test connection
```

### CI/CD Pipeline

- **Lint:** `php -l` on all `.php` files (`.github/workflows/lint.yml`)
- **Security:** Trivy vulnerability scan
- **Compatibility:** Tests PHP 8.0–8.4
- **Release:** Tag `v*` triggers auto-packaging → ZIP/TAR.GZ + SHA256 checksums

See [`.github/workflows/release.yml`](.github/workflows/release.yml) for release automation.

## Code Conventions & Patterns

### Naming

- **Module functions:** `products_reseller_server_{FunctionName}` (WHMCS convention, snake_case)
- **Helper functions:** `prs_{purpose}` (e.g., `prs_writeBackServiceData`, `prs_getServiceCustomFields`, `prs_getServiceConfigOptions`)
- **Classes:** `ProductsReseller_Main` (PascalCase with underscore)
- **Internal helpers:** No strict access modifiers; comments indicate intent

### Code Structure

- **Guard all files:** Start with `if (!defined("WHMCS")) die("This file cannot be accessed directly");`
- **Database access:** Use `Capsule::table()` (WHMCS ORM), never raw SQL
- **API calls:** All HTTP requests go through `ProductsReseller_Main::send_request_to_api($data)` (single entry point)
  - Request format: `['action' => '...', 'data' => [...], ...]`
  - Response format: `['status' => 'success'|'error', 'message' => '...', 'data' => [...]]`
- **Error handling:** Return error arrays on failure; use `logModuleCall()` for WHMCS module log
- **Indentation:** 4 spaces (PSR-12)

### Example: Adding a New Action

1. Add action function to `products_reseller_server.php` (e.g., `_DoSomething()`)
2. Inside, instantiate `ProductsReseller_Main` and call `send_request_to_api()`
3. Helper functions like `prs_getServiceCustomFields()` and `prs_writeBackServiceData()` retrieve/update WHMCS service data
4. Log via `logModuleCall()` to WHMCS module log
5. Return standard WHMCS response: `['status' => 'success'|'error', ...]`

## Key Development Workflows

### Debugging

- **Module actions fail?** Check **Utilities > Module Log** in WHMCS Admin (all module calls logged there)
- **Connection test fails?** Verify API endpoint URL format (protocol, no trailing slashes) and check outbound HTTPS access
- **Custom fields not displayed?** Verify field mapping in WHMCS product configuration

### Testing

1. Set up WHMCS 8.0+ with PHP 8.1+ locally
2. Extract module to `modules/servers/products_reseller_server/`
3. Create server in WHMCS Admin, select "Products Reseller for WHMCS"
4. Configure: Username = API endpoint, Password = API key
5. Test connection button in admin
6. Verify logs: **Utilities > Logs > Module Log**

### Making Changes

1. Edit `.php` files in `modules/servers/products_reseller_server/`
2. Run lint: `php -l <file>`
3. Commit with GPG signature: `git commit -S -m "..."`
4. Create PR against `master` branch
5. Ensure all tests pass in GitHub Actions

## Important Conventions

### Required: GPG-Signed Commits

All commits **must be signed** with GPG (enforced in [CONTRIBUTING.md](CONTRIBUTING.md)):

```bash
# Configure once
git config --global commit.gpgsign true

# Commits are automatically signed
git commit -m "Your message"

# Or explicitly with -S flag
git commit -S -m "Your message"
```

Without GPG signing, commits will be rejected by CI.

### Common Pitfalls

| Issue                             | Root Cause                                                | Solution                                                 |
| --------------------------------- | --------------------------------------------------------- | -------------------------------------------------------- |
| Module type not visible in WHMCS  | Files not in correct path                                 | Verify: `modules/servers/products_reseller_server/`      |
| API connection fails              | Invalid endpoint/key or firewall blocks                   | Verify credentials + outbound HTTPS access from server   |
| Action returns error              | API call failed                                           | Check **Utilities > Logs > Module Log** for details      |
| Incorrect custom fields displayed | Field mapping not configured in WHMCS product             | Add custom fields to product in WHMCS admin              |
| SSO redirect fails                | Upstream server not cPanel or SSO not enabled             | Confirm upstream is cPanel; enable SSO in module options |
| PHP version compatibility         | Module tested on PHP 8.0–8.4 but with different behaviors | Test against target PHP version in dev environment       |

## Documentation & API Reference

- [README.md](README.md) — Feature overview, quick start
- [API.md](API.md) — Detailed API contract (request/response envelope, all actions, parameters)
- [CONTRIBUTING.md](CONTRIBUTING.md) — Contribution guidelines, GPG requirement, PR workflow
- [INSTALL.md](INSTALL.md) — End-user installation instructions
- [docs/codedocs/](docs/codedocs/) — Architecture deep-dives, configuration guides
- [CHANGELOG.md](CHANGELOG.md) — Version history and breaking changes
- [MARKETPLACE_LISTING.md](MARKETPLACE_LISTING.md) — WHMCS marketplace metadata

## Quick-Start Checklist for New Tasks

- [ ] Read [API.md](API.md) to understand the API request/response contract
- [ ] Study [pr_server_classes.php](modules/servers/products_reseller_server/pr_server_classes.php) (core API logic)
- [ ] Verify `git config --global commit.gpgsign true` for GPG signing
- [ ] Before committing: run `php -l` lint and ensure no syntax errors
- [ ] Test locally on WHMCS 8.0+ with PHP 8.1+
- [ ] Check Module Log (**Utilities > Logs > Module Log**) for errors
- [ ] Verify PR passes GitHub Actions (lint, security scan, PHP compatibility)
