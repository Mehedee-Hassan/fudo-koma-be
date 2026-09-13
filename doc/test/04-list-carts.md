# Browse and Search Carts

`GET /api/v1/carts`

[All API tests](README.md) · Authorization: **public** · Success for the shared fixture: **200**

## Parameters

| Name | Location | Validation / behavior | Example |
|---|---|---|---|
| `search` | Query | `nullable / string / max:100` | `Tokyo` |
| `latitude` | Query | `required_with:longitude / numeric / between:-90,90` | `35.6812` |
| `longitude` | Query | `required_with:latitude / numeric / between:-180,180` | `139.7671` |
| `radius_meters` | Query | `sometimes / integer / min:100 / max:50000` | `5000` |
| `page` | Query | `pagination` | `1` |

All supported fields are shown above. Omit optional fields to retain defaults or existing values. Unknown body fields are ignored by these controllers; they do not grant privileges. See the index for headers, fixture IDs, and validation notation.

## Example with all parameters

```bash
curl -i -X GET "$BASE_URL/api/v1/carts" --get \
  -H 'Accept: application/json' \
  --data-urlencode 'search=Tokyo' \
  --data-urlencode 'latitude=35.6812' \
  --data-urlencode 'longitude=139.7671' \
  --data-urlencode 'radius_meters=5000' \
  --data-urlencode 'page=1'
```

## Expected response and effects

Coordinates must be supplied together. Without coordinates this is a catalog search; radius alone does not filter by proximity. With coordinates, results exclude stale/missing locations and sort by distance_meters. Only approved carts with active owners are public. Page is framework pagination, not a validated domain field; use positive integers. Page beyond the last page returns an empty data array.

Returns a paginated object with `data`, `current_page`, `per_page` (25), `total`, `last_page`, and navigation links. An empty result is `200` with `data: []`.

## Test cases

| Case | Input / action | Expected |
|---|---|---|
| All supported parameters | Run the full example with matching fixture records | 200 |
| search — wrong type | `["unexpected"]` (keep other fields valid) | 422 |
| search — above maximum | `"xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"` (keep other fields valid) | 422 |
| latitude — missing | `omit field` (keep other fields valid) | 422 |
| latitude — null | `null` (keep other fields valid) | 422 |
| latitude — wrong type | `["unexpected"]` (keep other fields valid) | 422 |
| latitude — below minimum | `-91.0` (keep other fields valid) | 422 |
| latitude — above maximum | `91.0` (keep other fields valid) | 422 |
| longitude — missing | `omit field` (keep other fields valid) | 422 |
| longitude — null | `null` (keep other fields valid) | 422 |
| longitude — wrong type | `["unexpected"]` (keep other fields valid) | 422 |
| longitude — below minimum | `-181.0` (keep other fields valid) | 422 |
| longitude — above maximum | `181.0` (keep other fields valid) | 422 |
| radius_meters — null | `null` (keep other fields valid) | 422 |
| radius_meters — wrong type | `["unexpected"]` (keep other fields valid) | 422 |
| radius_meters — below minimum | `99` (keep other fields valid) | 422 |
| radius_meters — above maximum | `50001` (keep other fields valid) | 422 |

Cases use independent fixtures; do not run destructive examples sequentially against the same records without restoring them. Automated coverage is in `tests/Feature/ApiEndpointMatrixTest.php`, using `tests/Fixtures/api-cases.json`. The behavioral notes also describe scenarios for manual regression testing; they are not a claim that every possible value or combination was executed.

## Accepted parameter variants

These cases keep the remaining fields valid and expect a 2xx response. For user-target reports, target_id changes to 102; password boundary cases update the confirmation too.

| Case | Value / action | Expected |
|---|---|---|
| search — optional omitted | `omit field` | 2xx |
| search — nullable value | `null` | 2xx |
| search — maximum accepted | `"xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"` | 2xx |
| latitude — minimum accepted | `-90` | 2xx |
| latitude — maximum accepted | `90` | 2xx |
| longitude — minimum accepted | `-180` | 2xx |
| longitude — maximum accepted | `180` | 2xx |
| radius_meters — optional omitted | `omit field` | 2xx |
| radius_meters — minimum accepted | `100` | 2xx |
| radius_meters — maximum accepted | `50000` | 2xx |
| page — optional omitted | `omit field` | 2xx |
| page — empty later page | `999` | 2xx |
