# Register Push Device

`POST /api/v1/me/devices`

[All API tests](README.md) · Authorization: **customer** · Success for the shared fixture: **201**

## Parameters

| Name | Location | Validation / behavior | Example |
|---|---|---|---|
| `token` | JSON body | `required / string / max:4096` | `test-fcm-device-token-not-for-real-delivery` |
| `platform` | JSON body | `required / in:android,ios,web` | `android` |

All supported fields are shown above. Omit optional fields to retain defaults or existing values. Unknown body fields are ignored by these controllers; they do not grant privileges. See the index for headers, fixture IDs, and validation notation.

## Example with all parameters

```json
{
  "token": "test-fcm-device-token-not-for-real-delivery",
  "platform": "android"
}
```

```bash
curl -i -X POST "$BASE_URL/api/v1/me/devices" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $CUSTOMER_TOKEN" \
  -H 'Content-Type: application/json' \
  --data '{"token":"test-fcm-device-token-not-for-real-delivery","platform":"android"}'
```

## Expected response and effects

201 for a new token; 200 when updating an existing token. Re-registering the same token does not create a second device. Registration transfers the token to the current user if previously registered by another account. Token and token_hash are hidden in responses. The example token is synthetic; keep PUSH_DRIVER=disabled for manual tests.

Illustrative response excerpt (IDs/timestamps/tokens vary; additional fields may be present):

```json
{
  "id": 601,
  "user_id": 101,
  "platform": "android"
}
```

## Test cases

| Case | Input / action | Expected |
|---|---|---|
| All supported parameters | Run the full example with matching fixture records | 201 |
| No authentication | Omit Authorization | 401 |
| Disabled account | Use a token for a disabled account | 403 |
| token — missing | `omit field` (keep other fields valid) | 422 |
| token — null | `null` (keep other fields valid) | 422 |
| token — wrong type | `["unexpected"]` (keep other fields valid) | 422 |
| token — above maximum | `string of 4097 characters` (keep other fields valid) | 422 |
| platform — missing | `omit field` (keep other fields valid) | 422 |
| platform — null | `null` (keep other fields valid) | 422 |
| platform — wrong type | `["unexpected"]` (keep other fields valid) | 422 |
| platform — invalid choice | `"unsupported-value"` (keep other fields valid) | 422 |

Cases use independent fixtures; do not run destructive examples sequentially against the same records without restoring them. Automated coverage is in `tests/Feature/ApiEndpointMatrixTest.php`, using `tests/Fixtures/api-cases.json`. The behavioral notes also describe scenarios for manual regression testing; they are not a claim that every possible value or combination was executed.

## Accepted parameter variants

These cases keep the remaining fields valid and expect a 2xx response. For user-target reports, target_id changes to 102; password boundary cases update the confirmation too.

| Case | Value / action | Expected |
|---|---|---|
| token — maximum accepted | `string of 4096 characters` | 2xx |
| platform — allowed android | `"android"` | 2xx |
| platform — allowed ios | `"ios"` | 2xx |
| platform — allowed web | `"web"` | 2xx |
