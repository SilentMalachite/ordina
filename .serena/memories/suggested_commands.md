# Suggested Commands

- Clone + enter: `git clone https://github.com/SilentMalachite/ordina.git && cd ordina`
- Install deps: `composer install` and `npm install`
- Env + app key: `cp .env.example .env && php artisan key:generate`
- DB setup: `php artisan migrate && php artisan db:seed`
- Seed roles/permissions: `php artisan db:seed --class=RolesAndPermissionsSeeder`
- Dev servers:
  - NativePHP: `php artisan native:serve`
  - Vite (assets): `npm run dev`
- Build assets: `npm run build`
- Run tests: `php artisan test`
- Format code: `./vendor/bin/pint`
