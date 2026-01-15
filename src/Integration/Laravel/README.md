# Laravel Adapter (Laravel 12)

This folder contains Laravel 12 integration pieces for `tetthys/activity-log` core.

## What you get

- Service provider that binds core contracts to Laravel implementations.
- Database writer for `activity_logs`.
- Counter store with atomic increment upsert (MySQL / Postgres).
- Cache-backed idempotency store.
- Optional request context middleware + contextual logger wrapper.
- Config + migration stub (publishable).

## Laravel 12 notes

Middleware aliases and pipeline configuration live in `bootstrap/app.php` in Laravel 11+ / 12.
See Laravel docs:
- Service Providers: https://laravel.com/docs/12.x/providers
- Middleware: https://laravel.com/docs/12.x/middleware
- Query Builder Upsert: https://laravel.com/docs/12.x/queries
