# Project Overview

- Purpose: Ordina is a small-to-mid scale inventory management system built with Laravel + NativePHP, providing offline-capable desktop apps and a web-like UI for stock, transactions, customers, reports, and admin.
- Tech stack: PHP 8.1+, Laravel 10, NativePHP, Blade + Alpine.js + Tailwind, SQLite (embedded), Laravel Breeze, Laravel Excel, Vite.
- Structure: Typical Laravel app with `app/`, `config/`, `database/`, `resources/`, `routes/`, `tests/`. Docs under `docs/`. CI via GitHub Actions.
- Entrypoints: Native app via `php artisan native:serve`, assets via Vite.
- Notable features: Role/permission system (spatie/laravel-permission), offline sync, reports export (Excel), admin tools (logs, backup), API tokens.
