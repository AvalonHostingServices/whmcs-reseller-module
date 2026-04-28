---
title: "Configure Products"
description: "Attach WHMCS products to the reseller module and choose the correct provider product mapping."
---

This guide covers the first usable WHMCS product after the server is active. It relies on `products_reseller_server_ConfigOptions()` to populate the provider product dropdown and on the config flags that control non-cPanel client-area display.

<Steps>
<Step>
### Create or edit a WHMCS product

Open the product editor and switch the module to the reseller server module.

```text
System Settings > Products/Services > Create a New Product
Module Settings > Module Name = Products Reseller for WHMCS
```

Once the module is selected, WHMCS calls `products_reseller_server_ConfigOptions($params)`, which requests `Get_Products` from the upstream API and populates `configoption1`.

</Step>
<Step>
### Choose the assigned provider product

In module settings, set:

```text
Assigned Products: provider product ID from Get_Products
Show Server Name: on or off
Show Host Name: on or off
Show Domain: on or off
Show IP Address: on or off
Show Username: on or off
Show Password: on or off
```

These flags become `configoption2` through `configoption7` in `products_reseller_server_ClientArea($params)` and only affect the non-cPanel fallback view.

</Step>
<Step>
### Attach the product to the reseller server or server group

Assign the server or a server group so the resulting service has a valid `serverid`.

```text
Product > Module Settings > Server Group = your reseller server group
```

This matters because provisioning callbacks read `$params['serverid']` and pass it through to the provider API as `server_id`.

</Step>
<Step>
### Place a test order and verify provisioning

Create or accept a test order for the product, then run module create in WHMCS admin.

```text
Expected success path:
CreateAccount -> ProductsReseller_Main::create_account -> provider action CreateAccount -> return "success"
```

If the service stays pending, check **Utilities > Logs > Module Log** for the request logged by `logModuleCall()`.

</Step>
</Steps>

## Complete Example Mapping

Use this as a checklist for a normal hosting plan:

```text
WHMCS Product Name: Starter Shared Hosting
Module Name: Products Reseller for WHMCS
Assigned Products: 45
Show Server Name: on
Show Host Name: on
Show Domain: on
Show IP Address: off
Show Username: on
Show Password: off
```

That setup produces a generic service-information client-area view unless the provider later reports `server_name = cpanel`, in which case the module will automatically switch to `cpanel.tpl`.

## Edge-Case Example

If you want to hide most operational details for a non-cPanel product:

```text
Assigned Products: 62
Show Server Name: off
Show Host Name: off
Show Domain: on
Show IP Address: off
Show Username: off
Show Password: off
```

This is useful for products where exposing the decrypted WHMCS service password would be inappropriate or where the server hostname is not meaningful to the customer.

<Callout type="warn">
Because `ConfigOptions()` queries the first active reseller server in `tblservers`, the product dropdown can be misleading if you keep multiple active `products_reseller_server` server records. Validate that the fetched provider catalog matches the server you intend to assign before saving product mappings.
</Callout>

Next: [Import Provider Catalog](/docs/guides/import-provider-catalog) if you want WHMCS products to be created for you instead of configuring them one by one.
