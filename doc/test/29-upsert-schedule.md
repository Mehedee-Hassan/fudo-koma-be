# Save Cart Schedule or One-off Stop

`PUT /api/v1/owner/carts/{cart}/schedules`

[All API tests](README.md) · Authorization: **owner** · Success for the shared fixture: **201**

## Parameters

| Name | Location | Validation / behavior | Example |
|---|---|---|---|
| `cart` | Path | Existing resource ID | `401` |
| `day_of_week` | JSON body | `required / integer / between:1,7` | `7` |
| `opens_at` | JSON body | `required / date_format:H:i` | `11:00` |
| `closes_at` | JSON body | `required / date_format:H:i` | `22:00` |
| `timezone` | JSON body | `required / timezone` | `Asia/Tokyo` |
| `address` | JSON body | `nullable / string / max:255` | `Tokyo station plaza` |
| `specific_date` | JSON body | `nullable / date_format:Y-m-d` | `2026-10-04` |
| `is_active` | JSON body | `sometimes / boolean` | `True` |
| `latitude` | JSON body | `nullable / required_with:longitude / numeric / between:-90,90` | `35.6812` |
| `longitude` | JSON body | `nullable / required_with:latitude / numeric / between:-180,180` | `139.7671` |

All supported fields are shown above. Omit optional fields to retain defaults or existing values. Unknown body fields are ignored by these controllers; they do not grant privileges. See the index for headers, fixture IDs, and validation notation.

## Example with all parameters

```json
{
  "day_of_week": 7,
  "opens_at": "11:00",
  "closes_at": "22:00",
  "timezone": "Asia/Tokyo",
  "address": "Tokyo station plaza",
  "specific_date": "2026-10-04",
  "is_active": true,
  "latitude": 35.6812,
  "longitude": 139.7671
}
```

```bash
curl -i -X PUT "$BASE_URL/api/v1/owner/carts/401/schedules" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $OWNER_TOKEN" \
  -H 'Content-Type: application/json' \
  --data '{"day_of_week":7,"opens_at":"11:00","closes_at":"22:00","timezone":"Asia/Tokyo","address":"Tokyo station plaza","specific_date":"2026-10-04","is_active":true,"latitude":35.6812,"longitude":139.7671}'
```

## Expected response and effects

201 for a new cart/weekday/date key, 200 for an existing key. Monday=1 through Sunday=7. Omit specific_date or send null for a weekly entry. Optional coordinates must be supplied together when non-null. Closing before opening is allowed for overnight hours; equal times are currently allowed. Date/weekday consistency is not currently validated.

Illustrative response excerpt (IDs/timestamps/tokens vary; additional fields may be present):

```json
{
  "cart_id": 401,
  "day_of_week": 7,
  "opens_at": "11:00",
  "closes_at": "22:00",
  "specific_date": "2026-10-04"
}
```

## Test cases

| Case | Input / action | Expected |
|---|---|---|
| All supported parameters | Run the full example with matching fixture records | 201 |
| No authentication | Omit Authorization | 401 |
| Disabled account | Use a token for a disabled account | 403 |
| Wrong role | Use a customer token | 403 |
| Missing resource | Replace a path ID with 999999 | 404 |
| day_of_week — missing | `omit field` (keep other fields valid) | 422 |
| day_of_week — null | `null` (keep other fields valid) | 422 |
| day_of_week — wrong type | `["unexpected"]` (keep other fields valid) | 422 |
| day_of_week — below minimum | `0.0` (keep other fields valid) | 422 |
| day_of_week — above maximum | `8.0` (keep other fields valid) | 422 |
| opens_at — missing | `omit field` (keep other fields valid) | 422 |
| opens_at — null | `null` (keep other fields valid) | 422 |
| opens_at — wrong type | `["unexpected"]` (keep other fields valid) | 422 |
| opens_at — invalid time | `"25:70"` (keep other fields valid) | 422 |
| closes_at — missing | `omit field` (keep other fields valid) | 422 |
| closes_at — null | `null` (keep other fields valid) | 422 |
| closes_at — wrong type | `["unexpected"]` (keep other fields valid) | 422 |
| closes_at — invalid time | `"25:70"` (keep other fields valid) | 422 |
| timezone — missing | `omit field` (keep other fields valid) | 422 |
| timezone — null | `null` (keep other fields valid) | 422 |
| timezone — wrong type | `["unexpected"]` (keep other fields valid) | 422 |
| timezone — unknown timezone | `"Invalid/Zone"` (keep other fields valid) | 422 |
| address — wrong type | `["unexpected"]` (keep other fields valid) | 422 |
| address — above maximum | `string of 256 characters` (keep other fields valid) | 422 |
| specific_date — wrong type | `["unexpected"]` (keep other fields valid) | 422 |
| specific_date — invalid calendar date | `"2026-02-30"` (keep other fields valid) | 422 |
| is_active — null | `null` (keep other fields valid) | 422 |
| is_active — invalid boolean | `"true"` (keep other fields valid) | 422 |
| latitude — missing | `omit field` (keep other fields valid) | 422 |
| latitude — wrong type | `["unexpected"]` (keep other fields valid) | 422 |
| latitude — below minimum | `-91.0` (keep other fields valid) | 422 |
| latitude — above maximum | `91.0` (keep other fields valid) | 422 |
| longitude — missing | `omit field` (keep other fields valid) | 422 |
| longitude — wrong type | `["unexpected"]` (keep other fields valid) | 422 |
| longitude — below minimum | `-181.0` (keep other fields valid) | 422 |
| longitude — above maximum | `181.0` (keep other fields valid) | 422 |

Cases use independent fixtures; do not run destructive examples sequentially against the same records without restoring them. Automated coverage is in `tests/Feature/ApiEndpointMatrixTest.php`, using `tests/Fixtures/api-cases.json`. The behavioral notes also describe scenarios for manual regression testing; they are not a claim that every possible value or combination was executed.

## Accepted parameter variants

These cases keep the remaining fields valid and expect a 2xx response. For user-target reports, target_id changes to 102; password boundary cases update the confirmation too.

| Case | Value / action | Expected |
|---|---|---|
| day_of_week — minimum accepted | `1` | 2xx |
| day_of_week — maximum accepted | `7` | 2xx |
| address — optional omitted | `omit field` | 2xx |
| address — nullable value | `null` | 2xx |
| address — maximum accepted | `string of 255 characters` | 2xx |
| specific_date — optional omitted | `omit field` | 2xx |
| specific_date — nullable value | `null` | 2xx |
| is_active — optional omitted | `omit field` | 2xx |
| is_active — allowed true | `true` | 2xx |
| is_active — allowed false | `false` | 2xx |
| is_active — allowed 0 | `0` | 2xx |
| is_active — allowed 1 | `1` | 2xx |
| is_active — allowed "0" | `"0"` | 2xx |
| is_active — allowed "1" | `"1"` | 2xx |
| latitude — minimum accepted | `-90` | 2xx |
| latitude — maximum accepted | `90` | 2xx |
| longitude — minimum accepted | `-180` | 2xx |
| longitude — maximum accepted | `180` | 2xx |
