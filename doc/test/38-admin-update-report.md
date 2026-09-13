# Admin — Resolve or Review Report

`PATCH /api/v1/admin/reports/{report}`

[All API tests](README.md) · Authorization: **admin** · Success for the shared fixture: **200**

## Parameters

| Name | Location | Validation / behavior | Example |
|---|---|---|---|
| `report` | Path | Existing resource ID | `801` |
| `status` | JSON body | `required / in:open,reviewing,resolved,dismissed` | `resolved` |
| `resolution_note` | JSON body | `nullable / string / max:2000` | `Verified the location with the owner.` |

All supported fields are shown above. Omit optional fields to retain defaults or existing values. Unknown body fields are ignored by these controllers; they do not grant privileges. See the index for headers, fixture IDs, and validation notation.

## Example with all parameters

```json
{
  "status": "resolved",
  "resolution_note": "Verified the location with the owner."
}
```

```bash
curl -i -X PATCH "$BASE_URL/api/v1/admin/reports/801" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H 'Content-Type: application/json' \
  --data '{"status":"resolved","resolution_note":"Verified the location with the owner."}'
```

## Expected response and effects

resolved/dismissed set resolved_at; open/reviewing clear it. resolved_by comes from the admin token, not the request. Unknown report returns 404.

Illustrative response excerpt (IDs/timestamps/tokens vary; additional fields may be present):

```json
{
  "id": 801,
  "status": "resolved",
  "resolved_by": 301,
  "resolution_note": "Verified the location with the owner."
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
| status — missing | `omit field` (keep other fields valid) | 422 |
| status — null | `null` (keep other fields valid) | 422 |
| status — wrong type | `["unexpected"]` (keep other fields valid) | 422 |
| status — invalid choice | `"unsupported-value"` (keep other fields valid) | 422 |
| resolution_note — wrong type | `["unexpected"]` (keep other fields valid) | 422 |
| resolution_note — above maximum | `string of 2001 characters` (keep other fields valid) | 422 |

Cases use independent fixtures; do not run destructive examples sequentially against the same records without restoring them. Automated coverage is in `tests/Feature/ApiEndpointMatrixTest.php`, using `tests/Fixtures/api-cases.json`. The behavioral notes also describe scenarios for manual regression testing; they are not a claim that every possible value or combination was executed.

## Accepted parameter variants

These cases keep the remaining fields valid and expect a 2xx response. For user-target reports, target_id changes to 102; password boundary cases update the confirmation too.

| Case | Value / action | Expected |
|---|---|---|
| status — allowed open | `"open"` | 2xx |
| status — allowed reviewing | `"reviewing"` | 2xx |
| status — allowed resolved | `"resolved"` | 2xx |
| status — allowed dismissed | `"dismissed"` | 2xx |
| resolution_note — optional omitted | `omit field` | 2xx |
| resolution_note — nullable value | `null` | 2xx |
| resolution_note — maximum accepted | `string of 2000 characters` | 2xx |
