# WHMCS Marketplace Listing

This file contains all the copy needed to submit **Products Reseller for WHMCS** to the
[WHMCS Marketplace](https://marketplace.whmcs.com/). Use each section below when filling
in the corresponding form field.

---

## Listing Fields

### Product Name

```
Products Reseller for WHMCS
```

### Tagline / Short Description

> 160 characters or fewer — used in search results and category listings.

```
Resell hosting, VPS, SSL, and email solutions under your brand inside WHMCS. Connect your Avalon Hosting Services reseller account and go live in minutes.
```

### Category

```
Server Modules > Provisioning
```

### Module Type

```
Server Module
```

### Version

```
1.0.0
```

### Author / Publisher

```
Avalon Hosting Services
```

### Website

```
https://avalon.hosting
```

### Support URL

```
https://github.com/AvalonHostingServices/whmcs-reseller-module/issues
```

### Documentation URL

```
https://github.com/AvalonHostingServices/whmcs-reseller-module
```

### License

```
Proprietary
```

---

## Long Description

**Products Reseller for WHMCS** is a native WHMCS server module that bridges your WHMCS
installation with the Avalon Hosting Services reseller API. Once installed, every standard
WHMCS service action — provisioning, suspension, reactivation, termination, package change,
and password reset — is handled automatically over a secure HTTPS API call. No manual
intervention is required.

---

#### What This Module Does

Resellers using Avalon Hosting Services can sell a wide range of products under their own
brand directly through WHMCS:

- **Web Hosting** — Shared hosting and cPanel accounts
- **VPS / VDS** — Virtual private and dedicated virtual servers
- **Dedicated Servers** — Bare-metal dedicated server plans
- **CMS Hosting** — Hosting plans optimised for WordPress and other CMS platforms
- **SSL Certificates** — Issue and manage SSL certificates for client domains
- **Email Solutions** — Titan Email, Open-Xchange (OX App Suite), and Google Workspace

When a client places an order, upgrades, or cancels, WHMCS sends the corresponding action
to the Avalon reseller API and the account is created, modified, or removed on the
upstream server in real time — with zero manual steps.

---

#### Key Features

**Automated Service Lifecycle Management**
- CreateAccount: provisions a new hosting account on order activation
- SuspendAccount: suspends the upstream account when WHMCS marks a service as suspended
- UnsuspendAccount: reactivates the upstream account when a service is reactivated
- TerminateAccount: fully removes the account on termination
- ChangePackage: updates the hosting plan when a client upgrades or downgrades
- ChangePassword: syncs password changes from WHMCS to the upstream account

**cPanel Single Sign-On (SSO)**
When the upstream server type is cPanel, an SSO link is automatically surfaced in the
WHMCS client area so clients can log in to cPanel with a single click — no credentials to
copy and paste.

**One-Click Product Import — Set Up in Minutes**
An **Import / Sync Products** button appears directly on the WHMCS Products/Services page.
Click it to instantly pull your entire Avalon reseller catalogue into WHMCS — no manual
product creation required. New products are flagged as **New** and previously synced ones
are marked as **Imported**, making it easy to track what has already been set up.

**Profit Margin Control at Import Time**
Before importing, set a **Global Profit Margin** (percentage or fixed amount) and apply it
to all products at once. The import modal shows the base price from Avalon and calculates
the final client-facing price with your margin applied — across all billing cycles
(monthly, quarterly, semi-annually, annually, biennially, triennially). Individual
products can be given their own margin before confirming the import.

**Secure Credential Handling**
The API key is stored using WHMCS encrypted credential storage and decrypted at runtime
via `localAPI('DecryptPassword')`. Credentials are never stored in plain text.

**WHMCS Module Logging**
All API requests and responses are passed through the native WHMCS module logging
system, accessible under Utilities > Logs > Module Log, making troubleshooting
straightforward without requiring server log access.

**Hook-Based Extension Points**
An included hook file (`hooks/prs_hooks.php`) is automatically registered in the WHMCS
hooks directory during module initialisation, providing additional admin UI labels and
visibility controls without modifying core WHMCS files.

---

#### System Requirements

| Requirement    | Minimum                                                            |
| -------------- | ------------------------------------------------------------------ |
| WHMCS          | 8.0 or newer                                                       |
| PHP            | 7.4 or newer                                                       |
| PHP Extensions | `curl`, `json`, `openssl`                                          |
| Connectivity   | Outbound HTTPS from WHMCS server to Avalon reseller API endpoint   |
| Permissions    | File write permission on `includes/hooks/` during first activation |
| Account        | Active reseller account with Avalon Hosting Services               |
| Credentials    | API endpoint URL and API key from your reseller settings           |

---

#### Installation — Three Steps

1. Download the latest release ZIP from the
   [GitHub Releases page](https://github.com/AvalonHostingServices/whmcs-reseller-module/releases).
2. Extract the ZIP into your WHMCS root directory. The module files will land at
   `modules/servers/products_reseller_server/`.
3. Go to WHMCS Admin > System Settings > Servers, create a new server, and select
   **Products Reseller for WHMCS** as the module type.

---

#### Server Configuration

When configuring the server in WHMCS, the credential field labels are adapted:

- **Username** field → enter your **API Endpoint URL**
  (e.g. `https://node1.avalon.hosting/reseller/modules/addons/products_reseller/api.php`)
- **Password** field → enter your **API Key** from your reseller settings page

---

#### Product Setup

1. Create or edit a WHMCS product.
2. Set the module to **Products Reseller for WHMCS**.
3. Select the upstream product to map to from the module configuration options.
4. Save and place a test order to verify.

---

#### Troubleshooting

- **Module not visible in WHMCS**: Confirm the directory is
  `modules/servers/products_reseller_server/` relative to your WHMCS root.
- **Connection test fails**: Check API endpoint URL, API key, and that outbound HTTPS is
  not blocked by a firewall.
- **Actions return errors**: Open WHMCS Utilities > Logs > Module Log for the full
  request/response trace.

---

#### Support and Documentation

- Full documentation: https://github.com/AvalonHostingServices/whmcs-reseller-module
- API reference: https://github.com/AvalonHostingServices/whmcs-reseller-module/blob/master/API.md
- Bug reports: https://github.com/AvalonHostingServices/whmcs-reseller-module/issues
- Email support: support@avalon.hosting

---

## Screenshots Checklist

Prepare the following screenshots before submitting:

1. **Server configuration screen** — WHMCS admin > Servers > Products Reseller for WHMCS,
   showing the Username (API Endpoint) and Password (API Key) fields.
2. **Product module settings** — Product edit > Module Settings tab, showing the module
   selected and the upstream product dropdown populated.
3. **Client area service view** — A provisioned service in the client area, ideally showing
   the cPanel SSO login button.
4. **Module Log sample** — WHMCS Utilities > Logs > Module Log showing a successful
   CreateAccount request/response (redact the API key).
5. **Import / Sync Products button** — The Products/Services page in WHMCS admin showing
   the "Import/Sync Products (By Products Reseller)" button in the toolbar.
6. **Import modal — product list** — The Import / Sync Products modal showing New and
   Imported product badges, base prices, and final prices across billing cycles.
7. **Import modal — Global Profit Margin** — The same modal with the Global Profit Margin
   control highlighted and a margin value applied, showing the recalculated final prices.

---

## Tags / Keywords

```
reseller, provisioning, server module, cPanel, hosting, VPS, dedicated server, SSL, email, Titan, Google Workspace, automation, avalon, whm, accounts, import
```

---

## Pricing

**Free** — no license fee. Requires an active Avalon Hosting Services reseller account.

---

## Submission Checklist

- [ ] WHMCS Marketplace account created at https://marketplace.whmcs.com/
- [ ] Module ZIP built from latest GitHub release tag (contains `modules/` folder only)
- [ ] Screenshots prepared (see list above)
- [ ] Support URL reachable and returns valid response
- [ ] Documentation URL reachable
- [ ] Long description proofread and HTML validated (if HTML version used)
- [ ] Tags entered
- [ ] Pricing model decided
- [ ] Module tested against WHMCS 8.x on PHP 7.4 and PHP 8.x before submission
