#!/bin/bash

##############################################################################
# 🚀 IRON CODE SKINS - SCRIPT DE DEPLOY AUTOMÁTICO EM PRODUÇÃO
# 
# Este script automatiza todo o processo de deploy da plataforma Iron Code Skins
# em ambiente de produção, incluindo configuração, build, deploy e validação.
#
# Uso: ./production-deploy.sh [opcoes]
# Opcoes:
#   --full          Deploy completo (default)
#   --update        Apenas atualizar aplicação
#   --rollback      Rollback para versão anterior
#   --status        Verificar status dos serviços
#   --backup        Criar backup antes do deploy
#   --logs          Visualizar logs em tempo real
##############################################################################

set -e  # Exit on any error

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Variáveis globais
PROJECT_DIR="/home/funil/SKINS-MAIDEN"
COMPOSE_FILE="docker-compose.prod.yml"
ENV_FILE=".env.production"
BACKUP_DIR="backups"
LOG_FILE="deployment.log"

# Função para logging
log() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1" | tee -a "$LOG_FILE"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1" | tee -a "$LOG_FILE"
    exit 1
}

success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1" | tee -a "$LOG_FILE"
}

warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1" | tee -a "$LOG_FILE"
}

info() {
    echo -e "${CYAN}[INFO]${NC} $1" | tee -a "$LOG_FILE"
}

# Banner do projeto
show_banner() {
    echo -e "${PURPLE}"
    cat << "EOF"
    ╔═══════════════════════════════════════════════════════════════╗
    ║                                                               ║
    ║              🚀 IRON CODE SKINS - PRODUCTION DEPLOY          ║
    ║                                                               ║
    ║         Plataforma de Trading de Skins CS:GO/CS2             ║
    ║                   Deploy Automático v1.0                     ║
    ║                                                               ║
    ╚═══════════════════════════════════════════════════════════════╝
EOF
    echo -e "${NC}"
}

# Verificar pré-requisitos
check_prerequisites() {
    log "🔍 Verificando pré-requisitos..."
    
    # Verificar se está no diretório correto
    if [[ ! -f "$PROJECT_DIR/$COMPOSE_FILE" ]]; then
        error "Arquivo $COMPOSE_FILE não encontrado em $PROJECT_DIR"
    fi
    
    # Verificar Docker
    if ! command -v docker &> /dev/null; then
        error "Docker não está instalado"
    fi
    
    # Verificar Docker Compose
    if ! command -v docker-compose &> /dev/null; then
        error "Docker Compose não está instalado"
    fi
    
    # Verificar arquivo de ambiente
    if [[ ! -f "$PROJECT_DIR/$ENV_FILE" ]]; then
        warning "Arquivo $ENV_FILE não encontrado, criando template..."
        create_env_template
    fi
    
    # Verificar se as portas estão livres
    check_ports
    
    success "✅ Todos os pré-requisitos verificados"
}

# Criar template do arquivo de ambiente
create_env_template() {
    cat > "$PROJECT_DIR/$ENV_FILE" << 'EOL'
# Iron Code Skins - Production Environment
APP_ENV=production
APP_DEBUG=false
APP_URL=https://ironcodeskins.com
API_URL=https://api.ironcodeskins.com

# Database
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=ironcode_skins
DB_USERNAME=ironcode_user
DB_PASSWORD=CHANGE_THIS_PASSWORD

# Redis
REDIS_HOST=redis
REDIS_PASSWORD=CHANGE_THIS_PASSWORD
REDIS_PORT=6379

# Steam API
STEAM_API_KEY=YOUR_STEAM_API_KEY
STEAM_BOT_USERNAME=YOUR_BOT_USERNAME
STEAM_BOT_PASSWORD=YOUR_BOT_PASSWORD
STEAM_BOT_SHARED_SECRET=YOUR_BOT_SHARED_SECRET
STEAM_BOT_IDENTITY_SECRET=YOUR_BOT_IDENTITY_SECRET

# Payment Gateways
STRIPE_SECRET_KEY=sk_live_YOUR_STRIPE_KEY
PIX_WEBHOOK_SECRET=YOUR_PIX_WEBHOOK_SECRET

# Security
JWT_SECRET=YOUR_JWT_SECRET_KEY
APP_KEY=base64:YOUR_APP_KEY

# SSL & Domain
LETSENCRYPT_EMAIL=admin@ironcodeskins.com
CLOUDFLARE_EMAIL=your@email.com
CLOUDFLARE_API_TOKEN=your_cloudflare_token

# Monitoring
GRAFANA_PASSWORD=CHANGE_THIS_PASSWORD

# Backups
AWS_ACCESS_KEY_ID=your_aws_key
AWS_SECRET_ACCESS_KEY=your_aws_secret
AWS_REGION=us-east-1
BACKUP_S3_BUCKET=ironcode-backups
EOL
    
    error "❌ Configure o arquivo $ENV_FILE com suas credenciais e execute novamente"
}

