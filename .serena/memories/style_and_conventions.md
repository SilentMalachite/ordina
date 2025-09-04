# Style and Conventions

- Coding standard: PSR-12. Use Laravel best practices and descriptive names. Add comments when helpful (Japanese is fine).
- Formatting: Use Laravel Pint (`./vendor/bin/pint`).
- Commits: `type: description` (e.g., `feat: ...`, `fix: ...`, `docs: ...`). Japanese or English acceptable.
- Testing: Add tests for new features; ensure all tests pass (`php artisan test`).
- Security: Prefer bearer tokens for API. Keep `.env` out of backups; safe file/extension validation for downloads.
- Permissions: unified naming (e.g., `inventory-view` replaces `inventory-list`). See docs/Permissions.md.
