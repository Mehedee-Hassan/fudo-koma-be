# Normal User Journey — Discover and Follow a Food Cart

[All journeys](../README.md) · Role: `customer` · Example person: **Aiko Tanaka** (`aiko@example.test`).

Aiko wants to find nearby lunch, follow Tokyo Taco Club, and receive updates. Start after Kenji's cart has been approved and has a fresh location, as described in the [owner journey](../cart_owner/README.md).

```bash
export BASE_URL=http://localhost:8000
```

## 1. Explore without an account

Aiko opens Explore. Find approved carts within 5 km of Tokyo station. No token is required.

**API:** `GET /api/v1/carts?search=Tokyo&latitude=35.6812&longitude=139.7671&radius_meters=5000&page=1`

```bash
curl -i -X GET "$BASE_URL/api/v1/carts?search=Tokyo&latitude=35.6812&longitude=139.7671&radius_meters=5000&page=1" \
  -H 'Accept: application/json'
```

**Expected:** 200 with a paginated `data` array, sorted by distance. If empty, check cart approval and location freshness; general catalog browsing uses `/carts?page=1`.

Example response excerpt:

```json
{"data":[{"id":401,"name":"Tokyo Taco Club","status":"open","distance_meters":0}],"current_page":1,"total":1}
```

Copy the selected cart's actual ID (401 is illustrative):

```bash
export CART_ID=401
```

## 2. View the cart

Aiko taps the marker to see the cart details, photos, serving schedule, and published location.

**API:** `GET /api/v1/carts/$CART_ID`

```bash
curl -i -X GET "$BASE_URL/api/v1/carts/$CART_ID" \
  -H 'Accept: application/json'
```

**Expected:** 200 with the cart and its `location`, `photos`, and `schedules`. A missing location may be null; a hidden cart returns 404.

## 3. Register

Aiko creates an account so her follows persist across sessions.

**API:** `POST /api/v1/auth/register`

```bash
curl -i -X POST "$BASE_URL/api/v1/auth/register" \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  --data '{"name":"Aiko Tanaka","email":"aiko@example.test","password":"Journey-Aiko-2026!","password_confirmation":"Journey-Aiko-2026!"}'
```

**Expected:** 201 with `user` and `token`; `user.role` is customer. If this email already exists, use login below instead.

```json
{"user":{"id":101,"name":"Aiko Tanaka","email":"aiko@example.test","role":"customer"},"token":"<customer-bearer-token>"}
```

```bash
export CUSTOMER_TOKEN='<token returned by registration or login>'
```

## 4. Sign in on a later visit (alternative to registration)

Use this when the account already exists. Replace CUSTOMER_TOKEN with the newly returned token; registration already provides a token, so this is not required immediately after step 3.

**API:** `POST /api/v1/auth/login`

```bash
curl -i -X POST "$BASE_URL/api/v1/auth/login" \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  --data '{"email":"aiko@example.test","password":"Journey-Aiko-2026!"}'
```

**Expected:** 200 with `user` and a new `token`. Wrong credentials return 422.

## 5. Restore the profile

The app checks a securely stored token on startup.

**API:** `GET /api/v1/me`

```bash
curl -i -X GET "$BASE_URL/api/v1/me" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $CUSTOMER_TOKEN"
```

**Expected:** 200 with Aiko’s account; password/token fields are not included. On 401, return to login.

## 6. Update the display name (optional)

Aiko shortens the name shown in her profile.

**API:** `PATCH /api/v1/me`

```bash
curl -i -X PATCH "$BASE_URL/api/v1/me" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $CUSTOMER_TOKEN" \
  -H 'Content-Type: application/json' \
  --data '{"name":"Aiko T."}'
```

**Expected:** 200; only the name changes. Role and email are not editable through this endpoint.

## 7. Read and choose notification preferences

Show the current settings first.

**API:** `GET /api/v1/me/settings`

```bash
curl -i -X GET "$BASE_URL/api/v1/me/settings" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $CUSTOMER_TOKEN"
```

**Expected:** 200; defaults enable push, nearby, and update alerts with a 5000-meter radius.
```bash
curl -i -X PATCH "$BASE_URL/api/v1/me/settings" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $CUSTOMER_TOKEN" \
  -H 'Content-Type: application/json' \
  --data '{"push_enabled":true,"nearby_enabled":true,"updates_enabled":true,"radius_meters":5000}'
```

**Expected:** 200 for the existing row. Each setting can be changed independently. Push opt-in alone does not register a device.

## 8. Share the current position

After Aiko grants permission in the client, publish the GPS reading. Refresh periodically while sharing, even if unchanged.

**API:** `PUT /api/v1/me/location`

```bash
curl -i -X PUT "$BASE_URL/api/v1/me/location" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $CUSTOMER_TOKEN" \
  -H 'Content-Type: application/json' \
  --data '{"latitude":35.6812,"longitude":139.7671}'
```

**Expected:** 201 on first position, then 200 on updates. The server controls `updated_at`.

## 9. Register a push device (optional)

The app obtains an FCM token and sends it here. The example is synthetic: use it only with PUSH_DRIVER=disabled, or replace it with a real FCM token for delivery testing.

**API:** `POST /api/v1/me/devices`

```bash
curl -i -X POST "$BASE_URL/api/v1/me/devices" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $CUSTOMER_TOKEN" \
  -H 'Content-Type: application/json' \
  --data '{"token":"example-aiko-fcm-token-replace-before-live-push","platform":"android"}'
```

