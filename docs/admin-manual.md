# Manual do Administrador - Iron Code Skins

## 🛠️ Guia de Operações e Manutenção

Este manual destina-se à equipe técnica responsável pela operação e manutenção da plataforma Iron Code Skins.

## 🏗️ Arquitetura do Sistema

### Stack Tecnológica
- **Backend:** Laravel 10 (PHP 8.2)
- **Frontend:** React/Vue.js
- **Database:** PostgreSQL 15
- **Cache:** Redis 7
- **Web Server:** Nginx
- **Containers:** Docker & Docker Compose
- **Monitoramento:** Prometheus + Grafana
- **Alertas:** Alertmanager

### Serviços Principais
- **app:** Aplicação Laravel (Backend)
- **nginx:** Servidor web e proxy reverso
- **postgres:** Banco de dados principal
- **redis:** Cache e sessões
- **prometheus:** Coleta de métricas
- **grafana:** Dashboards de monitoramento
- **alertmanager:** Gerenciamento de alertas

## 🚀 Comandos de Deploy e Operação

### Ambiente de Desenvolvimento
```bash
# Configuração inicial
make setup

# Iniciar ambiente
make start

# Parar ambiente
make stop

# Ver logs
make logs

# Acessar shell do container
make shell
```

### Ambiente de Produção
```bash
# Construir imagens de produção
make build-prod

# Iniciar produção
make start-prod

# Parar produção
make stop-prod

# Reiniciar produção
make restart-prod

# Ver logs de produção
make logs-prod

# Realizar backup
make backup

# Executar teste de carga
make load-test

# Acessar dashboards de monitoramento
make monitor
```

## 📊 Monitoramento e Observabilidade

### Grafana (http://localhost:3001)
- **Usuário:** admin
- **Senha:** secret (configurável via variável `GRAFANA_PASSWORD`)

**Dashboards Disponíveis:**
- Sistema (CPU, Memória, Disco, Rede)
- Aplicação (Requisições, Tempo de resposta, Erros)
- Banco de Dados (Conexões, Queries, Performance)
- Redis (Comandos, Memória, Hit rate)

### Prometheus (http://localhost:9090)
**Métricas Importantes:**
- `up`: Status dos serviços
- `node_cpu_seconds_total`: CPU do sistema
- `node_memory_MemAvailable_bytes`: Memória disponível
- `container_cpu_usage_seconds_total`: CPU dos containers
- `http_requests_total`: Total de requisições HTTP

### Alertmanager (http://localhost:9093)
**Alertas Configurados:**
- **HostHighCpuLoad:** CPU > 80% por 5 minutos
- **HostHighMemoryLoad:** Memória > 85% por 5 minutos
- **ContainerKilled:** Container desapareceu das métricas
- **ContainerCpuUsage:** Container usando > 80% CPU

## 🗄️ Gestão do Banco de Dados

### Backups Automáticos
```bash
# Backup manual
make backup

# Backup agendado (adicionar ao crontab)
0 2 * * * cd /path/to/project && make backup
```

### Restore de Backup
```bash
# Parar aplicação
make stop-prod

# Restaurar backup
docker-compose -f docker-compose.prod.yml exec postgres psql -U ironcode -d iron_code_skins < backup_YYYYMMDD_HHMMSS.sql

# Reiniciar aplicação
make start-prod
```

### Migrations
```bash
# Executar migrations
docker-compose -f docker-compose.prod.yml exec app php artisan migrate

# Rollback da última migration
docker-compose -f docker-compose.prod.yml exec app php artisan migrate:rollback

# Status das migrations
docker-compose -f docker-compose.prod.yml exec app php artisan migrate:status
```

## 🔍 Troubleshooting

### Problemas Comuns

#### Aplicação não inicia
1. Verificar logs: `make logs-prod`
2. Verificar status dos containers: `docker-compose -f docker-compose.prod.yml ps`
3. Verificar conectividade com banco: `docker-compose -f docker-compose.prod.yml exec app php artisan tinker`

#### Performance degradada
1. Verificar métricas no Grafana
2. Analisar queries lentas no PostgreSQL
3. Verificar uso de cache Redis
4. Executar `php artisan optimize:clear` se necessário

#### Banco de dados com problemas
1. Verificar conectividade: `docker-compose -f docker-compose.prod.yml exec postgres pg_isready`
2. Verificar logs: `docker-compose -f docker-compose.prod.yml logs postgres`
3. Executar vacuum: `docker-compose -f docker-compose.prod.yml exec postgres psql -U ironcode -c "VACUUM ANALYZE;"`

