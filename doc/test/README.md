# API Test Cases and Request Examples

One document per API method/path, with the API title as its document title. These documents cover every declared `/api/v1` route (GET routes also accept HEAD). Browser dashboard routes are documented separately in the main README.

## Coverage

The endpoint matrix contains 488 tests: 38 full-parameter examples, 182 invalid-field cases, 166 accepted variants, 33 missing-authentication cases, 33 disabled-account cases, 16 wrong-role cases, 19 missing-resource cases, and one route/document coverage check. See [verification results](results.md).

## Run automated checks

```bash
docker compose exec -T app php artisan test --filter=ApiEndpointMatrixTest
```

The suite creates isolated in-memory SQLite fixtures and fakes photo storage; it does not change your configured MySQL data or send push messages. It checks every endpoint's full-parameter request, generated invalid-field cases, access control, and selected persisted effects. Existing `FoodCartApiTest` covers additional behavioral flows. Rule-derived cases cover representative invalid types, missing values, enums, and limits, not the infinite set of all possible parameter combinations.

## Manual setup and shared data

Run examples from the repository root. `$BASE_URL` excludes `/api/v1`:

```bash
export BASE_URL=http://localhost:8000
export CUSTOMER_TOKEN='<token from customer login>'
export OWNER_TOKEN='<token from owner login>'
export ADMIN_TOKEN='<token from admin login>'
```

Use a disposable database for manual mutation/deletion tests. IDs below are created automatically by the test suite, **not** by the running application. Replace example path/body IDs with actual IDs returned by your own setup. The example passwords and FCM token are synthetic test data, not existing login credentials.

| Fixture | Test ID | Details |
|---|---|---|
| Customer | 101 | customer@example.test, Customer Test |
| Other customer | 102 | other@example.test |
| Owner | 201 | owner@example.test |
| Other owner | 202 | other-owner@example.test |
| Administrator | 301 | admin@example.test |
| Approved cart | 401 | Tokyo Taco Club, owner 201; fresh location 35.6812, 139.7671 |
| Other owner's cart | 402 | owner 202 |
| Weekly schedule | 501 | cart 401, Monday |
| Device | 601 | customer 101 |
| Unread notification | 701 | customer 101, cart 401 |
| Report | 801 | open report about cart 401 |
| Photo | 901 | cart 401; fake storage file in automated tests |

Fixture accounts use `Test-password-123!`. Customer 101 already follows cart 401 and has a settings row. The fixture cart has an activity update, a photo, a schedule, and a location. User location starts absent. The full schedule example creates a separate dated Sunday entry.

For manual setup: register customer/owner accounts, promote the owner through an administrator account, create and approve a cart, publish its location, follow it as the customer, then publish an update and run `notifications:dispatch` to populate the inbox. Upload a photo, save a schedule, register a device, and submit a report, keeping the returned IDs. Use `app:create-admin` for the initial administrator. Keep `PUSH_DRIVER=disabled` with synthetic device tokens.

## Request conventions

- Send `Accept: application/json`; authenticated APIs use bearer tokens. JSON writes also need `Content-Type: application/json`.
- Uploads use multipart, with a provided small PNG at `doc/test/fixtures/cart.png`.
- `required`: must be present/nonempty; `sometimes`: validate only when supplied; `nullable`: null is accepted. Empty strings are normalized to null by Laravel.
- Boolean fields accept JSON true/false, 0/1, or strings "0"/"1"; strings "true"/"false" fail validation.
- For query-string null cases send an empty value, such as `radius_meters=`; Laravel normalizes it to null. Omit the key entirely for omission cases.
- Coordinates are numeric, days/radii/IDs are integers. Schedule times use HH:mm, dates YYYY-MM-DD, and timezone is an IANA identifier.
- Validation failures return 422 with `message` and usually `errors` keyed by field. Business-rule failures can have only `message`.
- Unknown parameters are ignored. The APIs do not support arbitrary sort/per_page/filter fields beyond those listed.
- List `page` is Laravel pagination rather than domain validation; use 1 or higher. Lists contain up to 25 rows per page.
- Missing/invalid/expired tokens return 401. Disabled authenticated users return 403. Foreign private notification/device IDs return 404.
- Rate limits can return 429: auth and reports 6/minute, general API 120/minute, photo uploads also 10/minute. Wait for the limit window when manually running many examples.
- Upserts return 201 on creation and 200 on update. Generated IDs, timestamps, photo names, and tokens vary. Response examples are excerpts, not exact snapshots.

