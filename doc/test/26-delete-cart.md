# Delete Food Cart

`DELETE /api/v1/owner/carts/{cart}`

[All API tests](README.md) · Authorization: **owner** · Success for the shared fixture: **204**

## Parameters

| Name | Location | Validation / behavior | Example |
|---|---|---|---|
| `cart` | Path | Existing resource ID | `401` |

All supported fields are shown above. Omit optional fields to retain defaults or existing values. Unknown body fields are ignored by these controllers; they do not grant privileges. See the index for headers, fixture IDs, and validation notation.

## Example with all parameters

```bash
curl -i -X DELETE "$BASE_URL/api/v1/owner/carts/401" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $OWNER_TOKEN"
```

## Expected response and effects

Deletes the cart, related records, and photo files. Use disposable fixture data. Another owner receives 403. Repeating after deletion returns 404.

Response body is empty.

## Test cases

| Case | Input / action | Expected |
|---|---|---|
| All supported parameters | Run the full example with matching fixture records | 204 |
| No authentication | Omit Authorization | 401 |
| Disabled account | Use a token for a disabled account | 403 |
| Wrong role | Use a customer token | 403 |
| Missing resource | Replace a path ID with 999999 | 404 |

Cases use independent fixtures; do not run destructive examples sequentially against the same records without restoring them. Automated coverage is in `tests/Feature/ApiEndpointMatrixTest.php`, using `tests/Fixtures/api-cases.json`. The behavioral notes also describe scenarios for manual regression testing; they are not a claim that every possible value or combination was executed.

## Accepted parameter variants

This endpoint has no additional independently optional or enumerated parameters. Use the full example and access-control cases above.
