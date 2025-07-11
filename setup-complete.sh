##!/bin/bash
# filepath: /home/funil/SKINS-MAIDEN/setup-complete.sh

# Script master que executa todo o setup

echo "🎮 IRON CODE SKINS - SETUP COMPLETO"
echo "===================================="
echo ""

# Verificar se está rodando como root
if [ "$EUID" -eq 0 ]; then 
   echo "❌ Não execute este script como root!"
   exit 1
fi

# Tornar scripts executáveis
chmod +x setup-project-structure.sh
chmod +x create-config-files.sh
chmod +x initialize-projects.sh

# Executar em ordem
echo "📁 Etapa 1/3: Criando estrutura..."
./setup-project-structure.sh

echo -e "\n📝 Etapa 2/3: Criando configurações..."
./create-config-files.sh

echo -e "\n🚀 Etapa 3/3: Inicializando projetos..."
./initialize-projects.sh

echo -e "\n✨ Setup completo!"
echo -e "\n📋 Para iniciar o desenvolvimento:"
echo -e "  ${YELLOW}make setup${NC}  # Configura o ambiente"
echo -e "  ${YELLOW}make start${NC}  # Inicia os containers"
echo -e "  ${YELLOW}make logs${NC}   # Monitora os logs"

# Criar alias úteis
echo -e "\n💡 Dica: Adicione estes aliases ao seu ~/.bashrc:"
echo 'alias ironcode="cd ~/SKINS-MAIDEN"'
echo 'alias ironcode-logs="cd ~/SKINS-MAIDEN && make logs"'
echo 'alias ironcode-shell="cd ~/SKINS-MAIDEN && make shell"'