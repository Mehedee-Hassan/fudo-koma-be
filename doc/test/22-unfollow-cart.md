# Unfollow Cart

`DELETE /api/v1/carts/{cart}/follow`

[All API tests](README.md) · Authorization: **customer** · Success for the shared fixture: **204**

## Parameters

| Name | Location | Validation / behavior | Example |
|---|---|---|---|
| `cart` | Path | Existing resource ID | `401` |

All supported fields are shown above. Omit optional fields to retain defaults or existing values. Unknown body fields are ignored by these controllers; they do not grant privileges. See the index for headers, fixture IDs, and validation notation.

## Example with all parameters

```bash
curl -i -X DELETE "$BASE_URL/api/v1/carts/401/follow" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $CUSTOMER_TOKEN"
```

## Expected response and effects

Sets is_following=false and retains the row. Repeating, or unfollowing an existing cart with no prior follow, returns 204. Unknown cart returns 404 through route binding. A hidden existing cart can still be unfollowed.

Response body is empty.

## Test cases

| Case | Input / action | Expected |
|---|---|---|
| All supported parameters | Run the full example with matching fixture records | 204 |
| No authentication | Omit Authorization | 401 |
| Disabled account | Use a token for a disabled account | 403 |
| Missing resource | Replace a path ID with 999999 | 404 |

Cases use independent fixtures; do not run destructive examples sequentially against the same records without restoring them. Automated coverage is in `tests/Feature/ApiEndpointMatrixTest.php`, using `tests/Fixtures/api-cases.json`. The behavioral notes also describe scenarios for manual regression testing; they are not a claim that every possible value or combination was executed.

## Accepted parameter variants

This endpoint has no additional independently optional or enumerated parameters. Use the full example and access-control cases above.
