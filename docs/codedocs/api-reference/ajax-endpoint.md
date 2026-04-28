---
title: "AJAX Endpoint"
description: "Reference for the admin-side import actions handled by ajax_functions.php."
---

Source file: `modules/servers/products_reseller_server/ajax_functions.php`

This endpoint is consumed by the import modal injected from `prs_hooks.php`. It is not a general public API for third parties, but it is a stable internal module endpoint with two action names that matter for admins and maintainers.

## Runtime Path

```php
modules/servers/products_reseller_server/ajax_functions.php
```

The file loads `../../../init.php` only when `$_POST['prs_doing_ajax']` is truthy.

## Endpoint Contract

### Request Envelope

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `prs_doing_ajax` | `bool` | — | Enables WHMCS bootstrap and action dispatch. |
| `action` | `string` | — | One of `getProductsForImport` or `importSyncProducts`. |
| `products` | `string` | `[]` | JSON-encoded product array for `importSyncProducts` only. |

## `action = getProductsForImport`

### Signature

```text
POST ajax_functions.php
prs_doing_ajax=true
action=getProductsForImport
```

### Behavior

1. Resolve the first active `products_reseller_server` server ID.
2. Return an error if no active server exists.
3. Call `ProductsReseller_Main::send_request_to_api()` with `Get_Products_For_Import`.
4. Echo the provider response as JSON.

### Response Type

```json
{
  "status": "success",
  "data": [
    {
      "product_id": 45,
      "product_name": "Starter Hosting",
      "is_mapped": false,
      "pricing": []
    }
  ]
}
```

### Example

```bash
curl -X POST "https://whmcs.example.com/modules/servers/products_reseller_server/ajax_functions.php" \
  -d "prs_doing_ajax=1" \
  -d "action=getProductsForImport"
```

## `action = importSyncProducts`

### Signature

```text
POST ajax_functions.php
prs_doing_ajax=true
action=importSyncProducts
products=[...JSON array...]
```

### Product Payload Fields

| Field | Type | Default | Description |
|------|------|---------|-------------|
| `product_id` | `int` | — | Provider product ID. |
| `product_name` | `string` | — | Provider product name. |
| `product_type` | `string` | `other` | WHMCS product type candidate. |
| `product_group_name` | `string` | `General` | Local WHMCS product group name. |
| `pgroup_slug` | `string` | `''` | Group slug for new group creation. |
| `pgroup_headline` | `string` | `''` | Group headline for new group creation. |
| `pgroup_tagline` | `string` | `''` | Group tagline for new group creation. |
| `product_description` | `string` | `''` | Local product description. |
| `product_shortdesc` | `string` | `''` | Short description passed to AddProduct. |
| `product_tagline` | `string` | `''` | Product tagline passed to AddProduct. |
| `paytype` | `string` | `recurring` | WHMCS pay type. |
| `is_mapped` | `bool` | `false` | Whether the provider already tracks a local product. |
| `reseller_product_id` | `int` | `0` | Local WHMCS product ID for sync operations. |
| `margin_type` | `string` | `percentage` | Margin mode. |
| `margin_value` | `float` | `0` | Margin amount. |
| `pricing` | `array<int, array<string, mixed>>` | `[]` | Provider pricing by currency code and billing cycle. |

### Return Type

```json
{
  "status": "success | partial | error",
  "message": "2 product(s) processed successfully, 1 failed",
  "results": [
    {
      "product_id": 45,
      "reseller_product_id": 81,
      "status": "imported | synced | error",
      "message": "'Starter Hosting' imported successfully."
    }
  ],
  "success_count": 2,
  "error_count": 1
}
```

### Example

```bash
curl -X POST "https://whmcs.example.com/modules/servers/products_reseller_server/ajax_functions.php" \
  -d "prs_doing_ajax=1" \
  -d "action=importSyncProducts" \
  --data-urlencode 'products=[{"product_id":45,"product_name":"Starter Hosting","product_type":"hostingaccount","product_group_name":"Shared Hosting","paytype":"recurring","is_mapped":false,"reseller_product_id":0,"margin_type":"percentage","margin_value":15,"pricing":[{"currency_code":"USD","pricing":{"monthly":5,"quarterly":-1,"semiannually":-1,"annually":50,"biennially":-1,"triennially":-1,"msetupfee":0,"qsetupfee":0,"ssetupfee":0,"asetupfee":0,"bsetupfee":0,"tsetupfee":0}}]}]'
```

## Processing Notes

### Currency translation

The endpoint matches provider `currency_code` values to `tblcurrencies.code`. This is why local currency codes must exist before import.

### Local product creation

New products are created through `localAPI('AddProduct', ...)` with:

```php
[
  'module' => 'products_reseller_server',
  'configoption1' => $productId,
  'pricing' => $adjustedPricing,
  'autosetup' => 'payment',
]
```

### Local sync behavior

Existing mapped products have pricing rows updated or inserted in `tblpricing`, and the product `name` and `description` are refreshed in `tblproducts`.

### Default action fallback

Unknown actions return:

```json
{
  "status": "error",
  "message": "No case is match"
}
```

Related pages: [Admin Hooks](/docs/api-reference/admin-hooks), [Product Import and Sync](/docs/product-import-sync).
