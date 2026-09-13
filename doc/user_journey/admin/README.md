# Administrator Journey — Approve and Moderate the Community

[All journeys](../README.md) · Role: `admin` · Example person: **Mika Admin** (`mika@example.test`).

Mika approves Kenji's owner access and cart, then investigates Aiko's report. Keep privileged tokens on the administrator client. The examples use the REST API; the browser dashboard uses separate session/CSRF authentication.

```bash
export BASE_URL=http://localhost:8000
```

## 1. Bootstrap the first administrator (server operator)

There is no public API that creates the first admin. An authorized server operator runs:

```bash
docker compose exec app php artisan app:create-admin mika@example.test --name="Mika Admin"
```

The command prompts privately for a password of at least 12 characters. For this disposable walkthrough only, the subsequent example assumes `Journey-Mika-2026!`; use the password actually entered. If the administrator already exists, skip creation. The CLI is not a mobile API.

## 2. Sign in and verify administrator access

Use the administrator credentials created above.

**API:** `POST /api/v1/auth/login`

```bash
curl -i -X POST "$BASE_URL/api/v1/auth/login" \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  --data '{"email":"mika@example.test","password":"Journey-Mika-2026!"}'
```

**Expected:** 200 with `user.role: admin` and a bearer token.

```bash
export ADMIN_TOKEN='<token returned by administrator login>'
```
```bash
curl -i -X GET "$BASE_URL/api/v1/me" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $ADMIN_TOKEN"
```

**Expected:** 200 with Mika’s account. Customer/owner tokens cannot call the admin endpoints.

## 3. Find Kenji and grant owner access

After Kenji registers, search users by name. Search does not match email. Verify the returned email before modifying the account.

**API:** `GET /api/v1/admin/users?search=Kenji&page=1`

```bash
curl -i -X GET "$BASE_URL/api/v1/admin/users?search=Kenji&page=1" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $ADMIN_TOKEN"
```

**Expected:** 200 with matching users. Copy Kenji’s actual ID, not Mika’s.

```bash
export OWNER_USER_ID=201 # Replace with Kenji’s returned user ID.
```
```bash
curl -i -X PATCH "$BASE_URL/api/v1/admin/users/$OWNER_USER_ID" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H 'Content-Type: application/json' \
  --data '{"role":"owner","is_active":true}'
```

**Expected:** 200; Kenji now has role=owner and can create his cart. Send him back to owner step 3.

## 4. Review all carts

After Kenji creates the cart and adds details, inspect the listings. The API returns all moderation states; filter pending items in the client because this path has no status query filter.

**API:** `GET /api/v1/admin/carts?page=1`

```bash
curl -i -X GET "$BASE_URL/api/v1/admin/carts?page=1" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $ADMIN_TOKEN"
```

**Expected:** 200 with cart details, including pending carts and related locations, photos, and schedules. Match owner_id to Kenji and name to Tokyo Taco Club.

```bash
export CART_ID=401 # Replace with Kenji’s cart ID from the response.
```

## 5. Approve and optionally feature the cart

Approve the reviewed listing. Featuring is an independent administrator choice.

**API:** `PATCH /api/v1/admin/carts/$CART_ID`

```bash
curl -i -X PATCH "$BASE_URL/api/v1/admin/carts/$CART_ID" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H 'Content-Type: application/json' \
  --data '{"moderation_status":"approved","is_featured":true}'
```

**Expected:** 200. The cart becomes publicly discoverable as long as its owner remains active; nearby discovery additionally needs fresh GPS data.
```bash
curl -i -X GET "$BASE_URL/api/v1/carts/$CART_ID" \
  -H 'Accept: application/json'
```

**Expected:** 200. Kenji can now publish updates, and Aiko can follow.

## 6. Find customer reports

After Aiko submits her report, open the moderation queue.

**API:** `GET /api/v1/admin/reports?status=open&page=1`

```bash
curl -i -X GET "$BASE_URL/api/v1/admin/reports?status=open&page=1" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $ADMIN_TOKEN"
```

**Expected:** 200 with open reports. Locate the report for this cart and record its actual ID. An empty list means no matching report exists yet.

```bash
export REPORT_ID=801 # Replace with the returned report ID.
```

## 7. Start reviewing the report

Record that Mika is investigating the location complaint.

**API:** `PATCH /api/v1/admin/reports/$REPORT_ID`

```bash
curl -i -X PATCH "$BASE_URL/api/v1/admin/reports/$REPORT_ID" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H 'Content-Type: application/json' \
  --data '{"status":"reviewing","resolution_note":"Checking the published location with the owner."}'
```

**Expected:** 200 with status=reviewing, resolved_by=Mika’s ID, and resolved_at=null. The actor is derived from the token.

## 8. Hide the cart while investigating (optional)

If the complaint warrants a temporary takedown, set it pending and remove featured status. This is an optional moderation branch.

