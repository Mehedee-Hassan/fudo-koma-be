# Publish My Location

`PUT /api/v1/me/location`

[All API tests](README.md) · Authorization: **customer** · Success for the shared fixture: **201**

## Parameters

| Name | Location | Validation / behavior | Example |
|---|---|---|---|
| `latitude` | JSON body | `required / numeric / between:-90,90` | `35.6812` |
| `longitude` | JSON body | `required / numeric / between:-180,180` | `139.7671` |

All supported fields are shown above. Omit optional fields to retain defaults or existing values. Unknown body fields are ignored by these controllers; they do not grant privileges. See the index for headers, fixture IDs, and validation notation.

## Example with all parameters

```json
{
  "latitude": 35.6812,
  "longitude": 139.7671
}
```

```bash
curl -i -X PUT "$BASE_URL/api/v1/me/location" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $CUSTOMER_TOKEN" \
  -H 'Content-Type: application/json' \
  --data '{"latitude":35.6812,"longitude":139.7671}'
```

## Expected response and effects

201 on first creation, 200 on later updates. The server sets updated_at, including repeated identical positions. Client timestamps and user_id are ignored.

Illustrative response excerpt (IDs/timestamps/tokens vary; additional fields may be present):

```json
{
  "user_id": 101,
  "latitude": 35.6812,
  "longitude": 139.7671
}
```

## Test cases

| Case | Input / action | Expected |
|---|---|---|
| All supported parameters | Run the full example with matching fixture records | 201 |
| No authentication | Omit Authorization | 401 |
| Disabled account | Use a token for a disabled account | 403 |
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

Cases use independent fixtures; do not run destructive examples sequentially against the same records without restoring them. Automated coverage is in `tests/Feature/ApiEndpointMatrixTest.php`, using `tests/Fixtures/api-cases.json`. The behavioral notes also describe scenarios for manual regression testing; they are not a claim that every possible value or combination was executed.

## Accepted parameter variants

These cases keep the remaining fields valid and expect a 2xx response. For user-target reports, target_id changes to 102; password boundary cases update the confirmation too.

| Case | Value / action | Expected |
|---|---|---|
| latitude — minimum accepted | `-90` | 2xx |
| latitude — maximum accepted | `90` | 2xx |
| longitude — minimum accepted | `-180` | 2xx |
| longitude — maximum accepted | `180` | 2xx |
