---
title: "Getting Started"
description: "Install, configure, and understand the Avalon Hosting Services WHMCS reseller module."
---

`/avalonhostingservices/whmcs-reseller-module` is a WHMCS server module that provisions and manages reseller-hosted services through Avalon Hosting Services' upstream reseller API.

## The Problem

- WHMCS expects a server module interface, but reseller providers usually expose raw HTTP APIs instead of WHMCS-native lifecycle callbacks.
- Product catalogs, price changes, and local WHMCS product mappings drift over time when they are maintained by hand.
- cPanel-based services need conditional SSO and usage panels, while non-cPanel services need a simpler service-information view.
- Resellers need installation and activation to be low-friction because the module must live directly inside an existing WHMCS deployment.

## The Solution

The module implements the standard WHMCS server-module callbacks in `modules/servers/products_reseller_server/products_reseller_server.php`, funnels those calls through `ProductsReseller_Main`, and translates them into the JSON envelope expected by the upstream reseller API. It also installs admin hooks that can import or sync the provider catalog into local WHMCS products.

```php
<?php

$main = new ProductsReseller_Main();

$result = $main->send_request_to_api([
    'action' => 'CreateAccount',
    'server_id' => 7,
    'serviceid' => 1234,
    'billingcycle' => 'Monthly',
    'qty' => 1,
    'domain' => 'example.com',
    'username' => 'exampleuser',
    'password' => 'TempPassword123!',
    'ip_address' => '203.0.113.10',
    'selected_product' => 45,
]);
```

That request becomes a JSON payload with `api_key`, `action`, and `params`, then the decoded response is returned to the WHMCS callback layer.

## Installation

<Callout type="info">
This project is distributed as a WHMCS release archive, not as a package published to JavaScript registries. The tabs below keep the landing page format consistent while showing the same release-download workflow for each environment.
</Callout>

<Tabs items={["npm", "pnpm", "yarn", "bun"]}>
<Tab value="npm">

```bash
mkdir -p /tmp/whmcs-reseller-module
cd /tmp/whmcs-reseller-module
curl -L -o whmcs-reseller-module.zip \
  https://github.com/AvalonHostingServices/whmcs-reseller-module/releases/latest/download/whmcs-reseller-module.zip
unzip whmcs-reseller-module.zip -d /path/to/whmcs
```

</Tab>
<Tab value="pnpm">

```bash
mkdir -p /tmp/whmcs-reseller-module
cd /tmp/whmcs-reseller-module
curl -L -o whmcs-reseller-module.zip \
  https://github.com/AvalonHostingServices/whmcs-reseller-module/releases/latest/download/whmcs-reseller-module.zip
unzip whmcs-reseller-module.zip -d /path/to/whmcs
```

</Tab>
<Tab value="yarn">

```bash
mkdir -p /tmp/whmcs-reseller-module
cd /tmp/whmcs-reseller-module
curl -L -o whmcs-reseller-module.zip \
  https://github.com/AvalonHostingServices/whmcs-reseller-module/releases/latest/download/whmcs-reseller-module.zip
unzip whmcs-reseller-module.zip -d /path/to/whmcs
```

</Tab>
<Tab value="bun">

```bash
mkdir -p /tmp/whmcs-reseller-module
cd /tmp/whmcs-reseller-module
curl -L -o whmcs-reseller-module.zip \
  https://github.com/AvalonHostingServices/whmcs-reseller-module/releases/latest/download/whmcs-reseller-module.zip
unzip whmcs-reseller-module.zip -d /path/to/whmcs
```

</Tab>
</Tabs>

## Quick Start

The smallest working setup is:

1. Extract the module so the path is `modules/servers/products_reseller_server/`.
2. In WHMCS admin, create a server of type **Products Reseller for WHMCS**.
3. Put the provider hostname in the WHMCS **Hostname** field.
4. Put any optional path prefix in the WHMCS **Username** field. The module relabels this field to **API Endpoint** in admin UI.
5. Put the reseller API key in the WHMCS **Password** field. The module relabels this to **API Key**.
6. Assign that server to a product using the module type **Products Reseller for WHMCS**.

Once a service is created, WHMCS will call `products_reseller_server_CreateAccount($params)` and the module will return `success` when the upstream API responds with a non-error status.

```php
<?php

$result = products_reseller_server_CreateAccount([
    'serviceid' => 1234,
    'serverid' => 7,
    'password' => 'TempPassword123!',
    'configoption1' => 45,
]);

var_dump($result);
```

Expected output:

```text
success
```

## Key Features

- WHMCS lifecycle support for create, suspend, unsuspend, terminate, package change, and password change.
- Dynamic product dropdown population from the provider catalog during module configuration.
- Admin-side product import and sync with margin controls and currency-aware pricing translation.
- Conditional cPanel SSO and usage widgets when the upstream product reports `server_name = cpanel`.
- Client-area fallback rendering for non-cPanel services with configurable visibility for server details.
- Release packages and install scripts designed for direct extraction into an existing WHMCS root.

<Cards>
  <Card title="Architecture" href="/docs/architecture">Understand the callback flow, hook bootstrap, and data lifecycle inside the module.</Card>
  <Card title="Core Concepts" href="/docs/module-lifecycle">Start with the provisioning lifecycle, transport client, and import pipeline.</Card>
  <Card title="API Reference" href="/docs/api-reference/server-module">See every public callback, method, endpoint action, and source file.</Card>
</Cards>
