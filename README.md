<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="320" alt="Laravel Logo"></a></p>

## Mini Shop

Mini Shop is a small e-commerce demo built with:

- Laravel 12 (PHP 8.2+) for the backend and Blade/Inertia pages
- Vue 3 + Vite + Tailwind CSS for the frontend (SPA mounted at `/app`)
- Stripe for payments and webhooks

Features:

- Browse products, categories, and search
- Cart, checkout, and order details
- Admin area to manage products, admins, and view sales
- AI customer support chat for product and order details (Gemini / ChatGPT / Claude)

## Requirements

- PHP 8.2+, Composer
- Node.js 18+ and npm
- Database: SQLite (default) or MySQL

## Quick Start

1) Install PHP and JS dependencies and build assets

- `composer install`
- `npm install`

2) Environment and app key

- Copy `.env.example` to `.env` and set `APP_URL`
- Generate key: `php artisan key:generate`

3) Database

- SQLite (default): create `database/database.sqlite` and set `DB_CONNECTION=sqlite` in `.env`
- Or configure MySQL in `.env` (host, database, user, password)

4) Migrate and seed data

- Run migrations: `php artisan migrate`
- Seed sample data and ensure an admin exists: `php artisan db:seed`
  - A test user is created: email `test@example.com`, password `password`
  - To promote your account to admin, set `ADMIN_EMAIL` in `.env` before seeding or run `php artisan db:seed --class=AdminSeeder`

5) Public storage for uploads

- Create the storage symlink so uploaded images are served: `php artisan storage:link`

6) Run the app in development

- `composer dev` (starts PHP server, queue listener, logs, and Vite)
- App: http://localhost:8000
- Vue SPA: http://localhost:8000/app

## AI Customer Support Chat

This project includes a lightweight AI assistant that only answers questions about:
- Product details (on product pages, public)
- The signed-in customer’s own order details (on order pages)

Supported providers (API access available in Malaysia):
- Google Gemini (`gemini-1.5-flash` default)
- OpenAI ChatGPT (`gpt-4o-mini` default)
- Anthropic Claude (`claude-3-5-haiku-20241022` default)

Environment configuration (add to `.env`):

```
GOOGLE_API_KEY=your_google_api_key
OPENAI_API_KEY=your_openai_api_key
ANTHROPIC_API_KEY=your_anthropic_api_key
AI_DEFAULT_PROVIDER=gemini
GEMINI_MODEL=gemini-1.5-flash
OPENAI_MODEL=gpt-4o-mini
ANTHROPIC_MODEL=claude-3-5-haiku-20241022
```

Run database migrations to create the settings table:

```
php artisan migrate
```

Admins can switch the active AI provider and per-provider model at `/admin/ai`.

## Stripe Setup (optional but recommended)

Set these variables in `.env`:

- Backend: `STRIPE_SECRET` (secret key), `STRIPE_KEY` (publishable key), `CURRENCY` (e.g., `usd`)
- Frontend: `VITE_STRIPE_KEY` (publishable key for the SPA/Checkout UI)
- Webhooks: `STRIPE_WEBHOOK_SECRET`

Webhook endpoint (set this in your Stripe dashboard or via Stripe CLI):

- `POST /stripe/webhook`

Using Stripe CLI:

- `stripe listen --forward-to http://localhost:8000/stripe/webhook`

Notes:

- Checkout uses Payment Intents; ensure the queue worker is running (included in `composer dev`).
- If Stripe keys are not configured, the checkout UI will indicate Stripe is unavailable.

## Admin Area

- Admin dashboard: `http://localhost:8000/admin`
- Only users with `is_admin = true` can access
- Admin login page: `http://localhost:8000/admin/login`

## Common Commands

- Migrate: `php artisan migrate`
- Seed all: `php artisan db:seed`
- Seed products only: `php artisan db:seed --class=ProductSeeder`
- Create storage link: `php artisan storage:link`
- Build assets: `npm run build` (dev server: `npm run dev`)

## Project Scripts

- `composer setup` — install, create `.env`, keygen, migrate, npm install, build
- `composer dev` — run PHP server, queue worker, logs, and Vite together
- `composer test` — run the test suite

## Folder Highlights

- Backend routes: `routes/web.php`
- Blade views: `resources/views`
- Inertia/Vue pages: `resources/js/Pages`
- SPA (Vue Router) mounted at `/app`: `resources/js/spa`
- Seeders: `database/seeders`
- Stripe config: `config/stripe.php`
