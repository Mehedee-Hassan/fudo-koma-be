# Update Food Cart

`PATCH /api/v1/owner/carts/{cart}`

[All API tests](README.md) · Authorization: **owner** · Success for the shared fixture: **200**

## Parameters

| Name | Location | Validation / behavior | Example |
|---|---|---|---|
| `cart` | Path | Existing resource ID | `401` |
| `name` | JSON body | `sometimes / string / max:100` | `Tokyo Taco Club Updated` |
| `description` | JSON body | `nullable / string / max:3000` | `Fresh tacos near Tokyo station.` |
| `cuisine` | JSON body | `nullable / string / max:100` | `Mexican` |
| `status` | JSON body | `sometimes / in:open,closed` | `open` |

All supported fields are shown above. Omit optional fields to retain defaults or existing values. Unknown body fields are ignored by these controllers; they do not grant privileges. See the index for headers, fixture IDs, and validation notation.

## Example with all parameters

```json
{
  "name": "Tokyo Taco Club Updated",
  "description": "Fresh tacos near Tokyo station.",
  "cuisine": "Mexican",
  "status": "open"
}
```

```bash
curl -i -X PATCH "$BASE_URL/api/v1/owner/carts/401" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $OWNER_TOKEN" \
  -H 'Content-Type: application/json' \
  --data '{"name":"Tokyo Taco Club Updated","description":"Fresh tacos near Tokyo station.","cuisine":"Mexican","status":"open"}'
```

## Expected response and effects

All fields are optional. Nullable description/cuisine can be cleared with null. Another owner receives 403; unknown cart receives 404. Administrators can edit any cart.

Illustrative response excerpt (IDs/timestamps/tokens vary; additional fields may be present):

```json
{
  "id": 401,
  "name": "Tokyo Taco Club Updated"
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
| name — null | `null` (keep other fields valid) | 422 |
| name — wrong type | `["unexpected"]` (keep other fields valid) | 422 |
| name — above maximum | `"xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"` (keep other fields valid) | 422 |
| description — wrong type | `["unexpected"]` (keep other fields valid) | 422 |
| description — above maximum | `string of 3001 characters` (keep other fields valid) | 422 |
| cuisine — wrong type | `["unexpected"]` (keep other fields valid) | 422 |
| cuisine — above maximum | `"xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"` (keep other fields valid) | 422 |
| status — null | `null` (keep other fields valid) | 422 |
| status — wrong type | `["unexpected"]` (keep other fields valid) | 422 |
| status — invalid choice | `"unsupported-value"` (keep other fields valid) | 422 |

Cases use independent fixtures; do not run destructive examples sequentially against the same records without restoring them. Automated coverage is in `tests/Feature/ApiEndpointMatrixTest.php`, using `tests/Fixtures/api-cases.json`. The behavioral notes also describe scenarios for manual regression testing; they are not a claim that every possible value or combination was executed.

## Accepted parameter variants

These cases keep the remaining fields valid and expect a 2xx response. For user-target reports, target_id changes to 102; password boundary cases update the confirmation too.

| Case | Value / action | Expected |
|---|---|---|
| name — optional omitted | `omit field` | 2xx |
| name — maximum accepted | `"xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"` | 2xx |
| description — optional omitted | `omit field` | 2xx |
| description — nullable value | `null` | 2xx |
| description — maximum accepted | `string of 3000 characters` | 2xx |
| cuisine — optional omitted | `omit field` | 2xx |
| cuisine — nullable value | `null` | 2xx |
| cuisine — maximum accepted | `"xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"` | 2xx |
| status — optional omitted | `omit field` | 2xx |
| status — allowed open | `"open"` | 2xx |
| status — allowed closed | `"closed"` | 2xx |
