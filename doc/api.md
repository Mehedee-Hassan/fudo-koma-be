# API v1

See [per-endpoint test cases and full-parameter examples](test/README.md) for runnable requests, sample data, and expected validation results.

Base URL: `/api/v1`. Use `Accept: application/json`. JSON writes use `Content-Type: application/json`; photo uploads use multipart form data. Authenticated endpoints require `Authorization: Bearer <token>`.

Lists return Laravel pagination: `data`, `current_page`, `last_page`, `per_page`, `total`, `next_page_url`. Use `?page=2` to continue. IDs are numeric on the wire; convert to strings for existing Flutter models. Timestamps are UTC ISO-8601; fields use snake_case. Singular endpoints return the object directly. Creation returns 201; updates generally return 200, upserts can return 201 on first creation, and deletion/logout returns 204. Validation errors return 422 with `message` and `errors`; unauthenticated 401, unauthorized 403, unavailable records 404, rate limits 429.

## Authentication

| Method | Path | Body / behavior |
|---|---|---|
| POST | `/auth/register` | `name`, `email`, `password`, `password_confirmation`; minimum 12 characters; always creates a customer |
| POST | `/auth/login` | `email`, `password` |
| POST | `/auth/logout` | Revokes current token |
| GET | `/me` | Current account |
| PATCH | `/me` | `name` |

Register/login return `{ "user": {...}, "token": "..." }`. Tokens expire after 30 days. Administrators promote accounts to owner/admin. Disabled accounts cannot sign in or use existing tokens. Admin web sessions are separate from API tokens and use CSRF protection. Login and registration share a six-request/minute/IP limit.

## Discovery and follows

| Method | Path | Parameters |
|---|---|---|
| GET | `/carts` | Public approved carts; optional `search`; optional paired `latitude`, `longitude`, `radius_meters` (100–50000, default 5000) |
| GET | `/carts/{id}` | Public cart with location, photos, schedules |
| GET | `/carts/{id}/updates` | Public activity feed |
| PUT | `/carts/{id}/follow` | Follow idempotently |
| DELETE | `/carts/{id}/follow` | Set follow flag false |
| GET | `/me/following` | Followed visible carts |
| GET | `/me/updates` | Activity from followed visible carts |

Cart shape:

```json
{
  "id": 1,
  "owner_id": 2,
  "name": "Tokyo Taco Club",
  "description": "Fresh street food",
  "cuisine": "Mexican",
  "status": "open",
  "moderation_status": "approved",
  "is_featured": false,
  "followers_count": 12,
  "updated_at": "2026-09-13T00:00:00.000000Z",
  "location": {
    "latitude": 35.6812,
    "longitude": 139.7671,
    "address": "Tokyo station",
    "updated_at": "2026-09-13T00:00:00.000000Z"
  },
  "photos": [{"id": 1, "url": "http://localhost:8000/storage/carts/1/example.jpg"}],
  "schedules": []
}
```

Nearby results add `distance_meters` and sort by distance. Missing/stale locations are excluded from nearby searches; general catalog results may have a null or stale location. Clients should use `location.updated_at` when rendering markers. Blocked owners' carts are hidden automatically. Follow responses include `user_id`, `cart_id`, `is_following`, and `updated_at`; owner ID is obtained from the cart, avoiding duplicate ownership data.

## Account preferences, location, and inbox

| Method | Path | Body / behavior |
|---|---|---|
| GET/PATCH | `/me/settings` | `push_enabled`, `nearby_enabled`, `updates_enabled` booleans; `radius_meters` integer 100–50000 |
| PUT | `/me/location` | `latitude` (-90..90), `longitude` (-180..180); timestamp is server-controlled |
| DELETE | `/me/location` | Remove stored position |
| POST | `/me/devices` | `token`, `platform` (`android`, `ios`, `web`); returns device ID; token never returned |
| DELETE | `/me/devices/{id}` | Remove this account's registered device |
| GET | `/me/notifications` | Private inbox |
| PATCH | `/me/notifications/{id}/read` | Mark own message read |
| PATCH | `/me/notifications/read-all` | Mark own inbox read |
| POST | `/reports` | `target_type` (`cart`,`user`), `target_id`, `reason` (`spam`,`fakeListing`,`wrongLocation`,`offensive`,`other`), optional `note` |

Device registration transfers a token to the currently authenticated account, supporting shared-device sign-in. Remove the registered device before logout. Pending deliveries recheck device ownership and opt-outs before sending. An inbox notification contains `id`, `user_id`, `cart_id`, `type` (`update`,`nearby`), `title`, `body`, `read_at`, timestamps. An update contains `cart_id`, `title`, `body`, `type` (announcement/opened/closed/moved/scheduleChanged/photoAdded), timestamps.

## Owner actions

All paths below require role `owner` or `admin`; owners can modify only their own carts. Privileged fields such as owner ID, feature status, moderation, and follower counts cannot be changed through owner writes.

| Method | Path | Body |
|---|---|---|
| GET | `/owner/carts` | Own carts, including pending/rejected |
| POST | `/owner/carts` | Required `name`; optional `description`, `cuisine`, `status` (`open`,`closed`); initially pending |
| PATCH | `/owner/carts/{id}` | Any of the same editable fields |
| DELETE | `/owner/carts/{id}` | Delete cart and related records/files |
| PUT | `/owner/carts/{id}/location` | `latitude`, `longitude`, optional `address` |
| POST | `/owner/carts/{id}/updates` | `title` (150 max), `body` (3000 max), optional `type`; cart must be approved |
| PUT | `/owner/carts/{id}/schedules` | `day_of_week` (Monday=1..Sunday=7), `opens_at`, `closes_at` (`HH:mm`), IANA `timezone`; optional `address`, `specific_date` (`YYYY-MM-DD`), `is_active`, paired coordinates |
| DELETE | `/owner/carts/{id}/schedules/{scheduleId}` | Remove a schedule belonging to that cart |
| POST | `/owner/carts/{id}/photos` | Multipart `photo`; JPEG/PNG/WebP, max 5 MB, max 10 per cart |
| DELETE | `/owner/carts/{id}/photos/{photoId}` | Remove a photo belonging to that cart |

Schedule upsert matches cart, weekday, and optional date. Weekly entries have `specific_date=null`. Closing before opening represents an overnight window. Dates and coordinates describe planned stops; they never substitute for live GPS. To replace a schedule list, delete removed records then upsert current entries. Emit an explicit update after an owner action when followers should be notified; frequent GPS writes do not automatically produce feed spam.

## Admin mobile actions

| Method | Path | Body / query |
|---|---|---|
| GET | `/admin/users` | Optional `search` |
| PATCH | `/admin/users/{id}` | `role` and/or `is_active` |
| GET | `/admin/carts` | Includes pending/hidden carts |
| PATCH | `/admin/carts/{id}` | `moderation_status`, `is_featured` |
| GET | `/admin/reports` | Optional `status` |
| PATCH | `/admin/reports/{id}` | `status` (open/reviewing/resolved/dismissed), optional `resolution_note` |

Administrators cannot disable or demote themselves. Web dashboard `/admin` additionally supports account/cart creation, data maintenance, photo upload, notification monitoring and retry, and platform configuration.

## Example

```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H 'Accept: application/json' -H 'Content-Type: application/json' \
  -d '{"email":"you@example.com","password":"your-password"}'
curl 'http://localhost:8000/api/v1/carts?latitude=35.6812&longitude=139.7671&radius_meters=5000' \
  -H 'Accept: application/json'
```
