---
title: "ProductsReseller_Main"
description: "Reference for the transport and provider-adapter class defined in pr_server_classes.php."
---

Source file: `modules/servers/products_reseller_server/pr_server_classes.php`

## Runtime Import Path

```php
require_once __DIR__ . '/modules/servers/products_reseller_server/pr_server_classes.php';
```

## Class Signature

```php
class ProductsReseller_Main
```

## Public Methods

```php
public function send_request_to_api(array $data): array
public function create_account(array $data): string
public function suspend_account(array $data): string
public function unsuspend_account(array $data): string
public function terminate_account(array $data): string
public function change_package(array $data): string
public function change_password(array $data): string
public function get_server_name(array $data): array
public function get_cpanel_sso(array $data): array
public function get_usage_deatils(array $data): array
```

## Constructor

The class does not define a constructor. Instantiate it directly:

```php
<?php

$main = new ProductsReseller_Main();
```

## `send_request_to_api(array $data)`

This is the core transport method used everywhere else.

### Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `server_id` | `int` | — | WHMCS server ID used to load endpoint and encrypted API key. |
| `action` | `string` | — | Provider action such as `Get_Products` or `CreateAccount`. |
| `...` | `mixed` | — | Any additional provider parameters passed inside `params`. |

### Return Type

```php
array<string, mixed>
```

The returned array is whatever the upstream API returns after JSON decoding, or a locally generated error array such as:

```php
[
  'status' => 'error',
  'message' => 'Serverid is required',
]
```

### Example

```php
<?php

$main = new ProductsReseller_Main();

$response = $main->send_request_to_api([
    'action' => 'GetServerName',
    'server_id' => 2,
    'serviceid' => 1205,
]);
```

## Provisioning Wrapper Methods

These methods all share the same contract: send the request upstream, then return `success` when the provider did not return `status = error`.

### Shared Signature

```php
public function create_account(array $data): string
public function suspend_account(array $data): string
public function unsuspend_account(array $data): string
public function terminate_account(array $data): string
public function change_package(array $data): string
public function change_password(array $data): string
```

### Shared Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `server_id` | `int` | — | Provider server lookup source. |
| `serviceid` | `int` | — | WHMCS service ID. |
| `action` | `string` | — | Provider action matching the wrapper purpose. |
| `domain` | `string` | `''` | Service domain when relevant. |
| `username` | `string` | `''` | Service username when relevant. |
| `password` | `string` | `''` | Service password when relevant. |
| `ip_address` | `string` | `''` | Dedicated IP value when available. |
| `selected_product` | `int|string` | — | Provider product ID for `create_account` only. |
| `billingcycle` | `string` | — | Billing cycle for `create_account` only. |
| `qty` | `int` | — | Quantity for `create_account` only. |
| `suspendreason` | `string` | `''` | Suspension reason for `suspend_account` only. |

### Return Type

| Return | Type | Description |
|--------|------|-------------|
| success | `string` | Returned when provider `status` is not `error`. |
| error message | `string` | Returned when provider `status` equals `error`. |

### Combined Example

```php
<?php

$main = new ProductsReseller_Main();

$result = $main->terminate_account([
    'action' => 'TerminateAccount',
    'server_id' => 2,
    'serviceid' => 1205,
    'domain' => 'example.com',
    'username' => 'exampleuser',
    'password' => 'CurrentPassword123!',
    'ip_address' => '203.0.113.10',
]);
```

## Query Methods

### `get_server_name(array $data)`

Returns the decoded response for `GetServerName`.

### `get_cpanel_sso(array $data)`

Returns the decoded response for `CreateSSOSession`, typically including `url`.

### `get_usage_deatils(array $data)`

Returns the decoded response for `get_Bandwidth_Disk_Usage`. The method name contains a typo in the source and should be referenced exactly as implemented.

### Shared Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `server_id` | `int` | — | WHMCS server ID. |
| `serviceid` | `int` | — | WHMCS service ID. |
| `action` | `string` | — | Provider action. |
| `app` | `string` | `Home` | Optional cPanel target for SSO only. |

### Example

```php
<?php

$main = new ProductsReseller_Main();

$usage = $main->get_usage_deatils([
    'action' => 'get_Bandwidth_Disk_Usage',
    'server_id' => 2,
    'serviceid' => 1205,
]);

echo $usage['disk_usage']['percentage_used'] ?? 0;
```

## Common Patterns

### Fetch first, then branch on capability

```php
<?php

$main = new ProductsReseller_Main();
$server = $main->get_server_name([
    'action' => 'GetServerName',
    'server_id' => 2,
    'serviceid' => 1205,
]);

if (($server['server_name'] ?? '') === 'cpanel') {
    $sso = $main->get_cpanel_sso([
        'action' => 'CreateSSOSession',
        'server_id' => 2,
        'serviceid' => 1205,
        'app' => 'Cron_Home',
    ]);
}
```

Related pages: [Server Module](/docs/api-reference/server-module), [API Transport](/docs/api-transport).
