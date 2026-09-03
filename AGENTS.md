# Repository Guidelines

## Project Structure & Module Organization
This is a **Laravel 12** application using **Inertia.js** with **Vue 3**.
- **Frontend**: Located in `.\resources/js/`. Inertia pages are in `.\resources/js/Pages/`. UI uses **Tailwind CSS 4** and **DaisyUI 5**.
- **Backend**: Standard Laravel architecture. Models are in `.\app/Models/` (e.g., `.\app/Models/Book.php`), and Controllers are in `.\app/Http/Controllers/`.
- **Routes**: Web routes in `.\routes/web.php`, API-like routes for Inertia.
- **Database**: Migrations are in `.\database/migrations/`.

## Build, Test, and Development Commands
The project uses `composer` scripts to manage the development environment:
- `composer setup`: Full project initialization (installations, migrations, build).
- `composer dev`: Recommended way to run the local environment. Starts `php artisan serve`, queue, logs, and `vite` concurrently.
- `composer test`: Executes the test suite.
- `composer pint`: Fixes PHP code style.
- `php artisan test --filter <name>`: Run a specific test.
- `npm run dev`: Start Vite development server.
- `npm run build`: Compile production assets.

## Coding Style & Naming Conventions
- **PHP**: Follows Laravel standards, enforced by **Laravel Pint**. Use `snake_case` for methods/variables and `PascalCase` for classes.
- **Vue**: Use **Vue 3 Composition API** with `<script setup>`.
- **CSS**: Use Tailwind utility classes; avoid custom CSS where possible.

## Testing Guidelines
- **Framework**: Uses **Pest** for testing.
- **Structure**: Feature tests reside in `.\tests/Feature/`, unit tests in `.\tests/Unit/`.
- **Setup**: `.\tests/Pest.php` contains global expectations and traits (e.g., `RefreshDatabase`).

## Commit Guidelines
- Use concise, descriptive commit messages.
- Current project history shows German commit messages (e.g., "BookController auf MySQL geändert"), but English is generally preferred for consistency in most repositories.
