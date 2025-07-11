# Iron Code Skins 🎮

Plataforma de escrow jurídico para transações P2P de skins CS:GO com foco em segurança e compliance.

## 🚀 Quick Start

### Pré-requisitos
- Docker & Docker Compose
- Make
- Git

### Setup inicial
```bash
# Clone o repositório
git clone [URL_DO_REPO]
cd SKINS-MAIDEN

# Execute o setup
./setup-project-structure.sh
./create-config-files.sh
./initialize-projects.sh

# Configure e inicie
make setup
make start
```

### Acessos
- Frontend: http://localhost:3000
- Backend API: http://localhost/api
- MailHog: http://localhost:8025
- MySQL: localhost:3306

## 📁 Estrutura do Projeto

```
SKINS-MAIDEN/
├── backend/          # API Laravel
├── frontend/         # SPA Vue 3
├── webapp-audit/     # WebApp de auditoria
├── infrastructure/   # Docker, K8s, Terraform
├── contracts/        # Documentos legais
└── docs/            # Documentação
```

## �� Comandos Úteis

```bash
make help         # Lista todos comandos
make logs         # Ver logs
make test         # Rodar testes
make shell        # Acessar container
make permissions  # Corrigir permissões
```

## 🛡️ Segurança

- Todas as transações são auditadas
- Compliance LGPD implementado
- Hash blockchain para imutabilidade
- 2FA obrigatório

## 📝 Licença

Proprietária - Todos os direitos reservados.
