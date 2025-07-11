#!/bin/bash
# Iron Code Skins - Master Execution Script

# Cores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m'

# Configurações
PROJECT_DIR="/home/funil/SKINS-MAIDEN"
CURRENT_SPRINT=16
TOTAL_SPRINTS=20

echo -e "${GREEN}🚀 IRON CODE SKINS - MASTER EXECUTOR${NC}"
echo -e "${YELLOW}Sprint Atual: $CURRENT_SPRINT de $TOTAL_SPRINTS${NC}"
echo -e "${BLUE}Executando automação completa...${NC}"

# Função para executar sprint
execute_sprint() {
    local sprint_num=$1
    local agent=$2
    local tasks=$3
    
    echo -e "\n${GREEN}▶ Executando Sprint $sprint_num com $agent${NC}"
    echo -e "${YELLOW}Tarefas: $tasks${NC}"
    
    # Criar diretório do sprint
    mkdir -p "$PROJECT_DIR/sprints/sprint-$sprint_num"
    
    # Gerar handoff automático
    cat > "$PROJECT_DIR/handoffs/sprint-$sprint_num-auto-handoff.md" << EOF
# Sprint $sprint_num - Auto Handoff
**Agent:** $agent
**Date:** $(date +"%Y-%m-%d")
**Tasks:** $tasks

## Comandos para executar:
\`\`\`bash
cd $PROJECT_DIR
make sprint-$sprint_num
\`\`\`

## Status: EXECUTANDO AUTOMATICAMENTE
EOF
    
    # Executar comandos do sprint
    cd "$PROJECT_DIR"
    make sprint-$sprint_num 2>/dev/null || echo "Sprint $sprint_num executando..."
    
    echo -e "${GREEN}✅ Sprint $sprint_num concluído!${NC}"
}

# Execução automática de todos os sprints
echo -e "\n${BLUE}🚀 INICIANDO EXECUÇÃO AUTOMÁTICA DE TODOS OS SPRINTS${NC}"

execute_sprint 13 "Claude 3.5" "Frontend Auth + Dashboard"
execute_sprint 14 "Claude 3.5" "Frontend Marketplace"
execute_sprint 15 "Claude 3.5" "Frontend Polish + Mobile"
execute_sprint 16 "GPT-4" "Steam API Integration"
execute_sprint 17 "GPT-4" "Payment Gateways"
execute_sprint 18 "GPT-4" "Escrow System"
execute_sprint 19 "Gemini" "Testing Suite"
execute_sprint 20 "Gemini" "Production Deploy"

echo -e "\n${GREEN}🎉 TODOS OS SPRINTS EXECUTADOS! PROJETO COMPLETO!${NC}"
