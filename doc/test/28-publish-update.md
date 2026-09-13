# Publish Cart Activity Update

`POST /api/v1/owner/carts/{cart}/updates`

[All API tests](README.md) · Authorization: **owner** · Success for the shared fixture: **201**

## Parameters

| Name | Location | Validation / behavior | Example |
|---|---|---|---|
| `cart` | Path | Existing resource ID | `401` |
| `title` | JSON body | `required / string / max:150` | `Lunch is ready` |
| `body` | JSON body | `required / string / max:3000` | `Fresh tacos served until 14:00.` |
| `type` | JSON body | `sometimes / in:announcement,opened,closed,moved,scheduleChanged,photoAdded` | `announcement` |

All supported fields are shown above. Omit optional fields to retain defaults or existing values. Unknown body fields are ignored by these controllers; they do not grant privileges. See the index for headers, fixture IDs, and validation notation.

## Example with all parameters

```json
{
  "title": "Lunch is ready",
  "body": "Fresh tacos served until 14:00.",
  "type": "announcement"
}
```

```bash
curl -i -X POST "$BASE_URL/api/v1/owner/carts/401/updates" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $OWNER_TOKEN" \
  -H 'Content-Type: application/json' \
  --data '{"title":"Lunch is ready","body":"Fresh tacos served until 14:00.","type":"announcement"}'
```

## Expected response and effects

Pending or rejected cart returns 403, as does another owner. Omitted type persists as announcement. The scheduler later creates follower notifications; this endpoint does not synchronously send FCM.

Illustrative response excerpt (IDs/timestamps/tokens vary; additional fields may be present):

```json
{
  "cart_id": 401,
  "title": "Lunch is ready",
  "type": "announcement"
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
| title — missing | `omit field` (keep other fields valid) | 422 |
| title — null | `null` (keep other fields valid) | 422 |
| title — wrong type | `["unexpected"]` (keep other fields valid) | 422 |
| title — above maximum | `string of 151 characters` (keep other fields valid) | 422 |
| body — missing | `omit field` (keep other fields valid) | 422 |
| body — null | `null` (keep other fields valid) | 422 |
| body — wrong type | `["unexpected"]` (keep other fields valid) | 422 |
| body — above maximum | `string of 3001 characters` (keep other fields valid) | 422 |
| type — null | `null` (keep other fields valid) | 422 |
| type — wrong type | `["unexpected"]` (keep other fields valid) | 422 |
| type — invalid choice | `"unsupported-value"` (keep other fields valid) | 422 |

Cases use independent fixtures; do not run destructive examples sequentially against the same records without restoring them. Automated coverage is in `tests/Feature/ApiEndpointMatrixTest.php`, using `tests/Fixtures/api-cases.json`. The behavioral notes also describe scenarios for manual regression testing; they are not a claim that every possible value or combination was executed.

## Accepted parameter variants

These cases keep the remaining fields valid and expect a 2xx response. For user-target reports, target_id changes to 102; password boundary cases update the confirmation too.

| Case | Value / action | Expected |
|---|---|---|
| title — maximum accepted | `string of 150 characters` | 2xx |
| body — maximum accepted | `string of 3000 characters` | 2xx |
| type — optional omitted | `omit field` | 2xx |
| type — allowed announcement | `"announcement"` | 2xx |
| type — allowed opened | `"opened"` | 2xx |
| type — allowed closed | `"closed"` | 2xx |
| type — allowed moved | `"moved"` | 2xx |
| type — allowed scheduleChanged | `"scheduleChanged"` | 2xx |
| type — allowed photoAdded | `"photoAdded"` | 2xx |
