HOST_UID ?= $(shell id -u)
HOST_GID ?= $(shell id -g)

COMPOSE ?= docker compose
SERVICE ?= php

.PHONY: build install test analyse phpstan

build:
	HOST_UID=$(HOST_UID) HOST_GID=$(HOST_GID) $(COMPOSE) build $(SERVICE)

install:
	$(COMPOSE) run --rm $(SERVICE) composer install

test:
	$(COMPOSE) run --rm $(SERVICE) composer test

stan:
	$(COMPOSE) run --rm $(SERVICE) composer stan

cs:
	$(COMPOSE) run --rm $(SERVICE) composer cs:check
