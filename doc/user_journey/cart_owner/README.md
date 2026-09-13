# Cart Owner Journey — Launch and Operate a Food Cart

[All journeys](../README.md) · Role after approval: `owner` · Example person: **Kenji Sato** (`kenji@example.test`).

Kenji creates **Tokyo Taco Club** and publishes operating information. The administrator must promote his account and approve his cart; neither action can be self-authorized through the owner API.

```bash
export BASE_URL=http://localhost:8000
```

## 1. Register an account

Kenji starts with normal registration. There is no public privileged-role registration.

**API:** `POST /api/v1/auth/register`

```bash
curl -i -X POST "$BASE_URL/api/v1/auth/register" \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  --data '{"name":"Kenji Sato","email":"kenji@example.test","password":"Journey-Kenji-2026!","password_confirmation":"Journey-Kenji-2026!"}'
```

**Expected:** 201 with a user ID and token; role is customer. Record the actual user ID and give it to Mika for promotion.

```json
{"user":{"id":201,"name":"Kenji Sato","role":"customer"},"token":"<initial-account-token>"}
```

## 2. Obtain owner access — administrator handoff

Mika performs `PATCH /api/v1/admin/users/{Kenji's ID}` with `{"role":"owner","is_active":true}` using **her** admin token. See [administrator step 3](../admin/README.md). Kenji must wait for approval before calling `/owner/*`; customer-role calls return 403. Role is loaded from the account on each request, so an existing valid token sees the promotion; signing in again below is also supported.

## 3. Sign in and verify the role

Kenji signs in with the promoted account.

**API:** `POST /api/v1/auth/login`

```bash
curl -i -X POST "$BASE_URL/api/v1/auth/login" \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  --data '{"email":"kenji@example.test","password":"Journey-Kenji-2026!"}'
```

**Expected:** 200; `user.role` must be owner.

```bash
export OWNER_TOKEN='<token returned by login>'
```
```bash
curl -i -X GET "$BASE_URL/api/v1/me" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $OWNER_TOKEN"
```

**Expected:** 200 with Kenji’s profile and `role: owner`.

## 4. Create the food cart

Create the cart closed while preparing its details. The owner ID is taken from the token.

**API:** `POST /api/v1/owner/carts`

```bash
curl -i -X POST "$BASE_URL/api/v1/owner/carts" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $OWNER_TOKEN" \
  -H 'Content-Type: application/json' \
  --data '{"name":"Tokyo Taco Club","description":"Fresh tacos and seasonal salsa near Tokyo station.","cuisine":"Mexican","status":"closed"}'
```

**Expected:** 201 with `id`, `owner_id`, and `moderation_status: pending`. It is not publicly visible yet.

```bash
export CART_ID=401 # Replace with the ID returned by creation.
```

## 5. Review owned carts

Kenji can see his own pending or rejected carts here, unlike public discovery.

**API:** `GET /api/v1/owner/carts?page=1`

```bash
curl -i -X GET "$BASE_URL/api/v1/owner/carts?page=1" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $OWNER_TOKEN"
```

**Expected:** 200 with Kenji’s carts and related locations/photos/schedules. Save any IDs needed when resuming later.

## 6. Publish the cart position

Send the current GPS position and readable address.

**API:** `PUT /api/v1/owner/carts/$CART_ID/location`

```bash
curl -i -X PUT "$BASE_URL/api/v1/owner/carts/$CART_ID/location" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $OWNER_TOKEN" \
  -H 'Content-Type: application/json' \
  --data '{"latitude":35.6812,"longitude":139.7671,"address":"Tokyo station, Marunouchi exit"}'
```

**Expected:** 201 on first location, 200 on later updates. Refresh periodically to prevent the marker becoming stale.

## 7. Add a photo

**API:** `POST /api/v1/owner/carts/{cart}/photos` (multipart).

The repository includes a tiny test PNG. Replace it with a real food-cart image for a useful listing. Do not manually set a JSON Content-Type for multipart uploads.

```bash
curl -i -X POST "$BASE_URL/api/v1/owner/carts/$CART_ID/photos"   -H 'Accept: application/json'   -H "Authorization: Bearer $OWNER_TOKEN"   -F 'photo=@doc/test/fixtures/cart.png'
```

**Expected:** 201 with `id`, `path`, and public `url`. JPEG/PNG/WebP only, up to 5 MB each and 10 photos per cart.

```bash
export PHOTO_ID=901 # Replace with the returned photo ID.
```

## 8. Publish the weekly schedule

Set a Monday serving window. This does not automatically change open/closed status.

**API:** `PUT /api/v1/owner/carts/$CART_ID/schedules`

```bash
curl -i -X PUT "$BASE_URL/api/v1/owner/carts/$CART_ID/schedules" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $OWNER_TOKEN" \
  -H 'Content-Type: application/json' \
  --data '{"day_of_week":1,"opens_at":"11:00","closes_at":"14:00","timezone":"Asia/Tokyo","address":"Tokyo station, Marunouchi exit","specific_date":null,"is_active":true,"latitude":35.6812,"longitude":139.7671}'
```

**Expected:** 201 for a new cart/weekday/date combination, then 200 when updating that entry. Record the returned schedule ID.

```bash
export WEEKLY_SCHEDULE_ID=501 # Replace with the returned ID.
```

## 9. Add a one-off stop (optional)

Publish an upcoming Sunday stop; replace the example date when appropriate. day_of_week uses Monday=1 through Sunday=7.

**API:** `PUT /api/v1/owner/carts/$CART_ID/schedules`