**Expected:** 201 for a new device, 200 if already registered. Save the returned `id`; the raw token is hidden.

```bash
export DEVICE_ID=601 # Replace with the returned device ID.
```

## 10. Follow Tokyo Taco Club

Aiko taps Follow. This is per-user persistent state.

**API:** `PUT /api/v1/carts/$CART_ID/follow`

```bash
curl -i -X PUT "$BASE_URL/api/v1/carts/$CART_ID/follow" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $CUSTOMER_TOKEN"
```

**Expected:** 201 for a new follow, 200 for an existing row. Repeating does not create duplicates.

## 11. Open Following

The app displays Aiko’s followed, publicly visible carts.

**API:** `GET /api/v1/me/following?page=1`

```bash
curl -i -X GET "$BASE_URL/api/v1/me/following?page=1" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $CUSTOMER_TOKEN"
```

**Expected:** 200; `data` contains the followed cart. Other users’ follows do not appear.

## 12. Read cart activity

Read the selected cart’s public activity log.

**API:** `GET /api/v1/carts/$CART_ID/updates?page=1`

```bash
curl -i -X GET "$BASE_URL/api/v1/carts/$CART_ID/updates?page=1" \
  -H 'Accept: application/json'
```

**Expected:** 200 with the selected cart’s updates.
```bash
curl -i -X GET "$BASE_URL/api/v1/me/updates?page=1" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $CUSTOMER_TOKEN"
```

**Expected:** 200 with activity from all carts Aiko follows. This feed can include activity published before she followed.

## 13. Receive and read a notification

Ask Kenji to publish an update **after step 10**, then let the scheduler run (every two minutes). For a local demonstration, the server operator can run this command; it is not a customer API:

```bash
docker compose exec app php artisan notifications:dispatch
```

Nearby alerts additionally require the cart to be open, fresh coordinates for both parties, and the configured radius; the same user/cart pair has a ten-minute cooldown.

```bash
curl -i -X GET "$BASE_URL/api/v1/me/notifications?page=1" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $CUSTOMER_TOKEN"
```

**Expected:** 200 with Aiko's inbox. Empty `data` is valid when no eligible notification has been generated. Select a returned notification ID:

```bash
export NOTIFICATION_ID=701 # Replace with the actual inbox ID.
```
```bash
curl -i -X PATCH "$BASE_URL/api/v1/me/notifications/$NOTIFICATION_ID/read" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $CUSTOMER_TOKEN"
```

**Expected:** 200 with `read_at` populated. Another user’s notification ID returns 404.
```bash
curl -i -X PATCH "$BASE_URL/api/v1/me/notifications/read-all" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $CUSTOMER_TOKEN"
```

**Expected:** 204 with no body; all unread messages in Aiko’s inbox are marked read.

## 14. Report a problem (optional)

Aiko notices the cart marker is out of date. Supply the actual CART_ID in JSON.

**API:** `POST /api/v1/reports`

```bash
curl -i -X POST "$BASE_URL/api/v1/reports" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $CUSTOMER_TOKEN" \
  -H 'Content-Type: application/json' \
  --data '{"target_type":"cart","target_id":401,"reason":"wrongLocation","note":"The cart was not near the Marunouchi exit at lunchtime."}'
```

**Expected:** 201 with a report ID. `reporter_id` is Aiko; persisted status is open. Share the returned report ID with the admin journey. The ID 401 in this JSON must be replaced if the actual cart differs.

## 15. Unfollow (optional)

After completing the notification demonstration, Aiko can stop following the cart.

**API:** `DELETE /api/v1/carts/$CART_ID/follow`

```bash
curl -i -X DELETE "$BASE_URL/api/v1/carts/$CART_ID/follow" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $CUSTOMER_TOKEN"
```

**Expected:** 204. The follow row remains with `is_following=false`; the cart disappears from Following.

## 16. Stop nearby alerts and erase the stored position

When location sharing is disabled, turn off nearby alerts.

**API:** `PATCH /api/v1/me/settings`

```bash
curl -i -X PATCH "$BASE_URL/api/v1/me/settings" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $CUSTOMER_TOKEN" \
  -H 'Content-Type: application/json' \
  --data '{"nearby_enabled":false}'
```

**Expected:** 200 for the existing settings row.
```bash
curl -i -X DELETE "$BASE_URL/api/v1/me/location" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $CUSTOMER_TOKEN"
```

**Expected:** 204. The client must also stop publishing new coordinates; this call does not change operating-system permissions.

## 17. Sign out

If step 9 registered a device, remove that registration before revoking the session token. Skip device deletion if no device was registered.

**API:** `DELETE /api/v1/me/devices/$DEVICE_ID`

```bash
curl -i -X DELETE "$BASE_URL/api/v1/me/devices/$DEVICE_ID" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $CUSTOMER_TOKEN"
```

**Expected:** 204; this device will no longer receive push for Aiko.
```bash
curl -i -X POST "$BASE_URL/api/v1/auth/logout" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $CUSTOMER_TOKEN"
```

**Expected:** 204. Clear the locally stored token and private app state. Reusing this bearer token returns 401.

## Completion check

Aiko can discover carts publicly, maintain private follows/preferences, read follower activity and inbox messages, report issues, and revoke access. The app must implement GPS permissions, token storage, and polling/push refresh; the backend does not drive these client interactions.
