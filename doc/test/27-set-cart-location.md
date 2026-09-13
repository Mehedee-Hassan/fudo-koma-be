# Publish Cart Location

`PUT /api/v1/owner/carts/{cart}/location`

[All API tests](README.md) · Authorization: **owner** · Success for the shared fixture: **200**

## Parameters

| Name | Location | Validation / behavior | Example |
|---|---|---|---|
| `cart` | Path | Existing resource ID | `401` |
| `latitude` | JSON body | `required / numeric / between:-90,90` | `35.6812` |
| `longitude` | JSON body | `required / numeric / between:-180,180` | `139.7671` |
| `address` | JSON body | `nullable / string / max:255` | `Tokyo station, Marunouchi exit` |

All supported fields are shown above. Omit optional fields to retain defaults or existing values. Unknown body fields are ignored by these controllers; they do not grant privileges. See the index for headers, fixture IDs, and validation notation.

## Example with all parameters

```json
{
  "latitude": 35.6812,
  "longitude": 139.7671,
  "address": "Tokyo station, Marunouchi exit"
}
```

```bash
curl -i -X PUT "$BASE_URL/api/v1/owner/carts/401/location" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $OWNER_TOKEN" \
  -H 'Content-Type: application/json' \
  --data '{"latitude":35.6812,"longitude":139.7671,"address":"Tokyo station, Marunouchi exit"}'
```

## Expected response and effects

201 if no location exists, 200 when updating (fixture cart 401 already has one). Identical submissions refresh updated_at. address can be null. Another owner receives 403.

Illustrative response excerpt (IDs/timestamps/tokens vary; additional fields may be present):

```json
{
  "cart_id": 401,
  "latitude": 35.6812,
  "longitude": 139.7671
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
| address — wrong type | `["unexpected"]` (keep other fields valid) | 422 |
| address — above maximum | `string of 256 characters` (keep other fields valid) | 422 |

Cases use independent fixtures; do not run destructive examples sequentially against the same records without restoring them. Automated coverage is in `tests/Feature/ApiEndpointMatrixTest.php`, using `tests/Fixtures/api-cases.json`. The behavioral notes also describe scenarios for manual regression testing; they are not a claim that every possible value or combination was executed.

## Accepted parameter variants

These cases keep the remaining fields valid and expect a 2xx response. For user-target reports, target_id changes to 102; password boundary cases update the confirmation too.

| Case | Value / action | Expected |
|---|---|---|
| latitude — minimum accepted | `-90` | 2xx |
| latitude — maximum accepted | `90` | 2xx |
| longitude — minimum accepted | `-180` | 2xx |
| longitude — maximum accepted | `180` | 2xx |
| address — optional omitted | `omit field` | 2xx |
| address — nullable value | `null` | 2xx |
| address — maximum accepted | `string of 255 characters` | 2xx |
