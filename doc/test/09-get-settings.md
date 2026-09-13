# Get My Notification Settings

`GET /api/v1/me/settings`

[All API tests](README.md) · Authorization: **customer** · Success for the shared fixture: **200**

## Parameters

No path, query, or body parameters. Identity comes from the bearer token.

All supported fields are shown above. Omit optional fields to retain defaults or existing values. Unknown body fields are ignored by these controllers; they do not grant privileges. See the index for headers, fixture IDs, and validation notation.

## Example with all parameters

```bash
curl -i -X GET "$BASE_URL/api/v1/me/settings" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $CUSTOMER_TOKEN"
```

## Expected response and effects

Creates default settings if missing.

Illustrative response excerpt (IDs/timestamps/tokens vary; additional fields may be present):

```json
{
  "user_id": 101,
  "push_enabled": true,
  "nearby_enabled": true,
  "updates_enabled": true,
  "radius_meters": 5000
}
```

## Test cases

| Case | Input / action | Expected |
|---|---|---|
| All supported parameters | Run the full example with matching fixture records | 200 |
| No authentication | Omit Authorization | 401 |
| Disabled account | Use a token for a disabled account | 403 |

Cases use independent fixtures; do not run destructive examples sequentially against the same records without restoring them. Automated coverage is in `tests/Feature/ApiEndpointMatrixTest.php`, using `tests/Fixtures/api-cases.json`. The behavioral notes also describe scenarios for manual regression testing; they are not a claim that every possible value or combination was executed.

## Accepted parameter variants

This endpoint has no additional independently optional or enumerated parameters. Use the full example and access-control cases above.
