# Report a Cart or User

`POST /api/v1/reports`

[All API tests](README.md) · Authorization: **customer** · Success for the shared fixture: **201**

## Parameters

| Name | Location | Validation / behavior | Example |
|---|---|---|---|
| `target_type` | JSON body | `required / in:cart,user` | `cart` |
| `target_id` | JSON body | `required / integer` | `401` |
| `reason` | JSON body | `required / in:spam,fakeListing,wrongLocation,offensive,other` | `wrongLocation` |
| `note` | JSON body | `nullable / string / max:2000` | `The cart is no longer at this location.` |

All supported fields are shown above. Omit optional fields to retain defaults or existing values. Unknown body fields are ignored by these controllers; they do not grant privileges. See the index for headers, fixture IDs, and validation notation.

## Example with all parameters

```json
{
  "target_type": "cart",
  "target_id": 401,
  "reason": "wrongLocation",
  "note": "The cart is no longer at this location."
}
```

```bash
curl -i -X POST "$BASE_URL/api/v1/reports" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $CUSTOMER_TOKEN" \
  -H 'Content-Type: application/json' \
  --data '{"target_type":"cart","target_id":401,"reason":"wrongLocation","note":"The cart is no longer at this location."}'
```

## Expected response and effects

target_id must exist in the table selected by target_type; unknown target returns 422. For a user report use target_type=user and target_id=102. Reporter identity and initial open status are server-owned. Creation may omit database-default status in the immediate response; the persisted report is open.

Illustrative response excerpt (IDs/timestamps/tokens vary; additional fields may be present):

```json
{
  "target_type": "cart",
  "target_id": 401,
  "reporter_id": 101
}
```

## Test cases

| Case | Input / action | Expected |
|---|---|---|
| All supported parameters | Run the full example with matching fixture records | 201 |
| No authentication | Omit Authorization | 401 |
| Disabled account | Use a token for a disabled account | 403 |
| target_type — missing | `omit field` (keep other fields valid) | 422 |
| target_type — null | `null` (keep other fields valid) | 422 |
| target_type — wrong type | `["unexpected"]` (keep other fields valid) | 422 |
| target_type — invalid choice | `"unsupported-value"` (keep other fields valid) | 422 |
| target_id — missing | `omit field` (keep other fields valid) | 422 |
| target_id — null | `null` (keep other fields valid) | 422 |
| target_id — wrong type | `["unexpected"]` (keep other fields valid) | 422 |
| reason — missing | `omit field` (keep other fields valid) | 422 |
| reason — null | `null` (keep other fields valid) | 422 |
| reason — wrong type | `["unexpected"]` (keep other fields valid) | 422 |
| reason — invalid choice | `"unsupported-value"` (keep other fields valid) | 422 |
| note — wrong type | `["unexpected"]` (keep other fields valid) | 422 |
| note — above maximum | `string of 2001 characters` (keep other fields valid) | 422 |
| target_id — nonexistent target | `999999` (keep other fields valid) | 422 |

Cases use independent fixtures; do not run destructive examples sequentially against the same records without restoring them. Automated coverage is in `tests/Feature/ApiEndpointMatrixTest.php`, using `tests/Fixtures/api-cases.json`. The behavioral notes also describe scenarios for manual regression testing; they are not a claim that every possible value or combination was executed.

## Accepted parameter variants

These cases keep the remaining fields valid and expect a 2xx response. For user-target reports, target_id changes to 102; password boundary cases update the confirmation too.

| Case | Value / action | Expected |
|---|---|---|
| target_type — allowed cart | `"cart"` | 2xx |
| target_type — allowed user | `"user"` | 2xx |
| reason — allowed spam | `"spam"` | 2xx |
| reason — allowed fakeListing | `"fakeListing"` | 2xx |
| reason — allowed wrongLocation | `"wrongLocation"` | 2xx |
| reason — allowed offensive | `"offensive"` | 2xx |
| reason — allowed other | `"other"` | 2xx |
| note — optional omitted | `omit field` | 2xx |
| note — nullable value | `null` | 2xx |
| note — maximum accepted | `string of 2000 characters` | 2xx |
