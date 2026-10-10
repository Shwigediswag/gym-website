# Gym — dev targets. Everything runs inside the php container.
# Windows without make: use ./make.ps1 <target> (same names).
COMPOSE = docker compose
PHP     = $(COMPOSE) exec php
CONSOLE = $(PHP) php bin/console

.PHONY: up down build sh install db migrate migration fixtures assets tailwind test lint phpstan cs reset

up:        ## start the stack
	$(COMPOSE) up -d
down:      ## stop the stack
	$(COMPOSE) down
build:     ## rebuild images
	$(COMPOSE) build
sh:        ## shell in php container
	$(PHP) bash
install:   ## composer install
	$(PHP) composer install
db:        ## create database if missing
	$(CONSOLE) doctrine:database:create --if-not-exists
migrate:   ## run migrations
	$(CONSOLE) doctrine:migrations:migrate -n
migration: ## generate a migration from entity diff
	$(CONSOLE) make:migration
fixtures:  ## load fixtures (dev)
	$(CONSOLE) doctrine:fixtures:load -n
assets:    ## compile assets (tailwind + asset-map)
	$(CONSOLE) tailwind:build --minify
	$(CONSOLE) asset-map:compile
tailwind:  ## tailwind watcher
	$(CONSOLE) tailwind:build --watch
lint:      ## twig / yaml / container lint
	$(CONSOLE) lint:twig templates
	$(CONSOLE) lint:yaml config translations
	$(CONSOLE) lint:container
phpstan:   ## static analysis
	$(PHP) vendor/bin/phpstan analyse --memory-limit=1G
test:      ## full test suite (lint + phpstan + phpunit)
	$(MAKE) lint
	$(MAKE) phpstan
	$(CONSOLE) --env=test doctrine:database:create --if-not-exists
	$(CONSOLE) --env=test doctrine:migrations:migrate -n
	$(PHP) vendor/bin/phpunit
reset:     ## drop + recreate db, migrate, fixtures
	$(CONSOLE) doctrine:database:drop --force --if-exists
	$(MAKE) db migrate fixtures
