# Log In

`POST /api/v1/auth/login`

[All API tests](README.md) · Authorization: **public** · Success for the shared fixture: **200**

## Parameters

| Name | Location | Validation / behavior | Example |
|---|---|---|---|
| `email` | JSON body | `required / email` | `customer@example.test` |
| `password` | JSON body | `required / string` | `Test-password-123!` |

All supported fields are shown above. Omit optional fields to retain defaults or existing values. Unknown body fields are ignored by these controllers; they do not grant privileges. See the index for headers, fixture IDs, and validation notation.

## Example with all parameters

```json
{
  "email": "customer@example.test",
  "password": "Test-password-123!"
}
```

```bash
curl -i -X POST "$BASE_URL/api/v1/auth/login" \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  --data '{"email":"customer@example.test","password":"Test-password-123!"}'
```

## Expected response and effects

Unknown email, wrong password, and disabled account return 422, not 401. A successful token expires after 30 days.

Illustrative response excerpt (IDs/timestamps/tokens vary; additional fields may be present):

```json
{
  "user": {
    "id": 101,
    "role": "customer"
  },
  "token": "<generated-token>"
}
```

## Test cases

| Case | Input / action | Expected |
|---|---|---|
| All supported parameters | Run the full example with matching fixture records | 200 |
| email — missing | `omit field` (keep other fields valid) | 422 |
| email — null | `null` (keep other fields valid) | 422 |
| email — wrong type | `["unexpected"]` (keep other fields valid) | 422 |
| email — invalid email | `"not-an-email"` (keep other fields valid) | 422 |
| password — missing | `omit field` (keep other fields valid) | 422 |
| password — null | `null` (keep other fields valid) | 422 |
| password — wrong type | `["unexpected"]` (keep other fields valid) | 422 |
| password — incorrect credentials | `"incorrect-password"` (keep other fields valid) | 422 |

Cases use independent fixtures; do not run destructive examples sequentially against the same records without restoring them. Automated coverage is in `tests/Feature/ApiEndpointMatrixTest.php`, using `tests/Fixtures/api-cases.json`. The behavioral notes also describe scenarios for manual regression testing; they are not a claim that every possible value or combination was executed.

## Accepted parameter variants

This endpoint has no additional independently optional or enumerated parameters. Use the full example and access-control cases above.
