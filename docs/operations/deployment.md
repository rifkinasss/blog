# Deployment checklist

Run these commands from the Laravel project directory. Set production environment variables first: `APP_ENV=production`, `APP_DEBUG=false`, a HTTPS `APP_URL`, `SESSION_SECURE_COOKIE=true`, and a non-public backup destination where available.

## Before deployment

1. Verify a recent backup: `php artisan backup:list`.
2. Install PHP dependencies: `composer install --no-dev --optimize-autoloader`.
3. Install and build frontend assets when dependencies changed: `npm ci && npm run build`.

## Deploy

```bash
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan view:cache
php artisan schedule:list
```

Use one scheduler process on the server:

```cron
* * * * * cd /path/to/Blog && php artisan schedule:run >> /dev/null 2>&1
```

Queues are not required for the current publishing workflow. Do not start a worker unless `QUEUE_CONNECTION` changes away from `sync` for a real operational need.

## After deployment

1. Request `GET /health` and confirm `{"status":"healthy"}`.
2. Sign in, save a draft, and open its signed preview.
3. Confirm one public article renders.
4. Check `/dashboard/settings?tab=system` after the scheduler runs.
5. Perform an authenticated OpenClaw API smoke test using a scoped service token; never place that token in shell history or logs.
