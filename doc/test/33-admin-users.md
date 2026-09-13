# Admin — List Users

`GET /api/v1/admin/users`

[All API tests](README.md) · Authorization: **admin** · Success for the shared fixture: **200**

## Parameters

| Name | Location | Validation / behavior | Example |
|---|---|---|---|
| `search` | Query | `nullable / string / max:100` | `Tokyo` |
| `page` | Query | `pagination` | `1` |

All supported fields are shown above. Omit optional fields to retain defaults or existing values. Unknown body fields are ignored by these controllers; they do not grant privileges. See the index for headers, fixture IDs, and validation notation.

## Example with all parameters

```bash
curl -i -X GET "$BASE_URL/api/v1/admin/users" --get \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  --data-urlencode 'search=Tokyo' \
  --data-urlencode 'page=1'
```

## Expected response and effects

Search matches name, not email. Customer and owner tokens return 403. Passwords and remember tokens are hidden.

Returns a paginated object with `data`, `current_page`, `per_page` (25), `total`, `last_page`, and navigation links. An empty result is `200` with `data: []`.

## Test cases

| Case | Input / action | Expected |
|---|---|---|
| All supported parameters | Run the full example with matching fixture records | 200 |
| No authentication | Omit Authorization | 401 |
| Disabled account | Use a token for a disabled account | 403 |
| Wrong role | Use a customer token | 403 |
| search — wrong type | `["unexpected"]` (keep other fields valid) | 422 |
| search — above maximum | `"xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"` (keep other fields valid) | 422 |

Cases use independent fixtures; do not run destructive examples sequentially against the same records without restoring them. Automated coverage is in `tests/Feature/ApiEndpointMatrixTest.php`, using `tests/Fixtures/api-cases.json`. The behavioral notes also describe scenarios for manual regression testing; they are not a claim that every possible value or combination was executed.

## Accepted parameter variants

These cases keep the remaining fields valid and expect a 2xx response. For user-target reports, target_id changes to 102; password boundary cases update the confirmation too.

| Case | Value / action | Expected |
|---|---|---|
| search — optional omitted | `omit field` | 2xx |
| search — nullable value | `null` | 2xx |
| search — maximum accepted | `"xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"` | 2xx |
| page — optional omitted | `omit field` | 2xx |
| page — empty later page | `999` | 2xx |