### Comandos de Diagnóstico
```bash
# Status geral do sistema
docker-compose -f docker-compose.prod.yml ps

# Uso de recursos dos containers
docker stats

# Logs de um serviço específico
docker-compose -f docker-compose.prod.yml logs -f app

# Health check manual
curl http://localhost/api/health

# Verificar conectividade Redis
docker-compose -f docker-compose.prod.yml exec redis redis-cli ping

# Verificar conectividade PostgreSQL
docker-compose -f docker-compose.prod.yml exec postgres pg_isready -U ironcode
```

## 🔒 Segurança e Compliance

### Configurações de Segurança
- **HTTPS:** Obrigatório em produção
- **Rate Limiting:** Configurado no Nginx
- **Firewall:** Apenas portas necessárias expostas
- **Backup Criptografado:** Backups com criptografia AES-256

### LGPD Compliance
- **Portal de Privacidade:** `/api/v1/privacy/*`
- **Logs de Auditoria:** Todas as ações são logadas
- **Retenção de Dados:** Política de 7 anos para dados financeiros
- **Anonimização:** Dados são anonimizados, não deletados

### Procedimentos de Emergência
1. **Incidente de Segurança:**
   - Isolar o sistema: `make stop-prod`
   - Analisar logs: `make logs-prod`
   - Notificar DPO e equipe legal
   - Documentar incident response

2. **Vazamento de Dados:**
   - Contenção imediata
   - Notificação ANPD em 72h
   - Comunicação aos usuários afetados
   - Auditoria completa

## 📈 Otimização de Performance

### Cache Strategy
```bash
# Limpar cache da aplicação
docker-compose -f docker-compose.prod.yml exec app php artisan cache:clear

# Limpar cache de configuração
docker-compose -f docker-compose.prod.yml exec app php artisan config:clear

# Limpar cache de rotas
docker-compose -f docker-compose.prod.yml exec app php artisan route:clear

# Otimizar para produção
docker-compose -f docker-compose.prod.yml exec app php artisan optimize
```

### Otimização do Banco
```sql
-- Verificar queries lentas
SELECT query, mean_time, calls 
FROM pg_stat_statements 
ORDER BY mean_time DESC 
LIMIT 10;

-- Reindexar tabelas críticas
REINDEX INDEX CONCURRENTLY users_email_index;
REINDEX INDEX CONCURRENTLY contracts_status_index;
```

## 🧪 Testes de Carga

### Executar Teste de Carga
```bash
# Teste padrão (300 usuários simultâneos)
make load-test

# Teste customizado
docker run --rm --network host -v $(PWD)/infrastructure/k6:/scripts grafana/k6 run --vus 500 --duration 10m /scripts/load-test.js
```

### Interpretar Resultados
- **http_req_duration:** Tempo de resposta (p95 < 500ms)
- **http_req_failed:** Taxa de erro (< 10%)
- **http_reqs:** Requisições por segundo
- **vus:** Usuários virtuais ativos

## 📋 Checklist de Deploy

### Pré-Deploy
- [ ] Executar testes automatizados
- [ ] Realizar backup completo
- [ ] Verificar configurações de produção
- [ ] Confirmar recursos de infraestrutura

### Deploy
- [ ] `make build-prod`
- [ ] `make stop-prod`
- [ ] `make start-prod`
- [ ] Verificar health checks
- [ ] Executar smoke tests

### Pós-Deploy
- [ ] Monitorar métricas por 30 minutos
- [ ] Verificar logs de erro
- [ ] Testar funcionalidades críticas
- [ ] Notificar stakeholders

## 📞 Contatos de Emergência

### Equipe Técnica
- **DevOps Lead:** devops@ironcodeskins.com
- **Backend Lead:** backend@ironcodeskins.com
- **DBA:** dba@ironcodeskins.com

### Escalation
1. **Nível 1:** Engenheiro de plantão
2. **Nível 2:** Tech Lead
3. **Nível 3:** CTO
4. **Crítico:** CEO + Advogados

### SLA Commitments
- **Uptime:** 99.9% mensal
- **Response Time:** < 200ms (p95)
- **Error Rate:** < 0.1%
- **Support Response:** < 4h úteis

---

**Importante:** Este manual deve ser atualizado a cada mudança significativa na arquitetura ou procedimentos operacionais.
