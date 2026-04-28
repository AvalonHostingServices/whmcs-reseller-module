---
title: "Import Provider Catalog"
description: "Use the admin import UI to create or sync WHMCS products from the provider catalog with margin rules."
---

This guide walks through the modal injected by `hooks/prs_hooks.php` and the server-side processing in `ajax_functions.php`.

<Steps>
<Step>
### Open the import modal

Go to the WHMCS products page where the hook injects the button:

```text
System Settings > Products/Services
```

Click **Import/Sync Products**. The modal immediately calls:

```text
POST modules/servers/products_reseller_server/ajax_functions.php
action = getProductsForImport
```

The response should include provider products, mapping state, and pricing data.

</Step>
<Step>
### Apply global or per-product margins

Choose one of the two margin modes:

```text
Percentage (%): multiplies recurring prices and setup fees
Fixed Amount: adds a flat amount to recurring prices only
```

The live preview in the modal is rendered in JavaScript using the same cycle list the server uses later in `ajax_functions.php`: `monthly`, `quarterly`, `semiannually`, `annually`, `biennially`, and `triennially`.

</Step>
<Step>
### Import or sync selected products

Select the rows you want, then click **Import / Sync Selected**. The modal sends the selected provider rows, margin settings, and pricing data as JSON to `importSyncProducts`.

```text
Unmapped product -> create WHMCS product with localAPI AddProduct
Mapped product -> update tblproducts and tblpricing
```

If the active server belongs to a WHMCS server group, new imports inherit that `servergroupid`.

</Step>
<Step>
### Verify mappings and pricing

After the results toast appears, confirm:

```text
1. Product exists in WHMCS
2. Module is products_reseller_server
3. configoption1 equals provider product_id
4. Pricing matches the adjusted margin output
```

When a mapping save back to the provider fails, the module logs `Save_Product_Mapping_Warning` through `logModuleCall()` but still keeps the local product creation result visible in the modal.

</Step>
</Steps>

## Complete Example

A common use case is importing a small shared-hosting catalog with a 15% markup:

```text
Global Margin Type: Percentage (%)
Global Margin Value: 15
Selected Products: Starter Hosting, Business Hosting, Reseller Bronze
```

If the provider returns:

```text
Starter Hosting monthly = USD 5.00
Business Hosting monthly = USD 9.00
```

WHMCS receives:

```text
Starter Hosting monthly = USD 5.75
Business Hosting monthly = USD 10.35
```

## Failure Recovery Example

If one product fails because the provider exposes `EUR` but WHMCS only has `USD`, rerun with the currency aligned first:

```text
1. Add EUR in WHMCS currencies
2. Reopen Import/Sync Products
3. Reimport the failed product
```

The endpoint is designed to return partial success, so unaffected products do not need to be recreated.

<Callout type="warn">
Deleting a local imported product later triggers `Delete_Product_Mapping` through the `ProductDelete` hook. If you remove products manually during catalog cleanup, expect the provider-side mapping to be deleted as well.
</Callout>

Next: [Enable cPanel SSO](/docs/guides/enable-cpanel-sso) if the imported products are backed by cPanel services.
