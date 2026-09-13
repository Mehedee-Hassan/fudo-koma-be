# List Cart Updates

`GET /api/v1/carts/{cart}/updates`

[All API tests](README.md) · Authorization: **public** · Success for the shared fixture: **200**

## Parameters

| Name | Location | Validation / behavior | Example |
|---|---|---|---|
| `cart` | Path | Existing resource ID | `401` |
| `page` | Query | `pagination` | `1` |

All supported fields are shown above. Omit optional fields to retain defaults or existing values. Unknown body fields are ignored by these controllers; they do not grant privileges. See the index for headers, fixture IDs, and validation notation.

## Example with all parameters

```bash
curl -i -X GET "$BASE_URL/api/v1/carts/401/updates" --get \
  -H 'Accept: application/json' \
  --data-urlencode 'page=1'
```

## Expected response and effects

Cart must be publicly visible; otherwise 404. Returns only this cart’s updates, newest first.

Returns a paginated object with `data`, `current_page`, `per_page` (25), `total`, `last_page`, and navigation links. An empty result is `200` with `data: []`.

## Test cases

| Case | Input / action | Expected |
|---|---|---|
| All supported parameters | Run the full example with matching fixture records | 200 |
| Missing resource | Replace a path ID with 999999 | 404 |

Cases use independent fixtures; do not run destructive examples sequentially against the same records without restoring them. Automated coverage is in `tests/Feature/ApiEndpointMatrixTest.php`, using `tests/Fixtures/api-cases.json`. The behavioral notes also describe scenarios for manual regression testing; they are not a claim that every possible value or combination was executed.

## Accepted parameter variants

These cases keep the remaining fields valid and expect a 2xx response. For user-target reports, target_id changes to 102; password boundary cases update the confirmation too.

| Case | Value / action | Expected |
|---|---|---|
| page — optional omitted | `omit field` | 2xx |
| page — empty later page | `999` | 2xx |