## Endpoints

| API title | Method | Path | Access |
|---|---|---|---|
| [Register Customer](01-register.md) | POST | `/api/v1/auth/register` | public |
| [Log In](02-login.md) | POST | `/api/v1/auth/login` | public |
| [Log Out](03-logout.md) | POST | `/api/v1/auth/logout` | customer |
| [Browse and Search Carts](04-list-carts.md) | GET | `/api/v1/carts` | public |
| [Get Cart Details](05-get-cart.md) | GET | `/api/v1/carts/{cart}` | public |
| [List Cart Updates](06-cart-updates.md) | GET | `/api/v1/carts/{cart}/updates` | public |
| [Get My Profile](07-get-profile.md) | GET | `/api/v1/me` | customer |
| [Update My Profile](08-update-profile.md) | PATCH | `/api/v1/me` | customer |
| [Get My Notification Settings](09-get-settings.md) | GET | `/api/v1/me/settings` | customer |
| [Update My Notification Settings](10-update-settings.md) | PATCH | `/api/v1/me/settings` | customer |
| [Publish My Location](11-set-user-location.md) | PUT | `/api/v1/me/location` | customer |
| [Delete My Location](12-delete-user-location.md) | DELETE | `/api/v1/me/location` | customer |
| [List My Followed Carts](13-following.md) | GET | `/api/v1/me/following` | customer |
| [List Updates from Followed Carts](14-my-updates.md) | GET | `/api/v1/me/updates` | customer |
| [Register Push Device](15-register-device.md) | POST | `/api/v1/me/devices` | customer |
| [Remove Push Device](16-delete-device.md) | DELETE | `/api/v1/me/devices/{device}` | customer |
| [List My Notifications](17-notifications.md) | GET | `/api/v1/me/notifications` | customer |
| [Mark Notification Read](18-read-notification.md) | PATCH | `/api/v1/me/notifications/{notification}/read` | customer |
| [Mark All My Notifications Read](19-read-all-notifications.md) | PATCH | `/api/v1/me/notifications/read-all` | customer |
| [Report a Cart or User](20-submit-report.md) | POST | `/api/v1/reports` | customer |
| [Follow Cart](21-follow-cart.md) | PUT | `/api/v1/carts/{cart}/follow` | customer |
| [Unfollow Cart](22-unfollow-cart.md) | DELETE | `/api/v1/carts/{cart}/follow` | customer |
| [List My Owned Carts](23-owner-carts.md) | GET | `/api/v1/owner/carts` | owner |
| [Create Food Cart](24-create-cart.md) | POST | `/api/v1/owner/carts` | owner |
| [Update Food Cart](25-update-cart.md) | PATCH | `/api/v1/owner/carts/{cart}` | owner |
| [Delete Food Cart](26-delete-cart.md) | DELETE | `/api/v1/owner/carts/{cart}` | owner |
| [Publish Cart Location](27-set-cart-location.md) | PUT | `/api/v1/owner/carts/{cart}/location` | owner |
| [Publish Cart Activity Update](28-publish-update.md) | POST | `/api/v1/owner/carts/{cart}/updates` | owner |
| [Save Cart Schedule or One-off Stop](29-upsert-schedule.md) | PUT | `/api/v1/owner/carts/{cart}/schedules` | owner |
| [Delete Cart Schedule](30-delete-schedule.md) | DELETE | `/api/v1/owner/carts/{cart}/schedules/{schedule}` | owner |
| [Upload Cart Photo](31-upload-photo.md) | POST | `/api/v1/owner/carts/{cart}/photos` | owner |
| [Delete Cart Photo](32-delete-photo.md) | DELETE | `/api/v1/owner/carts/{cart}/photos/{photo}` | owner |
| [Admin — List Users](33-admin-users.md) | GET | `/api/v1/admin/users` | admin |
| [Admin — Change User Role or Status](34-admin-update-user.md) | PATCH | `/api/v1/admin/users/{user}` | admin |
| [Admin — List All Carts](35-admin-carts.md) | GET | `/api/v1/admin/carts` | admin |
| [Admin — Moderate or Feature Cart](36-admin-update-cart.md) | PATCH | `/api/v1/admin/carts/{cart}` | admin |
| [Admin — List Reports](37-admin-reports.md) | GET | `/api/v1/admin/reports` | admin |
| [Admin — Resolve or Review Report](38-admin-update-report.md) | PATCH | `/api/v1/admin/reports/{report}` | admin |
