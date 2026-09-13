# Follo Cart backend

Laravel 13, PHP 8.4, MySQL 8.4 API and administrator dashboard for the Follo Cart mobile app. Includes token authentication, protected owner/admin actions, cart discovery, follows, GPS updates, photos, schedules, moderation, preferences, persistent inboxes, and Firebase Cloud Messaging delivery every two minutes. Redis is optional.

## Features

The features below are implemented in this backend. Customer and owner workflows are available through the API; administrators also have a browser dashboard. The existing Flutter app needs the HTTP integration described in [Flutter integration](doc/mobile-integration.md) to use them.


<img width="1226" height="744" alt="Screenshot from 2026-09-13 02-35-26" src="https://github.com/user-attachments/assets/c550d63d-b1c9-47b0-a47f-4f92b6286e5c" />
<img width="1226" height="744" alt="Screenshot from 2026-09-13 02-36-39" src="https://github.com/user-attachments/assets/1a224ad8-53dc-431f-998c-07bd68a540e5" />

### Accounts and access control

- Register a customer account with name, email, and a confirmed password of at least 12 characters.
- Sign in using email and password; receive a personal API token valid for 30 days.
- Restore account information with the current-user endpoint and update the display name.
- Sign out and revoke the current API token.
- Enforce customer, owner, and administrator roles on protected endpoints.
- Restrict owners to their own carts; reserve role changes and cart moderation for administrators.
- Prevent public registration from granting owner or administrator privileges.
- Block disabled accounts from login and protected requests; administrator account disabling revokes their API tokens.
- Protect administrator browser sessions with session regeneration, CSRF protection, and sign-out invalidation.
- Rate-limit authentication, API requests, reports, and photo uploads.

### Cart discovery and following

- Browse approved carts without signing in, search by cart name, and paginate results.
- Read cart details, cuisine, description, opening status, photos, schedules, and latest published location.
- Search around latitude/longitude using a radius from 100 meters to 50 kilometers, defaulting to 5 kilometers.
- Return nearby carts sorted by distance, including distance in meters.
- Exclude missing or stale coordinates from nearby searches.
- Hide pending/rejected carts and carts belonging to disabled owners from public discovery.
- Follow or unfollow a cart persistently, with one follow record per user/cart pair.
- View the current user's followed carts and their activity feed.
- Calculate follower counts from active follow records.

### Owner workflows

- Create multiple food carts under an owner account; new carts await administrator approval.
- List owned carts, including those awaiting approval or rejected by moderation.
- Edit cart name, description, cuisine, and open/closed status.
- Delete an owned cart and its related database records and photo files.
- Publish cart GPS coordinates and an optional address, with server-controlled freshness timestamps.
- Refresh location freshness even when the submitted coordinates have not changed.
- Publish approved-cart announcements and activity types: opened, closed, moved, schedule changed, and photo added.
- Maintain weekly schedules and dated one-off stops with weekday, opening/closing times, timezone, address, optional coordinates, and active status.
- Store overnight serving windows where closing time is earlier than opening time.
- Remove individual schedule entries.
- Upload JPEG, PNG, or WebP cart photos, up to 5 MB each and 10 photos per cart.
- Receive public photo URLs and delete individual photos.

Opening status is explicitly controlled by the owner; schedules do not automatically open or close carts. Owner actions notify followers when an activity update is published. Routine GPS submissions do not automatically create announcements.

### Customer preferences and location

- Read and change push, nearby-alert, and cart-update preferences independently.
- Set a personal nearby-alert radius, defaulting to 5 kilometers.
- Publish the customer's latest coordinates with a server timestamp.
- Delete stored customer coordinates when location sharing is disabled.
- Keep only the current position per customer and per cart, rather than storing movement history.
- Prune customer locations older than 24 hours through a daily scheduled task.

### Notifications and push delivery

- Store a private, persistent notification inbox for each user.
- Paginate inbox messages and mark one or all messages as read.
- Generate cart-update notifications for eligible active followers.
- Generate nearby notifications for followed, approved, open carts within the user's radius when both locations are fresh.
- Apply a ten-minute nearby cooldown per user/cart pair.
- Process notifications every two minutes when the scheduler is running, or manually through an Artisan command.
- Deduplicate generated notifications and maintain separate delivery records for each device.
- Register or remove Android, iOS, and web device tokens; reassign a token when a different account registers it on a shared device.
- Deliver through Firebase Cloud Messaging HTTP v1 when credentials are configured.
- Retry failed sends with exponential backoff, up to five attempts, and remove tokens reported as unregistered by FCM.
- Recheck account status, preferences, follow state, cart visibility, and device ownership before sending pending messages.
- Track pending, sent, failed, and skipped deliveries; retry failed deliveries from the dashboard.
- Pause push globally while preserving inboxes and pending delivery records.
- Use a shared lock to prevent overlapping dispatch runs and prune expired API tokens daily.

