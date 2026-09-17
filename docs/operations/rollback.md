# Rollback plan

1. Put the application into maintenance mode: `php artisan down`.
2. Revert application code to the last known-good release and reinstall its locked dependencies.
3. Only run migration rollback after confirming the migration is reversible and no newer data depends on it.
4. If data correctness is uncertain, restore the verified database and media backup using the restore runbook instead of forcing a migration rollback.
5. Rebuild caches: `php artisan config:cache && php artisan view:cache`.
6. Restore the storage link, bring the app up, then verify `/health`, login, preview, public article rendering, and scheduler registration.

Code rollback and database rollback are separate decisions. A code rollback alone may be safe; schema/data rollback is not automatically safe.