# Verificar portas
check_ports() {
    local ports=(80 443 3306 6379 9090 3000)
    for port in "${ports[@]}"; do
        if lsof -Pi :$port -sTCP:LISTEN -t >/dev/null 2>&1; then
            warning "⚠️  Porta $port já está em uso"
        fi
    done
}

# Criar backup
create_backup() {
    if [[ "$1" == "skip" ]]; then
        return 0
    fi
    
    log "💾 Criando backup..."
    
    local backup_name="backup-$(date +%Y%m%d-%H%M%S)"
    local backup_path="$PROJECT_DIR/$BACKUP_DIR/$backup_name"
    
    mkdir -p "$backup_path"
    
    # Backup do banco de dados
    if docker-compose -f "$COMPOSE_FILE" ps mysql | grep -q "Up"; then
        info "Fazendo backup do banco de dados..."
        docker-compose -f "$COMPOSE_FILE" exec -T mysql mysqldump \
            -u root -p"$DB_ROOT_PASSWORD" \
            --all-databases > "$backup_path/database.sql"
    fi
    
    # Backup dos volumes
    info "Fazendo backup dos volumes..."
    cp -r storage "$backup_path/" 2>/dev/null || true
    cp -r ssl "$backup_path/" 2>/dev/null || true
    
    # Comprimir backup
    tar -czf "$backup_path.tar.gz" -C "$PROJECT_DIR/$BACKUP_DIR" "$backup_name"
    rm -rf "$backup_path"
    
    success "✅ Backup criado: $backup_name.tar.gz"
    echo "$backup_name.tar.gz" > "$PROJECT_DIR/.last-backup"
}

# Build das imagens
build_images() {
    log "🔨 Fazendo build das imagens Docker..."
    
    cd "$PROJECT_DIR"
    
    # Build do backend
    info "Building backend image..."
    docker-compose -f "$COMPOSE_FILE" build backend
    
    # Build do frontend
    info "Building frontend image..."
    docker-compose -f "$COMPOSE_FILE" build frontend
    
    # Build do nginx
    info "Building nginx image..."
    docker-compose -f "$COMPOSE_FILE" build nginx
    
    success "✅ Build das imagens concluído"
}

# Deploy da aplicação
deploy_application() {
    log "🚀 Iniciando deploy da aplicação..."
    
    cd "$PROJECT_DIR"
    
    # Parar serviços se estiverem rodando
    info "Parando serviços existentes..."
    docker-compose -f "$COMPOSE_FILE" down || true
    
    # Remover volumes órfãos
    docker volume prune -f || true
    
    # Iniciar serviços de infraestrutura primeiro
    info "Iniciando serviços de infraestrutura..."
    docker-compose -f "$COMPOSE_FILE" up -d mysql redis
    
    # Aguardar MySQL estar pronto
    info "Aguardando MySQL estar pronto..."
    sleep 30
    
    # Executar migrações
    info "Executando migrações do banco..."
    docker-compose -f "$COMPOSE_FILE" run --rm backend php artisan migrate --force
    
    # Iniciar todos os serviços
    info "Iniciando todos os serviços..."
    docker-compose -f "$COMPOSE_FILE" up -d
    
    # Aguardar serviços estarem prontos
    sleep 60
    
    success "✅ Deploy da aplicação concluído"
}

