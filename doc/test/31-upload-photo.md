# Upload Cart Photo

`POST /api/v1/owner/carts/{cart}/photos`

[All API tests](README.md) · Authorization: **owner** · Success for the shared fixture: **201**

## Parameters

| Name | Location | Validation / behavior | Example |
|---|---|---|---|
| `cart` | Path | Existing resource ID | `401` |
| `photo` | Multipart | `required / image / mimes:jpg,jpeg,png,webp / max:5120` | `@doc/test/fixtures/cart.png` |

All supported fields are shown above. Omit optional fields to retain defaults or existing values. Unknown body fields are ignored by these controllers; they do not grant privileges. See the index for headers, fixture IDs, and validation notation.

## Example with all parameters

```bash
curl -i -X POST "$BASE_URL/api/v1/owner/carts/401/photos" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $OWNER_TOKEN" \
  -F 'photo=@doc/test/fixtures/cart.png'
```

## Expected response and effects

Use multipart/form-data, not JSON; let curl set the multipart boundary. Maximum file size is 5120 KB and maximum photos per cart is 10. Invalid content, SVG, or an eleventh photo returns 422. A proxy/PHP request-size limit can return 413 before Laravel validation. Another owner receives 403.

Illustrative response excerpt (IDs/timestamps/tokens vary; additional fields may be present):

```json
{
  "cart_id": 401,
  "path": "carts/401/<generated>.png",
  "url": "http://localhost:8000/storage/carts/401/<generated>.png"
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
| photo — missing | `omit field` (keep other fields valid) | 422 |
| photo — null | `null` (keep other fields valid) | 422 |

Cases use independent fixtures; do not run destructive examples sequentially against the same records without restoring them. Automated coverage is in `tests/Feature/ApiEndpointMatrixTest.php`, using `tests/Fixtures/api-cases.json`. The behavioral notes also describe scenarios for manual regression testing; they are not a claim that every possible value or combination was executed.

## Accepted parameter variants

This endpoint has no additional independently optional or enumerated parameters. Use the full example and access-control cases above.
