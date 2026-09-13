# Get Cart Details

`GET /api/v1/carts/{cart}`

[All API tests](README.md) · Authorization: **public** · Success for the shared fixture: **200**

## Parameters

| Name | Location | Validation / behavior | Example |
|---|---|---|---|
| `cart` | Path | Existing resource ID | `401` |

All supported fields are shown above. Omit optional fields to retain defaults or existing values. Unknown body fields are ignored by these controllers; they do not grant privileges. See the index for headers, fixture IDs, and validation notation.

## Example with all parameters

```bash
curl -i -X GET "$BASE_URL/api/v1/carts/401" \
  -H 'Accept: application/json'
```

## Expected response and effects

Unknown, pending, rejected, or disabled-owner carts return 404. Location can be null.

Illustrative response excerpt (IDs/timestamps/tokens vary; additional fields may be present):

```json
{
  "id": 401,
  "name": "Tokyo Taco Club",
  "location": {
    "latitude": 35.6812,
    "longitude": 139.7671
  },
  "photos": [],
  "schedules": []
}
```

## Test cases

| Case | Input / action | Expected |
|---|---|---|
| All supported parameters | Run the full example with matching fixture records | 200 |
| Missing resource | Replace a path ID with 999999 | 404 |

Cases use independent fixtures; do not run destructive examples sequentially against the same records without restoring them. Automated coverage is in `tests/Feature/ApiEndpointMatrixTest.php`, using `tests/Fixtures/api-cases.json`. The behavioral notes also describe scenarios for manual regression testing; they are not a claim that every possible value or combination was executed.

## Accepted parameter variants

This endpoint has no additional independently optional or enumerated parameters. Use the full example and access-control cases above.
