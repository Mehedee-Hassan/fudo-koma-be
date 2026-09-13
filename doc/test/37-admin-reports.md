# Admin — List Reports

`GET /api/v1/admin/reports`

[All API tests](README.md) · Authorization: **admin** · Success for the shared fixture: **200**

## Parameters

| Name | Location | Validation / behavior | Example |
|---|---|---|---|
| `status` | Query | `nullable / in:open,reviewing,resolved,dismissed` | `open` |
| `page` | Query | `pagination` | `1` |

All supported fields are shown above. Omit optional fields to retain defaults or existing values. Unknown body fields are ignored by these controllers; they do not grant privileges. See the index for headers, fixture IDs, and validation notation.

## Example with all parameters

```bash
curl -i -X GET "$BASE_URL/api/v1/admin/reports" --get \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  --data-urlencode 'status=open' \
  --data-urlencode 'page=1'
```

## Expected response and effects

Omit status for all reports. Invalid status returns 422. No reporter or target filter is implemented.

Returns a paginated object with `data`, `current_page`, `per_page` (25), `total`, `last_page`, and navigation links. An empty result is `200` with `data: []`.

## Test cases

| Case | Input / action | Expected |
|---|---|---|
| All supported parameters | Run the full example with matching fixture records | 200 |
| No authentication | Omit Authorization | 401 |
| Disabled account | Use a token for a disabled account | 403 |
| Wrong role | Use a customer token | 403 |
| status — wrong type | `["unexpected"]` (keep other fields valid) | 422 |
| status — invalid choice | `"unsupported-value"` (keep other fields valid) | 422 |

Cases use independent fixtures; do not run destructive examples sequentially against the same records without restoring them. Automated coverage is in `tests/Feature/ApiEndpointMatrixTest.php`, using `tests/Fixtures/api-cases.json`. The behavioral notes also describe scenarios for manual regression testing; they are not a claim that every possible value or combination was executed.

## Accepted parameter variants

These cases keep the remaining fields valid and expect a 2xx response. For user-target reports, target_id changes to 102; password boundary cases update the confirmation too.

| Case | Value / action | Expected |
|---|---|---|
| status — optional omitted | `omit field` | 2xx |
| status — nullable value | `null` | 2xx |
| status — allowed open | `"open"` | 2xx |
| status — allowed reviewing | `"reviewing"` | 2xx |
| status — allowed resolved | `"resolved"` | 2xx |
| status — allowed dismissed | `"dismissed"` | 2xx |
| page — optional omitted | `omit field` | 2xx |
| page — empty later page | `999` | 2xx |
