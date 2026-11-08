HOST_UID ?= $(shell id -u)
HOST_GID ?= $(shell id -g)

export HOST_UID HOST_GID

COMPOSE ?= docker compose
SERVICE ?= php

.PHONY: build install test stan cs cs-fix

build:
	$(COMPOSE) build $(SERVICE)

install:
	$(COMPOSE) run --rm $(SERVICE) composer install

test:
	$(COMPOSE) run --rm $(SERVICE) composer test

stan:
	$(COMPOSE) run --rm $(SERVICE) composer stan

cs:
	$(COMPOSE) run --rm $(SERVICE) composer cs:check

cs-fix:
	$(COMPOSE) run --rm $(SERVICE) composer cs:fix
