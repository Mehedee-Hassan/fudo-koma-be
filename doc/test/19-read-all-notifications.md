# Mark All My Notifications Read

`PATCH /api/v1/me/notifications/read-all`

[All API tests](README.md) · Authorization: **customer** · Success for the shared fixture: **204**

## Parameters

No path, query, or body parameters. Identity comes from the bearer token.

All supported fields are shown above. Omit optional fields to retain defaults or existing values. Unknown body fields are ignored by these controllers; they do not grant privileges. See the index for headers, fixture IDs, and validation notation.

## Example with all parameters

```bash
curl -i -X PATCH "$BASE_URL/api/v1/me/notifications/read-all" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $CUSTOMER_TOKEN"
```

## Expected response and effects

Marks unread messages for the current user only. Already-read timestamps are preserved. Empty inbox also returns 204.

Response body is empty.

## Test cases

| Case | Input / action | Expected |
|---|---|---|
| All supported parameters | Run the full example with matching fixture records | 204 |
| No authentication | Omit Authorization | 401 |
| Disabled account | Use a token for a disabled account | 403 |

Cases use independent fixtures; do not run destructive examples sequentially against the same records without restoring them. Automated coverage is in `tests/Feature/ApiEndpointMatrixTest.php`, using `tests/Fixtures/api-cases.json`. The behavioral notes also describe scenarios for manual regression testing; they are not a claim that every possible value or combination was executed.

## Accepted parameter variants

This endpoint has no additional independently optional or enumerated parameters. Use the full example and access-control cases above.