# Configuração inicial
initial_setup() {
    log "⚙️  Executando configuração inicial..."
    
    cd "$PROJECT_DIR"
    
    # Gerar chave da aplicação se necessário
    info "Gerando chaves da aplicação..."
    docker-compose -f "$COMPOSE_FILE" exec backend php artisan key:generate --force || true
    docker-compose -f "$COMPOSE_FILE" exec backend php artisan jwt:secret --force || true
    
    # Cache das configurações
    info "Fazendo cache das configurações..."
    docker-compose -f "$COMPOSE_FILE" exec backend php artisan config:cache
    docker-compose -f "$COMPOSE_FILE" exec backend php artisan route:cache
    docker-compose -f "$COMPOSE_FILE" exec backend php artisan view:cache
    
    # Criar storage links
    info "Criando storage links..."
    docker-compose -f "$COMPOSE_FILE" exec backend php artisan storage:link || true
    
    # Seed do banco se necessário
    if docker-compose -f "$COMPOSE_FILE" exec backend php artisan db:table --table=users | grep -q "Empty set"; then
        info "Executando seeders..."
        docker-compose -f "$COMPOSE_FILE" exec backend php artisan db:seed
    fi
    
    success "✅ Configuração inicial concluída"
}

# Verificar status dos serviços
check_services() {
    log "🔍 Verificando status dos serviços..."
    
    cd "$PROJECT_DIR"
    
    echo -e "${CYAN}=== Status dos Containers ===${NC}"
    docker-compose -f "$COMPOSE_FILE" ps
    
    echo -e "\n${CYAN}=== Health Checks ===${NC}"
    
    # Verificar MySQL
    if docker-compose -f "$COMPOSE_FILE" exec mysql mysqladmin ping -h localhost >/dev/null 2>&1; then
        echo -e "MySQL: ${GREEN}✅ Online${NC}"
    else
        echo -e "MySQL: ${RED}❌ Offline${NC}"
    fi
    
    # Verificar Redis
    if docker-compose -f "$COMPOSE_FILE" exec redis redis-cli ping >/dev/null 2>&1; then
        echo -e "Redis: ${GREEN}✅ Online${NC}"
    else
        echo -e "Redis: ${RED}❌ Offline${NC}"
    fi
    
    # Verificar Backend API
    if curl -f http://localhost:8000/api/health >/dev/null 2>&1; then
        echo -e "Backend API: ${GREEN}✅ Online${NC}"
    else
        echo -e "Backend API: ${RED}❌ Offline${NC}"
    fi
    
    # Verificar Frontend
    if curl -f http://localhost:3000 >/dev/null 2>&1; then
        echo -e "Frontend: ${GREEN}✅ Online${NC}"
    else
        echo -e "Frontend: ${RED}❌ Offline${NC}"
    fi
    
    # Verificar Nginx
    if curl -f http://localhost >/dev/null 2>&1; then
        echo -e "Nginx: ${GREEN}✅ Online${NC}"
    else
        echo -e "Nginx: ${RED}❌ Offline${NC}"
    fi
}

# Mostrar logs
show_logs() {
    cd "$PROJECT_DIR"
    
    if [[ -n "$1" ]]; then
        docker-compose -f "$COMPOSE_FILE" logs -f "$1"
    else
        docker-compose -f "$COMPOSE_FILE" logs -f
    fi
}

# Rollback
rollback_deployment() {
    log "🔄 Executando rollback..."
    
    if [[ ! -f "$PROJECT_DIR/.last-backup" ]]; then
        error "❌ Nenhum backup encontrado para rollback"
    fi
    
    local last_backup=$(cat "$PROJECT_DIR/.last-backup")
    local backup_file="$PROJECT_DIR/$BACKUP_DIR/$last_backup"
    
    if [[ ! -f "$backup_file" ]]; then
        error "❌ Arquivo de backup não encontrado: $backup_file"
    fi
    
    warning "⚠️  Isso irá restaurar o sistema para o backup: $last_backup"
    read -p "Confirmar rollback? (y/N): " -n 1 -r
    echo
    
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        info "Rollback cancelado"
        exit 0
    fi
    
    # Parar serviços
    docker-compose -f "$COMPOSE_FILE" down
    
    # Extrair backup
    tar -xzf "$backup_file" -C "$PROJECT_DIR/$BACKUP_DIR"
    local backup_dir="$PROJECT_DIR/$BACKUP_DIR/$(basename "$last_backup" .tar.gz)"
    
    # Restaurar dados
    if [[ -f "$backup_dir/database.sql" ]]; then
        info "Restaurando banco de dados..."
        docker-compose -f "$COMPOSE_FILE" up -d mysql
        sleep 30
        docker-compose -f "$COMPOSE_FILE" exec -T mysql mysql \
            -u root -p"$DB_ROOT_PASSWORD" < "$backup_dir/database.sql"
    fi
    
    # Restaurar volumes
    [[ -d "$backup_dir/storage" ]] && cp -r "$backup_dir/storage" "$PROJECT_DIR/"
    [[ -d "$backup_dir/ssl" ]] && cp -r "$backup_dir/ssl" "$PROJECT_DIR/"
    
    # Reiniciar serviços
    docker-compose -f "$COMPOSE_FILE" up -d
    
    # Limpar backup temporário
    rm -rf "$backup_dir"
    
    success "✅ Rollback concluído"
}