Push is disabled by default. Inbox generation works without Firebase credentials. Push delivery is at least once, so clients should deduplicate by notification ID. Device GPS collection, permission prompts, and background execution belong to the mobile app.

### Reporting and moderation

- Submit reports against a cart or user for spam, fake listings, wrong locations, offensive content, or another reason.
- Include a report note and derive the reporter's identity from the authenticated account.
- Let administrators list reports and filter by status through the API.
- Move reports through open, reviewing, resolved, or dismissed states.
- Record resolution notes, the acting administrator, and resolution time.
- Approve, reject, or return carts to pending review.
- Mark carts as featured through the administrator API and dashboard.
- Change account roles and enable or disable users.
- Prevent administrators from disabling or demoting their own account.

### Administrator dashboard

- Sign in through a dedicated administrator login page.
- See total carts, customer accounts, active follows, and carts pending review.
- Review recent carts, recent activity, and push delivery totals.
- Search users and carts by name, with paginated management lists.
- Create users, change their name/email/password/role/status, and promote customers to owners.
- Create carts, assign an active owner, edit details, set featured status, and moderate visibility.
- Create, edit, or delete cart updates, schedules, follows, and cart locations.
- Upload photos from a cart's edit page and browse/delete photos from the photo list.
- View customer locations and delete stored positions.
- Create and edit user notification preferences and radius settings.
- Review and resolve moderation reports.
- Inspect notification inbox records and push delivery history; requeue failed deliveries.
- Enable or pause push delivery and configure location freshness from 5 to 120 minutes.
- View the configured database, cache store, and push provider.
- Use the responsive dashboard without a Node/frontend build step.

### Storage and operations

- Persist users, carts, follows, locations, preferences, schedules, photos, activity, reports, inboxes, and delivery state in MySQL.
- Use database-backed sessions, cache, and dispatcher locks initially; optionally switch the cache/lock store to Redis.
- Keep notification delivery records in MySQL regardless of the cache choice.
- Run the app, MySQL, scheduler, and optional Redis through Docker Compose.
- Create an administrator with an interactive command and no default administrator password.
- Seed seven example carts around Tokyo for local development.
- Configure allowed web-client origins through CORS settings.
- Expose an application boot health endpoint at `/up`.
- Provide API, mobile integration, deployment, and system-design documentation in `doc/`.
- Include automated tests for authorization, follows, photos, proximity, notification retries, dashboard workflows, and location refresh behavior.

### Setup-dependent features and current limits

| Area | Current status |
|---|---|
| Flutter integration | API is implemented; the existing Flutter repositories still need to be connected to it. |
| Live push notifications | FCM implementation is included; Firebase credentials, device registration, and device testing are required. |
| Scheduled processing | Requires the scheduler service or cron to remain running. |
| Redis | Optional; requires enabling the service and changing deployment environment settings. |
| Photos | Stored on the public disk; known photo URLs remain accessible when a cart is hidden. |
| Live location | Accepts client GPS updates; does not collect GPS or run mobile background tasks. |
| Authentication extras | Self-service password reset, email verification, and administrator MFA are not implemented. |
| Real-time streams | HTTP endpoints are available; WebSocket/SSE subscriptions are not implemented. |
| Ordering and payments | This app manages food-cart discovery and following; menus, checkout, payments, and order fulfillment are not implemented. |
| Production operations | Local Docker setup is provided; production hosting, backups, monitoring, and hardening need deployment work. |

See [system-design improvements](doc/system-design.md) for the recommended next steps and known scaling tradeoffs.

## Run with Docker

```bash
cp .env.example .env
# Edit local database credentials if desired, before starting MySQL.
docker compose build
# Install dependencies in the bind-mounted workspace.
docker compose run --rm --no-deps app composer install
docker compose run --rm --no-deps app php artisan key:generate
docker compose up -d mysql app
docker compose exec app php artisan migrate
docker compose exec app php artisan storage:link
docker compose exec app php artisan app:create-admin you@example.com
# Optional: seven example carts around Tokyo, no default login credentials.
docker compose exec app php artisan db:seed --class=DemoSeeder
# Start after migrations have completed.
docker compose --profile workers up -d scheduler
```

Open **http://localhost:8000**. The administrator command asks for a password interactively. There are no hard-coded administrator credentials. Create owner accounts under People & roles, or promote a registered customer; then add and approve carts. Photos can be uploaded from a cart's edit page.

The Docker configuration is for local development. It exposes the app on loopback only and keeps MySQL private to the Compose network. Set LOCAL_UID / LOCAL_GID if your local user is not 1000. `docker compose stop` stops the app without deleting data.

