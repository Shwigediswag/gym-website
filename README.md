# Gym

Symfony 7.4 · PHP 8.3 · MariaDB · Tailwind v4 · Symfony UX (Twig Components, Live Components, Stimulus, Turbo).
Read `HANDOFF.md` (source of truth) and `PLANNING.md` (phased plan) before touching code.

## Dev setup

Requirements: Docker Desktop. Nothing else is needed on the host.

```bash
docker compose up -d --wait
docker compose exec php composer install
docker compose exec php php bin/console doctrine:migrations:migrate -n
docker compose exec php php bin/console doctrine:fixtures:load -n
docker compose exec php php bin/console tailwind:build
```

Site: http://localhost:8088 · Mailpit: http://localhost:8026 · DB: localhost:3307 (gym / gym).
Host ports are set in `.env` (`NGINX_PORT`, `DB_PORT`, `MAILPIT_PORT`, `MAILPIT_SMTP_PORT`); Compose reads `.env`, not `.env.local`.



Shortcuts: `make <target>` (Linux/macOS) or `./make.ps1 <target>` (Windows).
Targets: `up down build sh install db migrate migration fixtures assets tailwind lint phpstan test reset`.

The dev superadmin is seeded by fixtures from `SUPERADMIN_EMAIL` / `SUPERADMIN_PASSWORD` in `.env` (override in `.env.local`).
For production create one with `bin/console app:user:create-superadmin <email>`.

## Tests

`./make.ps1 test` runs twig/yaml/container lint, PHPStan (level 6) and PHPUnit against the `gym_test` database.
