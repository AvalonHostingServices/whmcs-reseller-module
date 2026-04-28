---
title: "Admin Hooks"
description: "Reference for the hook bootstrap logic and admin behaviors implemented in hooks.php and prs_hooks.php."
---

Source files:

- `modules/servers/products_reseller_server/hooks.php`
- `modules/servers/products_reseller_server/hooks/prs_hooks.php`

These files extend the module beyond the standard WHMCS server callbacks. They are not discovered as provisioning functions, but they are still part of the module's effective public surface because they alter admin behavior and enable product import.

## Runtime Paths

```php
modules/servers/products_reseller_server/hooks.php
includes/hooks/prs_hooks.php
```

`prs_hooks.php` is copied into `includes/hooks` by `whmp_prs_copyFileToHooks()`.

## Public Bootstrap Function

```php
function whmp_prs_copyFileToHooks(): array
```

Defined in `hooks.php`.

### Parameters

This function takes no arguments.

### Return Type

```php
array{
  success: bool,
  message: string
}
```

### Behavior

| Step | Description |
|------|-------------|
| 1 | Verifies `hooks/prs_hooks.php` exists and is readable. |
| 2 | Ensures `includes/hooks/` exists, creating it if needed. |
| 3 | Verifies the destination is writable. |
| 4 | Refuses to copy if the destination file already exists. |
| 5 | Removes the destination copy when no active `products_reseller_server` server exists. |
| 6 | Copies `prs_hooks.php` into `includes/hooks/` when all checks pass. |

### Example

```php
<?php

$result = whmp_prs_copyFileToHooks();
echo $result['message'];
```

## Hook: `AdminAreaHeadOutput` in `hooks.php`

This hook has two behaviors.

### 1. Server configuration relabeling

When the admin is on `configservers?action=manage`, the hook injects jQuery that relabels:

- Username -> API Endpoint
- Password -> API Key

This matches how `ProductsReseller_Main::send_request_to_api()` interprets those fields.

### 2. Conditional cPanel button hiding

When the admin is on `clientsservices`, the hook resolves a service ID, checks whether the server type is `products_reseller_server`, calls `GetServerName`, and hides the cPanel login button unless the result is exactly `cpanel`.

## Hook: `AdminAreaHeadOutput` in `prs_hooks.php`

This hook targets `configproducts` when no product ID is present, which effectively means the product list or creation context. It injects:

- modal styles
- button creation logic
- AJAX calls to `ajax_functions.php`
- per-product and global margin controls
- result and progress UI

### Example Request Flow

```text
Admin clicks Import/Sync Products
-> prs_hooks.php JavaScript opens modal
-> AJAX POST to ajax_functions.php action=getProductsForImport
-> admin selects rows and margins
-> AJAX POST to ajax_functions.php action=importSyncProducts
```

## Hook: `ProductDelete` in `prs_hooks.php`

When a local WHMCS product is deleted, the hook sends:

```php
[
  'action' => 'Delete_Product_Mapping',
  'server_id' => $server_id,
  'reseller_product_id' => $productId,
]
```

The request is sent through `ProductsReseller_Main::send_request_to_api()`.

### Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `pid` | `int` | — | Deleted local WHMCS product ID. |

### Example

```php
<?php

add_hook('ProductDelete', 1, function ($vars) {
    // The module internally sends Delete_Product_Mapping for $vars['pid']
});
```

## Common Patterns

### Reinstall the copied hook after environment migration

If `includes/hooks/prs_hooks.php` is missing after a deploy or restore, calling the bootstrap function from the main module load path will recreate it as long as the destination is writable and an active server exists.

### Use provider capability checks to drive admin UI

Both hook files avoid hard-coding assumptions. The label hook is tied to the selected server type, and the cPanel button visibility is tied to live `GetServerName` responses.

Related pages: [AJAX Endpoint](/docs/api-reference/ajax-endpoint), [Product Import and Sync](/docs/product-import-sync).