# Atualizar aplicação
update_application() {
    log "🔄 Atualizando aplicação..."
    
    # Backup automático
    create_backup skip
    
    # Pull das mudanças
    info "Fazendo pull do repositório..."
    cd "$PROJECT_DIR"
    git pull origin main
    
    # Rebuild e deploy
    build_images
    deploy_application
    
    success "✅ Aplicação atualizada"
}

# URLs da aplicação
show_urls() {
    echo -e "\n${PURPLE}=== URLs da Aplicação ===${NC}"
    echo -e "Frontend:     ${CYAN}http://localhost${NC} | ${CYAN}https://ironcodeskins.com${NC}"
    echo -e "Backend API:  ${CYAN}http://localhost:8000${NC} | ${CYAN}https://api.ironcodeskins.com${NC}"
    echo -e "Grafana:      ${CYAN}http://localhost:3001${NC} | ${CYAN}https://monitoring.ironcodeskins.com${NC}"
    echo -e "Prometheus:   ${CYAN}http://localhost:9090${NC}"
    echo -e "Kibana:       ${CYAN}http://localhost:5601${NC}"
    echo -e "Swagger API:  ${CYAN}http://localhost:8000/api/documentation${NC}"
}

# Menu principal
show_menu() {
    echo -e "\n${CYAN}=== Opções Disponíveis ===${NC}"
    echo "1. Deploy completo (--full)"
    echo "2. Atualizar aplicação (--update)"
    echo "3. Verificar status (--status)"
    echo "4. Criar backup (--backup)"
    echo "5. Ver logs (--logs)"
    echo "6. Rollback (--rollback)"
    echo "7. Sair"
    echo
}

# Função principal
main() {
    show_banner
    
    case "${1:-}" in
        --full)
            check_prerequisites
            create_backup
            build_images
            deploy_application
            initial_setup
            check_services
            show_urls
            success "🎉 Deploy completo finalizado!"
            ;;
        --update)
            check_prerequisites
            update_application
            check_services
            show_urls
            success "🎉 Atualização finalizada!"
            ;;
        --status)
            check_services
            show_urls
            ;;
        --backup)
            create_backup
            ;;
        --logs)
            show_logs "${2:-}"
            ;;
        --rollback)
            rollback_deployment
            ;;
        --help|-h)
            echo "Uso: $0 [opcao]"
            echo "Opções:"
            echo "  --full      Deploy completo"
            echo "  --update    Atualizar aplicação"
            echo "  --status    Verificar status"
            echo "  --backup    Criar backup"
            echo "  --logs      Ver logs"
            echo "  --rollback  Fazer rollback"
            ;;
        *)
            while true; do
                show_menu
                read -p "Escolha uma opção (1-7): " choice
                case $choice in
                    1)
                        check_prerequisites
                        create_backup
                        build_images
                        deploy_application
                        initial_setup
                        check_services
                        show_urls
                        success "🎉 Deploy completo finalizado!"
                        break
                        ;;
                    2)
                        check_prerequisites
                        update_application
                        check_services
                        show_urls
                        success "🎉 Atualização finalizada!"
                        break
                        ;;
                    3)
                        check_services
                        show_urls
                        ;;
                    4)
                        create_backup
                        ;;
                    5)
                        echo "Digite o nome do serviço (ou Enter para todos):"
                        read service
                        show_logs "$service"
                        ;;
                    6)
                        rollback_deployment
                        break
                        ;;
                    7)
                        info "👋 Saindo..."
                        exit 0
                        ;;
                    *)
                        error "❌ Opção inválida"
                        ;;
                esac
            done
            ;;
    esac
}

# Trap para cleanup
trap 'echo -e "\n${YELLOW}Script interrompido pelo usuário${NC}"; exit 1' INT

# Executar função principal
main "$@"
