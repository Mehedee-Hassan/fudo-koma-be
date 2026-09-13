# Follow Cart

`PUT /api/v1/carts/{cart}/follow`

[All API tests](README.md) · Authorization: **customer** · Success for the shared fixture: **200**

## Parameters

| Name | Location | Validation / behavior | Example |
|---|---|---|---|
| `cart` | Path | Existing resource ID | `401` |

All supported fields are shown above. Omit optional fields to retain defaults or existing values. Unknown body fields are ignored by these controllers; they do not grant privileges. See the index for headers, fixture IDs, and validation notation.

## Example with all parameters

```bash
curl -i -X PUT "$BASE_URL/api/v1/carts/401/follow" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $CUSTOMER_TOKEN"
```

## Expected response and effects

201 when a follow record is first created, 200 for an existing record (the shared fixture already follows cart 401). Repeating does not duplicate the row. Hidden/unknown carts return 404.

Illustrative response excerpt (IDs/timestamps/tokens vary; additional fields may be present):

```json
{
  "user_id": 101,
  "cart_id": 401,
  "is_following": true
}
```

## Test cases

| Case | Input / action | Expected |
|---|---|---|
| All supported parameters | Run the full example with matching fixture records | 200 |
| No authentication | Omit Authorization | 401 |
| Disabled account | Use a token for a disabled account | 403 |
| Missing resource | Replace a path ID with 999999 | 404 |

Cases use independent fixtures; do not run destructive examples sequentially against the same records without restoring them. Automated coverage is in `tests/Feature/ApiEndpointMatrixTest.php`, using `tests/Fixtures/api-cases.json`. The behavioral notes also describe scenarios for manual regression testing; they are not a claim that every possible value or combination was executed.

## Accepted parameter variants

This endpoint has no additional independently optional or enumerated parameters. Use the full example and access-control cases above.
