# Article management audit follow-up — 2026-09-16

Approved findings fixed: 1–5.

1. **R-26 / R-36 — Fixed:** Removed the non-functional subscription form. The footer now links to the real article archive.
2. **R-03 — Fixed in source:** Header search, theme, and locale controls now have a minimum 44px target (`h-11` / `min-h-11`).
3. **R-26 — Fixed:** The dashboard article menu now exposes publish and unpublish, each with confirmation and audit logging.
4. **R-09 / R-36 — Fixed:** Removed the unverified pulsing system-status claim. It is now a descriptive journal label.
5. **R-31 — Fixed:** Added an explicit design read, dial values, and concise rationale to `template/DESIGN.md`.

Additional requirement fixes:

- The article table now displays the publication timestamp as well as the updated date.
- New-article slugs now follow the title until an editor manually changes the slug.

Verification:

- `php artisan test --filter='Article(Workflow|EditorialWorkflow)Test'`: 8 passed, 21 assertions.
- `php artisan view:cache`: passed.
- `php -l` for both updated Livewire components: passed.
- `npm run build`: passed. Vite continues to warn that the CKEditor bundle exceeds 500 kB; this is non-blocking and unchanged in behaviour.

Not performed: visual click-through on a real mobile device. Source-level touch targets are corrected, but this still needs browser/device confirmation before claiming R-03/R-35 visual verification.
