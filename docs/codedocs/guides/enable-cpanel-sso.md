---
title: "Enable cPanel SSO"
description: "Verify the provider capability checks and expose cPanel SSO and usage panels for supported services."
---

This guide covers the cPanel-specific path implemented by `products_reseller_server_CustomActions()`, `products_reseller_server_ServiceSingleSignOn()`, `products_reseller_server_ClientArea()`, `hooks.php`, and `templates/cpanel.tpl`.

<Steps>
<Step>
### Confirm the provider reports cPanel

The module only enables cPanel behavior when `GetServerName` returns `cpanel`.

```bash
curl -X POST "https://provider.example.com/modules/addons/products_reseller/api.php" \
  -H "Content-Type: application/json" \
  -d '{
    "api_key": "REPLACE_WITH_REAL_KEY",
    "action": "GetServerName",
    "params": {
      "serviceid": 1205,
      "server_id": 2
    }
  }'
```

Expected response:

```json
{
  "status": "success",
  "server_name": "cpanel"
}
```

</Step>
<Step>
### Provision or identify an active cPanel-backed service

The richer client-area path only runs for services whose WHMCS `domainstatus` is `Active`.

```text
WHMCS Service Status: Active
Provider GetServerName: cpanel
```

Once those conditions hold, the module will request `get_Bandwidth_Disk_Usage` and render `templates/cpanel.tpl`.

</Step>
<Step>
### Test single sign-on

From the WHMCS admin service page, use the custom action **Log in to cPanel**. The callback path is:

```text
products_reseller_server_CustomActions
-> products_reseller_server_ServiceSingleSignOn
-> ProductsReseller_Main::get_cpanel_sso
-> provider action CreateSSOSession
```

You can also test a deep link by app:

```php
<?php

$response = products_reseller_server_ServiceSingleSignOn([
    'serviceid' => 1205,
    'serverid' => 2,
    'app' => 'Email_Accounts',
]);
```

</Step>
<Step>
### Verify the client-area dashboard

Open the product details page in the client area and confirm:

```text
1. Package/Domain panel renders
2. Disk and bandwidth usage dials appear
3. Shortcut icons link to dosinglesignon=1 with an app parameter
4. Billing overview is still visible
```

If the admin login button is missing, `hooks.php` likely hid it because `GetServerName` did not return `cpanel`.

</Step>
</Steps>

## Practical Example

For a cPanel hosting account with a working SSO session response:

```text
Service ID: 1205
Server ID: 2
App: FileManager_Home
Expected result: redirectTo points to a time-limited cPanel session URL
```

## Edge-Case Example

For a non-cPanel service on the same module:

```text
GetServerName -> "plesk"
```

Expected behavior:

```text
1. No custom cPanel action is returned
2. Admin-side cPanel button is hidden by hooks.php
3. Client area falls back to the generic Service Information tab
```

<Callout type="warn">
The module assumes HTTP when rendering the "Visit Website" button in both the generic fallback and `cpanel.tpl`. If your service should always be opened over HTTPS, adjust that behavior in the source before relying on the generated links in production.
</Callout>
