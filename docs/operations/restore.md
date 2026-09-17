# Restore runbook

There is intentionally no web restore button. Restoring production content is an operator action performed during a maintenance window.

## Prerequisites

- A verified archive produced by `php artisan operations:backup` or `php artisan backup:run`.
- PostgreSQL client tools compatible with the target server.
- Enough temporary disk space to unpack the archive.
- Maintenance access to the application and media disk.

## Procedure

1. Put the application into maintenance mode: `php artisan down`.
2. Copy the backup archive to a private temporary directory. Do not unpack archives beneath `public/`.
3. Verify the archive can be opened, then extract it in the temporary directory.
4. Create a safety backup of the current database before replacing it.
5. Restore the PostgreSQL dump with `psql` or `pg_restore`, matching the dump format. Use credentials supplied through the operator environment, never command-line literals.
6. Restore `storage/app/public` from the archive to the configured media disk.
7. Run `php artisan migrate --force`, `php artisan config:cache`, `php artisan view:cache`, and `php artisan storage:link`.
8. Bring the app up: `php artisan up`.

## Verification

- `GET /health` returns `healthy`.
- Dashboard login works.
- A restored draft opens through a newly generated signed preview.
- A public article and its featured image resolve correctly.
- The scheduler is visible in `php artisan schedule:list`.

## Rollback considerations

Database restore is destructive. Keep the pre-restore backup until validation is complete. Do not assume a code rollback reverses database changes; assess each migration before rolling it back.
