# API Documentation

This document describes the API actions used by the WHMCS module and the request format expected by the Avalon reseller endpoint.

## Request Envelope

All module calls are sent as JSON with this structure:

```json
{
  "api_key": "your_api_key",
  "action": "CreateAccount",
  "params": {
    "serviceid": 123,
    "server_id": 1
  }
}
```

## Response Format

The module expects JSON responses that include at least a status field.

Typical success shape:

```json
{
  "status": "success",
  "data": {
    "serviceid": 12345
  },
  "message": "Request completed successfully"
}
```

Typical error shape:

```json
{
  "status": "error",
  "message": "Human readable error"
}
```

## Primary Service Actions

### CreateAccount

Used by WHMCS when provisioning a new service.

Parameters sent by module:

- serviceid (int)
- billingcycle (string)
- qty (int)
- domain (string)
- username (string)
- password (string)
- ip_address (string)
- selected_product (int)
- server_id (int)

### SuspendAccount

Used when a service is suspended.

Parameters sent by module:

- serviceid (int)
- server_id (int)
- suspendreason (string)
- domain (string)
- username (string)
- password (string)
- ip_address (string)

### UnsuspendAccount

Used when a service is reactivated.

Parameters sent by module:

- serviceid (int)
- server_id (int)
- domain (string)
- username (string)
- password (string)
- ip_address (string)

### TerminateAccount

Used when a service is terminated.

Parameters sent by module:

- serviceid (int)
- server_id (int)
- domain (string)
- username (string)
- password (string)
- ip_address (string)

### ChangePackage

Used when package/product is changed.

Parameters sent by module:

- serviceid (int)
- server_id (int)
- domain (string)
- username (string)
- password (string)
- ip_address (string)

### ChangePassword

Used when service password is changed from WHMCS.

Parameters sent by module:

- serviceid (int)
- server_id (int)
- password (string)
- domain (string)
- username (string)
- ip_address (string)

## Additional Actions Used By Module

### CreateSSOSession

Used for cPanel SSO redirection.

Parameters sent by module:

- serviceid (int)
- server_id (int)
- app (string, optional)

### Get_Products

Used by module config and connection test to fetch product list.

Parameters sent by module:

- server_id (int)

### GetServerName

Used to decide whether cPanel SSO action should be shown.

Parameters sent by module:

- serviceid (int)
- server_id (int)

### Get_Products_For_Import

Used in admin-side mapping/import flow.

### Save_Product_Mapping

Used in admin-side mapping flow to save mappings.

## cURL Example

```bash
curl -X POST "https://your-api-endpoint/modules/addons/products_reseller/api.php" \
  -H "Content-Type: application/json" \
  -d '{
    "api_key": "your_api_key",
    "action": "CreateAccount",
    "params": {
      "serviceid": 123,
      "server_id": 1,
      "domain": "example.com",
      "username": "exampleuser"
    }
  }'
```

## Notes

- The module logs requests/responses through WHMCS module logging.
- Endpoint and API key are configured in WHMCS server settings.
- For failures, review WHMCS Module Log first.
