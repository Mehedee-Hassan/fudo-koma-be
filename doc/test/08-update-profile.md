# Update My Profile

`PATCH /api/v1/me`

[All API tests](README.md) · Authorization: **customer** · Success for the shared fixture: **200**

## Parameters

| Name | Location | Validation / behavior | Example |
|---|---|---|---|
| `name` | JSON body | `required / string / max:100` | `Customer Updated` |

All supported fields are shown above. Omit optional fields to retain defaults or existing values. Unknown body fields are ignored by these controllers; they do not grant privileges. See the index for headers, fixture IDs, and validation notation.

## Example with all parameters

```json
{
  "name": "Customer Updated"
}
```

```bash
curl -i -X PATCH "$BASE_URL/api/v1/me" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $CUSTOMER_TOKEN" \
  -H 'Content-Type: application/json' \
  --data '{"name":"Customer Updated"}'
```

## Expected response and effects

Only name is editable. Email, role, and is_active are ignored on this path.

Illustrative response excerpt (IDs/timestamps/tokens vary; additional fields may be present):

```json
{
  "id": 101,
  "name": "Customer Updated"
}
```

## Test cases

| Case | Input / action | Expected |
|---|---|---|
| All supported parameters | Run the full example with matching fixture records | 200 |
| No authentication | Omit Authorization | 401 |
| Disabled account | Use a token for a disabled account | 403 |
| name — missing | `omit field` (keep other fields valid) | 422 |
| name — null | `null` (keep other fields valid) | 422 |
| name — wrong type | `["unexpected"]` (keep other fields valid) | 422 |
| name — above maximum | `"xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"` (keep other fields valid) | 422 |

Cases use independent fixtures; do not run destructive examples sequentially against the same records without restoring them. Automated coverage is in `tests/Feature/ApiEndpointMatrixTest.php`, using `tests/Fixtures/api-cases.json`. The behavioral notes also describe scenarios for manual regression testing; they are not a claim that every possible value or combination was executed.

## Accepted parameter variants

These cases keep the remaining fields valid and expect a 2xx response. For user-target reports, target_id changes to 102; password boundary cases update the confirmation too.

| Case | Value / action | Expected |
|---|---|---|
| name — maximum accepted | `"xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"` | 2xx |
