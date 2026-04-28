---
title: "Server Module"
description: "Reference for the WHMCS callback functions defined in products_reseller_server.php."
---

Source file: `modules/servers/products_reseller_server/products_reseller_server.php`

This file is the WHMCS-facing public surface. WHMCS discovers these functions by filename and prefix, so these are the real exported entry points of the module.

## Runtime Import Path

```php
modules/servers/products_reseller_server/products_reseller_server.php
```

## Callback Signatures

```php
function products_reseller_server_MetaData(): array
function products_reseller_server_TestConnection(array $params): array
function products_reseller_server_ConfigOptions(array $params): array
function products_reseller_server_CreateAccount(array $params): string
function products_reseller_server_SuspendAccount(array $params): string
function products_reseller_server_UnsuspendAccount(array $params): string
function products_reseller_server_TerminateAccount(array $params): string
function products_reseller_server_ChangePackage(array $params): string
function products_reseller_server_ChangePassword(array $params): string
function products_reseller_server_ServiceSingleSignOn(array $params): array
function products_reseller_server_CustomActions(array $params): CustomActionCollection
function products_reseller_server_ClientArea(array $params): array|string
```

## `products_reseller_server_MetaData()`

Defined near the top of the file. Returns the static WHMCS module metadata, including display labels for service and admin SSO.

### Returns

| Field | Type | Default | Description |
|------|------|---------|-------------|
| `DisplayName` | `string` | `Products Reseller for WHMCS` | Name shown in WHMCS module selectors. |
| `APIVersion` | `string` | `1.0` | WHMCS server-module API version. |
| `RequiresServer` | `bool` | `true` | Forces the module to use a configured server record. |
| `ServiceSingleSignOnLabel` | `string` | `Login to cPanel` | Label shown in the client area. |
| `AdminSingleSignOnLabel` | `string` | `Login to cPanel` | Label shown in admin UI. |

### Example

```php
<?php

$meta = products_reseller_server_MetaData();
echo $meta['DisplayName'];
```

## `products_reseller_server_TestConnection(array $params)`

Calls `Get_Products` against the first active reseller server and treats a non-empty `data` array as success.

### Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `params` | `array<string, mixed>` | — | WHMCS connection-test context. The current implementation does not read from this array directly. |

### Returns

```php
array{
  success: bool,
  error: string|null
}
```

### Example

```php
<?php

$result = products_reseller_server_TestConnection([]);

if (!$result['success']) {
    echo $result['error'];
}
```

## `products_reseller_server_ConfigOptions(array $params)`

Builds the module settings shown on a WHMCS product. It fetches provider products through `Get_Products`, maps them into the product dropdown, and appends six yes/no fields used by the client-area fallback renderer.

### Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `params` | `array<string, mixed>` | — | WHMCS product module context. |

### Returns

```php
array<string, array<string, mixed>>
```

### Config Option Fields

| Option key | Type | Default | Description |
|-----------|------|---------|-------------|
| `products` | `dropdown` | `0` | Provider product list used as `configoption1`. |
| `show_server_name` | `yesno` | `on` | Show server name in non-cPanel fallback UI. |
| `show_host_name` | `yesno` | `on` | Show hostname in non-cPanel fallback UI. |
| `show_domain` | `yesno` | `on` | Show domain in non-cPanel fallback UI. |
| `show_ip` | `yesno` | `on` | Show IP address in non-cPanel fallback UI. |
| `show_username` | `yesno` | `on` | Show service username in non-cPanel fallback UI. |
| `show_password` | `yesno` | `on` | Show decrypted service password in non-cPanel fallback UI. |

### Example

```php
<?php

$options = products_reseller_server_ConfigOptions([]);
$assignedProducts = $options['products']['Options'];
```

## Provisioning Callbacks

The following functions all gather service fields from `tblhosting`, add `server_id` and an `action` string, and then delegate to the corresponding `ProductsReseller_Main` wrapper. Each returns either `success` or a provider error string.

### Shared Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `serviceid` | `int` | — | WHMCS hosting service ID. |
| `serverid` | `int` | — | WHMCS server ID passed upstream as `server_id`. |
| `password` | `string` | depends on WHMCS | Service password used for create, suspend, unsuspend, terminate, change package, and password change flows. |
| `configoption1` | `int|string` | — | Provider product ID for create-account requests only. |
| `suspendreason` | `string` | `''` | Suspension reason used by suspend requests only. |

### Functions

```php
products_reseller_server_CreateAccount(array $params): string
products_reseller_server_SuspendAccount(array $params): string
products_reseller_server_UnsuspendAccount(array $params): string
products_reseller_server_TerminateAccount(array $params): string
products_reseller_server_ChangePackage(array $params): string
products_reseller_server_ChangePassword(array $params): string
```

### Example

```php
<?php

$result = products_reseller_server_CreateAccount([
    'serviceid' => 1205,
    'serverid' => 2,
    'password' => 'TempPassword123!',
    'configoption1' => 45,
]);
```

## `products_reseller_server_ServiceSingleSignOn(array $params)`

Generates a cPanel SSO redirect. Uses `$params['app']` when present and falls back to `$_GET['app']` or `Home`.

### Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `serviceid` | `int` | `null` | Target WHMCS service. |
| `serverid` | `int` | `null` | Target WHMCS server. |
| `app` | `string` | `Home` | Optional cPanel application deep link. |

### Returns

```php
array{
  success: bool,
  redirectTo?: string,
  errorMsg?: string
}
```

### Example

```php
<?php

$sso = products_reseller_server_ServiceSingleSignOn([
    'serviceid' => 1205,
    'serverid' => 2,
    'app' => 'Backups_Home',
]);
```

## `products_reseller_server_CustomActions(array $params)`

Returns a `CustomActionCollection` containing **Log in to cPanel** only when the server type is `products_reseller_server` and `GetServerName` resolves to `cpanel`.

### Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `params` | `array<string, mixed>` | — | WHMCS service or admin action context. |

### Return Type

```php
WHMCS\Module\Server\CustomActionCollection
```

### Example

```php
<?php

$actions = products_reseller_server_CustomActions([
    'serviceid' => 1205,
    'serverid' => 2,
]);
```

## `products_reseller_server_ClientArea(array $params)`

Builds the client-area response.

### Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `serviceid` | `int` | — | WHMCS hosting service ID. |
| `serverid` | `int` | — | WHMCS server ID. |
| `domain` | `string` | `''` | Fallback domain if not present on the service row. |
| `username` | `string` | `N/A` | Fallback username if not present on the service row. |
| `configoption2-7` | `string` | module setting | Non-cPanel field visibility toggles. |

### Return Type

```php
array{
  tabOverviewReplacementTemplate?: string,
  vars?: array<string, mixed>
}|string
```

### Example

```php
<?php

$clientArea = products_reseller_server_ClientArea([
    'serviceid' => 1205,
    'serverid' => 2,
    'configoption2' => 'on',
    'configoption3' => 'on',
    'configoption4' => 'on',
    'configoption5' => 'off',
    'configoption6' => 'on',
    'configoption7' => 'off',
]);
```

Related pages: [ProductsReseller_Main](/docs/api-reference/main-class), [Admin Hooks](/docs/api-reference/admin-hooks), [AJAX Endpoint](/docs/api-reference/ajax-endpoint).