```bash
curl -i -X PUT "$BASE_URL/api/v1/owner/carts/$CART_ID/schedules" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $OWNER_TOKEN" \
  -H 'Content-Type: application/json' \
  --data '{"day_of_week":7,"opens_at":"12:00","closes_at":"17:00","timezone":"Asia/Tokyo","address":"Tokyo station plaza","specific_date":"2026-10-04","is_active":true,"latitude":35.6812,"longitude":139.7671}'
```

**Expected:** 201 for the new dated entry. Save its returned ID separately from the weekly entry.

```bash
export ONE_OFF_SCHEDULE_ID=502 # Replace with the returned dated-entry ID.
```

## 10. Wait for cart approval — administrator handoff

Mika reviews the listing using `GET /api/v1/admin/carts` and approves it with `PATCH /api/v1/admin/carts/{cart}`. Kenji cannot approve the cart himself. Once approved, the public detail endpoint becomes available:

```bash
curl -i -X GET "$BASE_URL/api/v1/carts/$CART_ID" \
  -H 'Accept: application/json'
```

**Expected:** 200 after approval; 404 while pending/rejected. Public nearby searches also require a fresh location.

## 11. Open for service

Kenji explicitly sets the cart open. For the shared demonstration, let Aiko finish following the cart before publishing the next update.

**API:** `PATCH /api/v1/owner/carts/$CART_ID`

```bash
curl -i -X PATCH "$BASE_URL/api/v1/owner/carts/$CART_ID" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $OWNER_TOKEN" \
  -H 'Content-Type: application/json' \
  --data '{"name":"Tokyo Taco Club","description":"Fresh tacos and seasonal salsa near Tokyo station.","cuisine":"Mexican","status":"open"}'
```

**Expected:** 200 with status=open. This changes cart state but does not itself notify followers.

## 12. Announce opening

Publish one activity update after Aiko has followed.

**API:** `POST /api/v1/owner/carts/$CART_ID/updates`

```bash
curl -i -X POST "$BASE_URL/api/v1/owner/carts/$CART_ID/updates" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $OWNER_TOKEN" \
  -H 'Content-Type: application/json' \
  --data '{"title":"Lunch is ready","body":"We are serving fresh tacos at the Marunouchi exit until 14:00.","type":"opened"}'
```

**Expected:** 201 with the new update. The scheduler later generates notifications for eligible followers. Pending/rejected carts cannot publish updates (403).

## 13. Move the cart and announce the new stop

Publish the changed GPS reading first.

**API:** `PUT /api/v1/owner/carts/$CART_ID/location`

```bash
curl -i -X PUT "$BASE_URL/api/v1/owner/carts/$CART_ID/location" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $OWNER_TOKEN" \
  -H 'Content-Type: application/json' \
  --data '{"latitude":35.682,"longitude":139.768,"address":"Tokyo station plaza"}'
```

**Expected:** 200, with refreshed location timestamp.
```bash
curl -i -X POST "$BASE_URL/api/v1/owner/carts/$CART_ID/updates" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $OWNER_TOKEN" \
  -H 'Content-Type: application/json' \
  --data '{"title":"Find us at the plaza","body":"We moved to Tokyo station plaza for the afternoon.","type":"moved"}'
```

**Expected:** 201. The client should avoid publishing an announcement for every background GPS sample.

## 14. Check the public activity feed

Confirm customers can see the announcements.

**API:** `GET /api/v1/carts/$CART_ID/updates?page=1`

```bash
curl -i -X GET "$BASE_URL/api/v1/carts/$CART_ID/updates?page=1" \
  -H 'Accept: application/json'
```

**Expected:** 200 with newest activity first. This verifies publication, not actual push receipt.

## 15. Close for the day

Set status closed when service ends; a schedule alone will not do it.

**API:** `PATCH /api/v1/owner/carts/$CART_ID`

```bash
curl -i -X PATCH "$BASE_URL/api/v1/owner/carts/$CART_ID" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $OWNER_TOKEN" \
  -H 'Content-Type: application/json' \
  --data '{"status":"closed"}'
```

**Expected:** 200; the cart is no longer eligible for newly generated nearby alerts.
```bash
curl -i -X POST "$BASE_URL/api/v1/owner/carts/$CART_ID/updates" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $OWNER_TOKEN" \
  -H 'Content-Type: application/json' \
  --data '{"title":"See you next time","body":"We are closed for today. Thanks for stopping by!","type":"closed"}'
```

**Expected:** 201. Eligible followers receive an update after scheduled processing.

## 16. Maintain the listing (optional)

Delete only records you intend to remove. These calls are cleanup branches, not required at the end of every day.
```bash
curl -i -X DELETE "$BASE_URL/api/v1/owner/carts/$CART_ID/schedules/$ONE_OFF_SCHEDULE_ID" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $OWNER_TOKEN"
```

**Expected:** 204; only the dated stop is removed.
```bash
curl -i -X DELETE "$BASE_URL/api/v1/owner/carts/$CART_ID/photos/$PHOTO_ID" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $OWNER_TOKEN"
```

**Expected:** 204; the photo row and file are removed.
```bash
curl -i -X DELETE "$BASE_URL/api/v1/owner/carts/$CART_ID" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $OWNER_TOKEN"
```

**Expected:** 204; permanently removes the cart and related records/files. Use closing status for a temporary closure. After deletion the remaining cart URLs return 404.

## 17. Sign out

Revoke the current API token when finished.

**API:** `POST /api/v1/auth/logout`

```bash
curl -i -X POST "$BASE_URL/api/v1/auth/logout" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $OWNER_TOKEN"
```

**Expected:** 204. If this owner session registered a device through the shared account API, remove that device first. Clear client credentials afterward.

## Access boundaries

Another owner’s existing cart returns 403 on owner writes. A photo/schedule belonging to a different cart returns 404. Owners cannot change is_featured, moderation_status, another account’s role, or notification recipients. Customer account/preferences/device endpoints also work for owner accounts.
