DOCKER_FOLDER = .docker
DOCKER_DIR = cd $(DOCKER_FOLDER) &&
HOST_OWNER = $(shell id -u):$(shell id -g)

default: help

.PHONY: help
help: ## Display this help screen
	@grep -hP '^\w.*?:.*##.*$$' $(MAKEFILE_LIST) | sort -u | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

.PHONY: up
up: ## Start the container
	docker-compose -f .docker/docker-compose.yml up -d

.PHONY: down
down: ## Stop the container
	docker-compose -f .docker/docker-compose.yml down --remove-orphans --volumes

.PHONY: build
build: ## Build the container
	docker-compose -f .docker/docker-compose.yml build

.PHONY: bash
bash: ## Run bash in the PHP 8.2 container
	docker-compose -f .docker/docker-compose.yml run --rm php bash

.PHONY: bash83
bash83: ## Run bash in the PHP 8.3 container
	docker-compose -f .docker/docker-compose.yml run --rm php83 bash

.PHONY: bash84
bash84: ## Run bash in the PHP 8.4 container
	docker-compose -f .docker/docker-compose.yml run --rm php84 bash

.PHONY: test
test: ## Run PHPUnit tests
	@php vendor/bin/phpunit

.PHONY: stan
stan: ## Run PHPStan analysis
	@php vendor/bin/phpstan analyse

.PHONY: ecs
ecs: ## Run Easy Coding Standard
	@php vendor/bin/ecs check

.PHONY: ecs/fix
ecs/fix: ## Fix coding standard issues
	@php vendor/bin/ecs check --fix

.PHONY: qa
qa: ecs stan test ## Run all QA tools
