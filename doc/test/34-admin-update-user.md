# Admin — Change User Role or Status

`PATCH /api/v1/admin/users/{user}`

[All API tests](README.md) · Authorization: **admin** · Success for the shared fixture: **200**

## Parameters

| Name | Location | Validation / behavior | Example |
|---|---|---|---|
| `user` | Path | Existing resource ID | `102` |
| `is_active` | JSON body | `sometimes / boolean` | `False` |
| `role` | JSON body | `sometimes / in:customer,owner,admin` | `owner` |

All supported fields are shown above. Omit optional fields to retain defaults or existing values. Unknown body fields are ignored by these controllers; they do not grant privileges. See the index for headers, fixture IDs, and validation notation.

## Example with all parameters

```json
{
  "is_active": false,
  "role": "owner"
}
```

```bash
curl -i -X PATCH "$BASE_URL/api/v1/admin/users/102" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H 'Content-Type: application/json' \
  --data '{"is_active":false,"role":"owner"}'
```

## Expected response and effects

All fields optional. Disabling revokes that user’s API tokens and hides their carts from public discovery. Disabling or demoting the current administrator returns 422. Unknown user returns 404.

Illustrative response excerpt (IDs/timestamps/tokens vary; additional fields may be present):

```json
{
  "id": 102,
  "role": "owner",
  "is_active": false
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
| is_active — null | `null` (keep other fields valid) | 422 |
| is_active — invalid boolean | `"true"` (keep other fields valid) | 422 |
| role — null | `null` (keep other fields valid) | 422 |
| role — wrong type | `["unexpected"]` (keep other fields valid) | 422 |
| role — invalid choice | `"unsupported-value"` (keep other fields valid) | 422 |

Cases use independent fixtures; do not run destructive examples sequentially against the same records without restoring them. Automated coverage is in `tests/Feature/ApiEndpointMatrixTest.php`, using `tests/Fixtures/api-cases.json`. The behavioral notes also describe scenarios for manual regression testing; they are not a claim that every possible value or combination was executed.

## Accepted parameter variants

These cases keep the remaining fields valid and expect a 2xx response. For user-target reports, target_id changes to 102; password boundary cases update the confirmation too.

| Case | Value / action | Expected |
|---|---|---|
| is_active — optional omitted | `omit field` | 2xx |
| is_active — allowed true | `true` | 2xx |
| is_active — allowed false | `false` | 2xx |
| is_active — allowed 0 | `0` | 2xx |
| is_active — allowed 1 | `1` | 2xx |
| is_active — allowed "0" | `"0"` | 2xx |
| is_active — allowed "1" | `"1"` | 2xx |
| role — optional omitted | `omit field` | 2xx |
| role — allowed customer | `"customer"` | 2xx |
| role — allowed owner | `"owner"` | 2xx |
| role — allowed admin | `"admin"` | 2xx |
