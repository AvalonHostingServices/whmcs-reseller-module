---
title: "Install and Activate"
description: "Install the module into WHMCS and activate the first reseller server entry correctly."
---

This guide covers the operational path from a release archive to an active WHMCS server record. It is based on `README.md`, `INSTALL.md`, `install.sh`, `install.bat`, and the bootstrap behavior in `hooks.php`.

<Steps>
<Step>
### Download and extract the release package

The release archive intentionally contains only the `modules/servers/products_reseller_server/` tree so it can be extracted directly into a WHMCS root.

```bash
curl -L -o whmcs-reseller-module.zip \
  https://github.com/AvalonHostingServices/whmcs-reseller-module/releases/latest/download/whmcs-reseller-module.zip
unzip whmcs-reseller-module.zip -d /var/www/whmcs
```

If you prefer the bundled installer on Linux or macOS:

```bash
chmod +x install.sh
./install.sh -p /var/www/whmcs -f whmcs-reseller-module.zip
```

</Step>
<Step>
### Verify the installed path and permissions

The module will not appear in WHMCS unless the directory layout is exact.

```bash
find /var/www/whmcs/modules/servers/products_reseller_server -maxdepth 2 -type f | sort
chmod -R 755 /var/www/whmcs/modules/servers/products_reseller_server
```

You should see files such as `products_reseller_server.php`, `pr_server_classes.php`, `ajax_functions.php`, and `templates/cpanel.tpl`.

</Step>
<Step>
### Create the WHMCS server entry

In WHMCS admin:

```text
System Settings > Servers > Add New Server
```

Choose **Products Reseller for WHMCS** as the server type, then fill the fields like this:

```text
Name: Any internal label
Hostname: provider.example.com
Username: optional path prefix under the provider install
Password: reseller API key
```

The module rewrites the username and password labels in admin UI to **API Endpoint** and **API Key** through the `AdminAreaHeadOutput` hook in `hooks.php`.

</Step>
<Step>
### Save, test, and confirm hook bootstrap

After saving, use WHMCS's connection test for the server entry.

```text
Expected result: success = true when Get_Products returns a non-empty data array
```

Then verify that the copied admin hook exists:

```bash
ls -l /var/www/whmcs/includes/hooks/prs_hooks.php
```

That file is copied by `whmp_prs_copyFileToHooks()` so the product import UI can run from the global hooks directory.

</Step>
</Steps>

## Runnable Troubleshooting Example

If the connection test fails, reproduce the upstream envelope manually with the same endpoint shape used by `ProductsReseller_Main::send_request_to_api()`:

```bash
curl -X POST "https://provider.example.com/modules/addons/products_reseller/api.php" \
  -H "Content-Type: application/json" \
  -d '{
    "api_key": "REPLACE_WITH_REAL_KEY",
    "action": "Get_Products",
    "params": {
      "server_id": 1
    }
  }'
```

Expected response:

```json
{
  "status": "success",
  "data": [
    {
      "product_id": 45,
      "product_name": "Starter Hosting"
    }
  ],
  "message": "Request completed successfully"
}
```

<Callout type="warn">
The module expects write access to `includes/hooks` during initialization. If that directory is not writable, the server module may still load, but the import/sync UI from `prs_hooks.php` will never be installed.
</Callout>

Next: [Configure Products](/docs/guides/configure-products) once the server entry is active.
