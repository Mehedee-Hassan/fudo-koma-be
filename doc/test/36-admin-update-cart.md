# Admin — Moderate or Feature Cart

`PATCH /api/v1/admin/carts/{cart}`

[All API tests](README.md) · Authorization: **admin** · Success for the shared fixture: **200**

## Parameters

| Name | Location | Validation / behavior | Example |
|---|---|---|---|
| `cart` | Path | Existing resource ID | `401` |
| `moderation_status` | JSON body | `sometimes / in:pending,approved,rejected` | `approved` |
| `is_featured` | JSON body | `sometimes / boolean` | `True` |

All supported fields are shown above. Omit optional fields to retain defaults or existing values. Unknown body fields are ignored by these controllers; they do not grant privileges. See the index for headers, fixture IDs, and validation notation.

## Example with all parameters

```json
{
  "moderation_status": "approved",
  "is_featured": true
}
```

```bash
curl -i -X PATCH "$BASE_URL/api/v1/admin/carts/401" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H 'Content-Type: application/json' \
  --data '{"moderation_status":"approved","is_featured":true}'
```

## Expected response and effects

All fields optional. Does not allow changing owner, name, or location. Unknown cart returns 404.

Illustrative response excerpt (IDs/timestamps/tokens vary; additional fields may be present):

```json
{
  "id": 401,
  "moderation_status": "approved",
  "is_featured": true
}
```

## Test cases

| Case | Input / action | Expected |
|---|---|---|
| All supported parameters | Run the full example with matching fixture records | 200 |
| No authentication | Omit Authorization | 401 |
| Disabled account | Use a token for a disabled account | 403 |
| Wrong role | Use a customer token | 403 |
| Missing resource | Replace a path ID with 999999 | 404 |
| moderation_status — null | `null` (keep other fields valid) | 422 |
| moderation_status — wrong type | `["unexpected"]` (keep other fields valid) | 422 |
| moderation_status — invalid choice | `"unsupported-value"` (keep other fields valid) | 422 |
| is_featured — null | `null` (keep other fields valid) | 422 |
| is_featured — invalid boolean | `"true"` (keep other fields valid) | 422 |

Cases use independent fixtures; do not run destructive examples sequentially against the same records without restoring them. Automated coverage is in `tests/Feature/ApiEndpointMatrixTest.php`, using `tests/Fixtures/api-cases.json`. The behavioral notes also describe scenarios for manual regression testing; they are not a claim that every possible value or combination was executed.

## Accepted parameter variants

These cases keep the remaining fields valid and expect a 2xx response. For user-target reports, target_id changes to 102; password boundary cases update the confirmation too.

| Case | Value / action | Expected |
|---|---|---|
| moderation_status — optional omitted | `omit field` | 2xx |
| moderation_status — allowed pending | `"pending"` | 2xx |
| moderation_status — allowed approved | `"approved"` | 2xx |
| moderation_status — allowed rejected | `"rejected"` | 2xx |
| is_featured — optional omitted | `omit field` | 2xx |
| is_featured — allowed true | `true` | 2xx |
| is_featured — allowed false | `false` | 2xx |
| is_featured — allowed 0 | `0` | 2xx |
| is_featured — allowed 1 | `1` | 2xx |
| is_featured — allowed "0" | `"0"` | 2xx |
| is_featured — allowed "1" | `"1"` | 2xx |
