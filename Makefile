.PHONY: help setup start stop restart logs test lint clean shell permissions

# Detectar UID e GID do usuário atual
export UID := $(shell id -u)
export GID := $(shell id -g)

help:
    @echo "Iron Code Skins - Comandos disponíveis:"
    @echo "  make setup       - Configura o projeto pela primeira vez"
    @echo "  make start       - Inicia os containers"
    @echo "  make stop        - Para os containers"
    @echo "  make restart     - Reinicia os containers"
    @echo "  make logs        - Mostra os logs"
    @echo "  make test        - Roda os testes"
    @echo "  make lint        - Roda o linter"
    @echo "  make clean       - Limpa arquivos temporários"
    @echo "  make shell       - Acessa o shell do container app"
    @echo "  make permissions - Corrige permissões dos arquivos"

setup: permissions
    @echo "🚀 Configurando projeto..."
    cd backend && composer install
    cd frontend && npm install
    cp backend/.env.example backend/.env 2>/dev/null || true
    docker-compose up -d
    docker-compose exec app php artisan key:generate
    docker-compose exec app php artisan migrate
    @echo "✅ Setup completo!"

start:
    docker-compose up -d

stop:
    docker-compose down

restart:
    docker-compose down
    docker-compose up -d

logs:
    docker-compose logs -f

test:
    docker-compose exec app php artisan test
    cd frontend && npm test

lint:
    docker-compose exec app ./vendor/bin/phpcs 2>/dev/null || echo "PHPCs não instalado ainda"
    cd frontend && npm run lint

clean:
    docker-compose down -v
    rm -rf backend/vendor
    rm -rf frontend/node_modules
    rm -rf backend/storage/logs/*
    find . -name "*.log" -delete
    find . -name ".DS_Store" -delete

# Comandos de Produção
build-prod:
    @echo "🏗️ Construindo imagens de produção..."
    docker-compose -f docker-compose.prod.yml build

start-prod:
    @echo "🚀 Iniciando ambiente de produção..."
    docker-compose -f docker-compose.prod.yml up -d

stop-prod:
    @echo "⏹️ Parando ambiente de produção..."
    docker-compose -f docker-compose.prod.yml down

restart-prod:
    @echo "🔄 Reiniciando ambiente de produção..."
    docker-compose -f docker-compose.prod.yml down
    docker-compose -f docker-compose.prod.yml up -d

logs-prod:
    docker-compose -f docker-compose.prod.yml logs -f

backup:
    @echo "💾 Realizando backup do banco de dados..."
    docker-compose -f docker-compose.prod.yml exec postgres pg_dump -U ironcode iron_code_skins > backup_$(shell date +%Y%m%d_%H%M%S).sql
    @echo "✅ Backup concluído!"

load-test:
    @echo "⚡ Executando teste de carga..."
    docker run --rm --network host -v $(PWD)/infrastructure/k6:/scripts grafana/k6 run /scripts/load-test.js

monitor:
    @echo "📊 Abrindo monitoramento..."
    @echo "Grafana: http://localhost:3001 (admin/secret)"
    @echo "Prometheus: http://localhost:9090"
    @echo "Alertmanager: http://localhost:9093"

shell:
    docker-compose exec app bash

permissions:
    @echo "🔧 Corrigindo permissões..."
    sudo chown -R $(UID):$(GID) .
    chmod -R 755 backend/storage
    chmod -R 755 backend/bootstrap/cache
