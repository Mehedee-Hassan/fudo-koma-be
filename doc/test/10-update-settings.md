# Update My Notification Settings

`PATCH /api/v1/me/settings`

[All API tests](README.md) · Authorization: **customer** · Success for the shared fixture: **200**

## Parameters

| Name | Location | Validation / behavior | Example |
|---|---|---|---|
| `push_enabled` | JSON body | `sometimes / boolean` | `True` |
| `nearby_enabled` | JSON body | `sometimes / boolean` | `True` |
| `updates_enabled` | JSON body | `sometimes / boolean` | `True` |
| `radius_meters` | JSON body | `sometimes / integer / between:100,50000` | `5000` |

All supported fields are shown above. Omit optional fields to retain defaults or existing values. Unknown body fields are ignored by these controllers; they do not grant privileges. See the index for headers, fixture IDs, and validation notation.

## Example with all parameters

```json
{
  "push_enabled": true,
  "nearby_enabled": true,
  "updates_enabled": true,
  "radius_meters": 5000
}
```

```bash
curl -i -X PATCH "$BASE_URL/api/v1/me/settings" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $CUSTOMER_TOKEN" \
  -H 'Content-Type: application/json' \
  --data '{"push_enabled":true,"nearby_enabled":true,"updates_enabled":true,"radius_meters":5000}'
```

## Expected response and effects

200 when the settings row already exists; 201 if this request creates it. Fields are independently optional; omitted settings keep their values. JSON true/false or 0/1 are accepted; strings "true" and "false" are not Laravel boolean values.

Illustrative response excerpt (IDs/timestamps/tokens vary; additional fields may be present):

```json
{
  "user_id": 101,
  "radius_meters": 5000
}
```

## Test cases

| Case | Input / action | Expected |
|---|---|---|
| All supported parameters | Run the full example with matching fixture records | 200 |
| No authentication | Omit Authorization | 401 |
| Disabled account | Use a token for a disabled account | 403 |
| push_enabled — null | `null` (keep other fields valid) | 422 |
| push_enabled — invalid boolean | `"true"` (keep other fields valid) | 422 |
| nearby_enabled — null | `null` (keep other fields valid) | 422 |
| nearby_enabled — invalid boolean | `"true"` (keep other fields valid) | 422 |
| updates_enabled — null | `null` (keep other fields valid) | 422 |
| updates_enabled — invalid boolean | `"true"` (keep other fields valid) | 422 |
| radius_meters — null | `null` (keep other fields valid) | 422 |
| radius_meters — wrong type | `["unexpected"]` (keep other fields valid) | 422 |
| radius_meters — below minimum | `99.0` (keep other fields valid) | 422 |
| radius_meters — above maximum | `50001.0` (keep other fields valid) | 422 |

Cases use independent fixtures; do not run destructive examples sequentially against the same records without restoring them. Automated coverage is in `tests/Feature/ApiEndpointMatrixTest.php`, using `tests/Fixtures/api-cases.json`. The behavioral notes also describe scenarios for manual regression testing; they are not a claim that every possible value or combination was executed.

## Accepted parameter variants

These cases keep the remaining fields valid and expect a 2xx response. For user-target reports, target_id changes to 102; password boundary cases update the confirmation too.

| Case | Value / action | Expected |
|---|---|---|
| push_enabled — optional omitted | `omit field` | 2xx |
| push_enabled — allowed true | `true` | 2xx |
| push_enabled — allowed false | `false` | 2xx |
| push_enabled — allowed 0 | `0` | 2xx |
| push_enabled — allowed 1 | `1` | 2xx |
| push_enabled — allowed "0" | `"0"` | 2xx |
| push_enabled — allowed "1" | `"1"` | 2xx |
| nearby_enabled — optional omitted | `omit field` | 2xx |
| nearby_enabled — allowed true | `true` | 2xx |
| nearby_enabled — allowed false | `false` | 2xx |
| nearby_enabled — allowed 0 | `0` | 2xx |
| nearby_enabled — allowed 1 | `1` | 2xx |
| nearby_enabled — allowed "0" | `"0"` | 2xx |
| nearby_enabled — allowed "1" | `"1"` | 2xx |
| updates_enabled — optional omitted | `omit field` | 2xx |
| updates_enabled — allowed true | `true` | 2xx |
| updates_enabled — allowed false | `false` | 2xx |
| updates_enabled — allowed 0 | `0` | 2xx |
| updates_enabled — allowed 1 | `1` | 2xx |
| updates_enabled — allowed "0" | `"0"` | 2xx |
| updates_enabled — allowed "1" | `"1"` | 2xx |
| radius_meters — optional omitted | `omit field` | 2xx |
| radius_meters — minimum accepted | `100` | 2xx |
| radius_meters — maximum accepted | `50000` | 2xx |
