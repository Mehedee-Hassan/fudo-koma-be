# Deployment notes

Use PHP 8.4 with pdo_mysql, mbstring, fileinfo, openssl, curl, intl, zip, and the normal Laravel extensions. Composer dependencies are locked in composer.lock. Serve only public/ through Nginx/Apache and PHP-FPM or an equivalent production runtime. Do not expose the repository root or use artisan serve for production.

Set APP_ENV=production, APP_DEBUG=false, APP_URL to your HTTPS origin, SESSION_SECURE_COOKIE=true, an explicit CORS_ALLOWED_ORIGINS list, unique database credentials, and a stable APP_KEY. Keep .env and Firebase JSON out of version control and public storage. Public photo URLs currently bypass moderation authorization; see system-design.md before handling sensitive takedowns.

Install dependencies with `composer install --no-dev --optimize-autoloader`, run `php artisan migrate --force`, `php artisan storage:link`, and `php artisan optimize`. Give the application user write permission to storage and bootstrap/cache. Persist MySQL and uploaded files across deployments. Set PHP upload_max_filesize >= 5M and post_max_size >= 6M, with matching proxy limits; the image defaults below provide room for the API's five-megabyte validation limit.

Run one scheduler every minute. The dispatch command itself runs every two minutes with a cache lock; no separate queue worker is required for the initial MySQL outbox. Laravel jobs tables remain available for future queued jobs. When changing CACHE_STORE, stop dispatchers first, clear config, and restart all processes together.

FCM uses the HTTP v1 API with short-lived OAuth access tokens fetched through google/auth. Grant the service account only the required Firebase messaging permission. Configure FIREBASE_CREDENTIALS as an absolute readable path. Invalid UNREGISTERED device tokens are deleted; transient failures retry, then remain failed for review. No real provider calls are made in tests.

Monitor `/up` for application boot, plus separate DB connectivity, scheduler heartbeat, oldest pending delivery, failed count, and storage usage. `/up` alone does not prove MySQL or FCM connectivity. Back up MySQL and photos together; test restoring both. User locations are pruned daily after 24 hours; define a retention policy for inboxes and reports before launch.

Useful upstream references: [Laravel deployment](https://laravel.com/docs/13.x/deployment), [Sanctum](https://laravel.com/docs/13.x/sanctum), [Scheduling](https://laravel.com/docs/13.x/scheduling), [FCM HTTP v1 authorization](https://firebase.google.com/docs/cloud-messaging/auth-server).