**API:** `PATCH /api/v1/admin/carts/$CART_ID`

```bash
curl -i -X PATCH "$BASE_URL/api/v1/admin/carts/$CART_ID" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H 'Content-Type: application/json' \
  --data '{"moderation_status":"pending","is_featured":false}'
```

**Expected:** 200. Public GET /carts/{cart} now returns 404 and it is excluded from public lists. Kenji still sees his own cart in /owner/carts but cannot publish activity until approval.

Known public photo URLs remain accessible after hiding a cart; moderation currently hides the listing rather than revoking file URLs.

## 9. Resolve the complaint and restore the listing

After Kenji refreshes the GPS location and Mika verifies it, approve the cart again.

**API:** `PATCH /api/v1/admin/carts/$CART_ID`

```bash
curl -i -X PATCH "$BASE_URL/api/v1/admin/carts/$CART_ID" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H 'Content-Type: application/json' \
  --data '{"moderation_status":"approved","is_featured":false}'
```

**Expected:** 200. The listing is visible again if the owner is active.
```bash
curl -i -X PATCH "$BASE_URL/api/v1/admin/reports/$REPORT_ID" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H 'Content-Type: application/json' \
  --data '{"status":"resolved","resolution_note":"Owner updated the location to Tokyo station plaza; verified and restored the listing."}'
```

**Expected:** 200 with status=resolved, resolved_by=Mika’s ID, and a server-generated resolved_at. For an unfounded complaint, use dismissed instead; to reopen, use open (clears resolved_at).

## 10. Review resolved reports

Confirm the case left the open queue.

**API:** `GET /api/v1/admin/reports?status=resolved&page=1`

```bash
curl -i -X GET "$BASE_URL/api/v1/admin/reports?status=resolved&page=1" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $ADMIN_TOKEN"
```

**Expected:** 200 with resolved reports. The response includes the stored resolution note and actor.

## 11. Disable an abusive owner (optional, separate branch)

Only do this if account-level action is warranted. It is not part of the successful location-correction flow.

**API:** `PATCH /api/v1/admin/users/$OWNER_USER_ID`

```bash
curl -i -X PATCH "$BASE_URL/api/v1/admin/users/$OWNER_USER_ID" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H 'Content-Type: application/json' \
  --data '{"is_active":false}'
```

**Expected:** 200. Kenji cannot log in or use protected APIs; existing personal access tokens are revoked. All his carts disappear from public discovery even if approved.
```bash
curl -i -X PATCH "$BASE_URL/api/v1/admin/users/$OWNER_USER_ID" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H 'Content-Type: application/json' \
  --data '{"is_active":true}'
```

**Expected:** 200 when access is restored. Previously revoked tokens remain invalid, so Kenji must log in again. Cart moderation_status is unchanged by account blocking/unblocking.

## 12. Other moderation choices

The same user endpoint supports role=customer/owner/admin. Promote additional admins only after verifying the account; there is no separate privilege-grant endpoint. Mika cannot demote or disable herself (422). For a permanent listing rejection, use moderation_status=rejected; rejection hides the cart but does not delete it.

## 13. Operate notifications and configuration through the dashboard

Open `http://localhost:8000/admin` and sign in using Mika's email/password. This creates a browser session; a REST bearer token is not a substitute for the dashboard's CSRF-protected forms.

| Task | Browser page | Behavior |
|---|---|---|
| Overview | `/admin` | Counts, recent carts/updates, delivery totals |
| Delivery monitoring | `/admin/deliveries` | Inspect pending/sent/failed/skipped delivery records and retry failed sends |
| Global preferences | `/admin/configuration` | Pause/resume push; set location freshness from 5–120 minutes |
| Account management | `/admin/users` | Create/edit users, including name, email, password, and roles |
| Cart management | `/admin/carts` | Create/edit listings, assign owners, moderate, and upload photos |

No `/api/v1/admin/configuration` or `/api/v1/admin/deliveries` endpoint exists. Use the actual dashboard for these operations. MySQL/Redis/provider credentials remain deployment environment settings. The server operator can trigger notification processing with:

```bash
docker compose exec app php artisan notifications:dispatch
```

That command is not an admin HTTP API, and live FCM still requires provider credentials and valid device registration.

## 14. Sign out

Revoke the current administrator API token.

**API:** `POST /api/v1/auth/logout`

```bash
curl -i -X POST "$BASE_URL/api/v1/auth/logout" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $ADMIN_TOKEN"
```

**Expected:** 204. Clear the token locally. If also signed into the dashboard, sign out there separately; browser sessions and API tokens are independent.

## Completion check

Kenji has approved owner access, Tokyo Taco Club is reviewed and discoverable, Aiko’s report has a recorded resolution, and Mika’s API token is revoked. Account takedowns and permanent cart deletion are optional branches, not routine cleanup.
