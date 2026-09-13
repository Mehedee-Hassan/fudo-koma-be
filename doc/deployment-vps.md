# Hostinger VPS deployment

Target: VPS 844958, `31.97.49.68` (`srv844958.hstgr.cloud`), Ubuntu 24.04 with Docker. Repository: https://github.com/Mehedee-Hassan/fudo-koma-be/.

The current VPS Compose configuration supports private staging through an SSH tunnel. It does not expose a public API. The default `compose.yaml` is for local development; use `scripts/vps` for this deployment.

## Before deploying

- Commit and push the VPS Compose file, `deploy/`, `scripts/`, and the Docker ignore changes so the server can obtain them from GitHub. Never commit `.env.vps` or Firebase credentials.
- Verify the application tests and formatting in a running development environment: `docker compose exec -T app php artisan test` and `docker compose exec -T app vendor/bin/pint --test`.
- Verify SSH access to the server and availability of Docker Compose, Git, and Python 3. Use your configured SSH username in place of `SSH_USER` below.
- For a private repository, configure read-only GitHub access on the VPS before cloning. Do not put access tokens in clone URLs.

## First private staging deployment

Connect from your computer:

```sh
ssh SSH_USER@31.97.49.68
```

On the VPS, clone into a directory writable by your deployment user:

```sh
git clone https://github.com/Mehedee-Hassan/fudo-koma-be.git
cd fudo-koma-be
python3 scripts/init-vps-env.py
./scripts/deploy-vps
./scripts/vps exec app php artisan app:create-admin YOUR_ADMIN_EMAIL
```

The environment generator creates private credentials once and preserves existing ones. Keep the application key stable and back it up securely. The deployment script builds images, starts MySQL, stops application writes, runs migrations, and starts the application and scheduler. Updates involve downtime. A failed migration requires investigation before restarting services; do not blindly rerun migrations or delete volumes.

From your computer, open the tunnel and keep it running:

```sh
ssh -N -L 8080:127.0.0.1:8080 SSH_USER@31.97.49.68
```

Visit `http://localhost:8080/admin`. The API base URL is `http://localhost:8080/api/v1`. These addresses work only on the computer running the tunnel.

## Verification

On the VPS:

```sh
./scripts/vps ps
curl --fail http://127.0.0.1:8080/up
./scripts/vps exec -T app php artisan migrate:status
./scripts/vps exec -T scheduler php artisan schedule:list
```

Also verify admin login, an API request using the database, photo upload and retrieval, and scheduled notification processing. `/up` checks application boot only; it does not establish database or Firebase health. Push is disabled by default.

## Before public launch

Configure an HTTPS reverse proxy to the loopback web port, a public hostname, `APP_URL`, secure session cookies, explicit CORS origins, and trusted proxy handling that preserves the original request scheme and client address. Keep MySQL and PHP-FPM private. Configure Firebase with a private read-only credentials mount if push is required.

Establish off-server backups of MySQL, uploaded files, and required secrets; test restoring them. Add disk, database, scheduler, and failed-delivery monitoring. Review the public-launch limitations in [system-design.md](system-design.md), particularly public photo access after moderation.

Do not use `docker compose down -v`: it deletes persistent volumes. Before updates, back up data and record the deployed Git commit. Reverting application code does not automatically reverse database migrations.

Automated GitHub deployment and public HTTPS are not configured by these scripts.