On an existing PHP installation, run `composer install`, configure MySQL with `DB_HOST=127.0.0.1`, then run the equivalent Artisan commands and `php artisan serve`. No Node build is needed for the dashboard.

## Configure the MySQL connection

The dedicated connection file is [config/mysql.php](config/mysql.php), loaded by `config/database.php`. Set your deployment values in the project's `.env` file, which is excluded from Git. Copy `.env.example` only when `.env` does not already exist.

```dotenv
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=follo_cart
DB_USERNAME=follo
DB_PASSWORD="your-mysql-password"
DB_SOCKET=
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
```

Use `DB_HOST=mysql` for the bundled Docker Compose database. If PHP and MySQL run directly on the same machine, use `127.0.0.1`; for a remote database, use its hostname. The hostname must be reachable from the PHP runtime. An optional `MYSQL_ATTR_SSL_CA` value supplies the CA certificate path inside that runtime. If `DB_URL` is set, its connection values can override the individual fields above; unset it when configuring the fields separately.

After editing `.env`, clear cached configuration and check the connection:

```bash
docker compose exec app php artisan config:clear
docker compose exec app php artisan migrate:status
# If the scheduler is running, restart it to load the new settings:
docker compose restart scheduler
```

For a fresh database, run `php artisan migrate` through the app container after configuring it. For a production deployment that caches configuration, rebuild it with `php artisan config:cache` and restart long-running application processes. On a direct PHP installation, omit `docker compose exec app`.

Changing these values selects a connection; it does not copy existing data or change MySQL account passwords. The bundled MySQL container uses its database/user/password environment values only when initializing an empty data volume. Update an existing server account separately before changing the application's password.

## Push and scheduling

The default `PUSH_DRIVER=disabled` creates in-app notifications and pending delivery records without contacting Firebase. For real delivery, place your Firebase service account JSON outside public storage, set `PUSH_DRIVER=fcm`, `FIREBASE_PROJECT_ID`, and `FIREBASE_CREDENTIALS` to its absolute path **inside the container**, and enable the FCM HTTP v1 API. Never commit that file. Register each device with `POST /api/v1/me/devices`.

Run `php artisan notifications:dispatch` manually, or run one scheduler with cron:

```cron
* * * * * cd /path/to/app && php artisan schedule:run >> /var/log/follo-scheduler.log 2>&1
```

The scheduler dispatches every two minutes. Notifications go to active followers of approved carts, respecting each user's settings; nearby alerts also require fresh user/cart coordinates, an open cart, and the selected radius (5 km default). Each user/cart pair has a ten-minute nearby cooldown. Registration alone does not send historical updates. New followers see existing updates through the feed and receive subsequent notifications.

Each device has separate retry state, with exponential backoff and five attempts. The dashboard can requeue failed deliveries. Delivery is at least once; the mobile app should deduplicate on `notification_id`. Dashboard configuration can pause push delivery while retaining in-app notifications and pending messages.

## Optional Redis

The initial cache and lock store is MySQL; no Redis server is required. To switch:

```bash
docker compose --profile redis up -d redis
# Set CACHE_STORE=redis, REDIS_CLIENT=predis and REDIS_HOST=redis in .env.
docker compose exec app php artisan config:clear
docker compose restart app scheduler
```

Stop the scheduler before switching cache stores so two dispatchers cannot hold locks in different stores. The persistent delivery outbox remains in MySQL regardless of cache choice.

## Validation and documentation

```bash
docker compose exec -T app php artisan test
docker compose exec -T app vendor/bin/pint --test
docker compose exec -T app php artisan route:list
docker compose exec -T app php artisan schedule:list
```

Tests use an isolated in-memory SQLite database. MySQL migration and notification command smoke checks should also run when changing schema or queries.

- [API contract](doc/api.md)
- [Role-based user journeys](doc/user_journey/README.md)
- [Database table schemas](doc/db/README.md)
- [Per-endpoint test cases and full-parameter examples](doc/test/README.md)
- [Flutter integration](doc/mobile-integration.md)
- [System design and improvements](doc/system-design.md)
- [Deployment notes](doc/deployment.md)

The Flutter source at `/home/mhr/Documents/follo-cart` was inspected for model compatibility. This repository supplies the backend; the Flutter app still needs an HTTP repository implementation replacing its local/Firestore repositories. Live FCM requires credentials and device testing.

## GPS storage policy

Cart locations keep one current row per cart. GPS submissions overwrite that row and refresh server-controlled `recorded_at` and `updated_at`, including when the cart is stationary. No unlimited GPS history is stored. See [cart location schema](doc/db/cart_locations.md).
