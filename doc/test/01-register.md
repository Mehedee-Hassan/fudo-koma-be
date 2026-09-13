# Register Customer

`POST /api/v1/auth/register`

[All API tests](README.md) · Authorization: **public** · Success for the shared fixture: **201**

## Parameters

| Name | Location | Validation / behavior | Example |
|---|---|---|---|
| `name` | JSON body | `required / string / max:100` | `Customer Test` |
| `email` | JSON body | `required / email / max:255 / unique:users` | `new-customer@example.test` |
| `password` | JSON body | `required / string / min:12 / confirmed` | `Test-password-123!` |
| `password_confirmation` | JSON body | `confirmation` | `Test-password-123!` |

All supported fields are shown above. Omit optional fields to retain defaults or existing values. Unknown body fields are ignored by these controllers; they do not grant privileges. See the index for headers, fixture IDs, and validation notation.

## Example with all parameters

```json
{
  "name": "Customer Test",
  "email": "new-customer@example.test",
  "password": "Test-password-123!",
  "password_confirmation": "Test-password-123!"
}
```

```bash
curl -i -X POST "$BASE_URL/api/v1/auth/register" \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  --data '{"name":"Customer Test","email":"new-customer@example.test","password":"Test-password-123!","password_confirmation":"Test-password-123!"}'
```

## Expected response and effects

Always creates a customer. Supplying role=admin does not grant privileges. A duplicate email returns 422. Password and confirmation must match; passwords are never returned.

Illustrative response excerpt (IDs/timestamps/tokens vary; additional fields may be present):

```json
{
  "user": {
    "name": "Customer Test",
    "email": "new-customer@example.test",
    "role": "customer"
  },
  "token": "<generated-token>"
}
```

## Test cases

| Case | Input / action | Expected |
|---|---|---|
| All supported parameters | Run the full example with matching fixture records | 201 |
| name — missing | `omit field` (keep other fields valid) | 422 |
| name — null | `null` (keep other fields valid) | 422 |
| name — wrong type | `["unexpected"]` (keep other fields valid) | 422 |
| name — above maximum | `"xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"` (keep other fields valid) | 422 |
| email — missing | `omit field` (keep other fields valid) | 422 |
| email — null | `null` (keep other fields valid) | 422 |
| email — wrong type | `["unexpected"]` (keep other fields valid) | 422 |
| email — invalid email | `"not-an-email"` (keep other fields valid) | 422 |
| email — above maximum | `string of 256 characters` (keep other fields valid) | 422 |
| email — duplicate email | `"customer@example.test"` (keep other fields valid) | 422 |
| password — missing | `omit field` (keep other fields valid) | 422 |
| password — null | `null` (keep other fields valid) | 422 |
| password — wrong type | `["unexpected"]` (keep other fields valid) | 422 |
| password — below minimum | `"xxxxxxxxxxx"` (keep other fields valid) | 422 |
| password_confirmation — mismatch | `"different-password"` (keep other fields valid) | 422 |

Cases use independent fixtures; do not run destructive examples sequentially against the same records without restoring them. Automated coverage is in `tests/Feature/ApiEndpointMatrixTest.php`, using `tests/Fixtures/api-cases.json`. The behavioral notes also describe scenarios for manual regression testing; they are not a claim that every possible value or combination was executed.

## Accepted parameter variants

These cases keep the remaining fields valid and expect a 2xx response. For user-target reports, target_id changes to 102; password boundary cases update the confirmation too.

| Case | Value / action | Expected |
|---|---|---|
| name — maximum accepted | `"xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"` | 2xx |
| password — minimum accepted | `"xxxxxxxxxxxx"` | 2xx |
