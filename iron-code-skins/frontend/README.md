# Iron Code Skins - Frontend

Sistema de auditoria blockchain desenvolvido em Vue 3 com Composition API, seguindo as especificações do Sprint 2.

## 📋 Visão Geral

Esta aplicação frontend implementa um sistema completo de auditoria de evidências com integração blockchain para garantir integridade e imutabilidade dos registros.

## 🛠️ Stack Tecnológica

- **Vue 3** - Framework JavaScript reativo com Composition API
- **Pinia** - Gerenciamento de estado moderno para Vue
- **Vue Router 4** - Roteamento single-page application
- **Tailwind CSS** - Framework CSS utility-first
- **Vite** - Build tool e dev server ultrarrápido
- **Axios** - Cliente HTTP para comunicação com APIs
- **Crypto-js** - Biblioteca para hashing SHA-256
- **WebRTC APIs** - Gravação nativa de tela, câmera e áudio

## 🏗️ Arquitetura do Projeto

```
src/
├── components/          # Componentes reutilizáveis
│   ├── audit/          # Componentes específicos de auditoria
│   │   ├── EvidenceRecorder.vue      # Gravação de evidências
│   │   ├── FileUploader.vue          # Upload de arquivos
│   │   └── RecordingCompleteModal.vue # Modal pós-gravação
│   ├── blockchain/     # Componentes de verificação blockchain
│   │   └── HashVerifier.vue          # Verificação de integridade
│   └── ui/            # Componentes de interface
│       └── NotificationContainer.vue # Sistema de notificações
├── stores/            # Gerenciamento de estado Pinia
│   ├── auditStore.js       # Estado de auditorias
│   └── evidenceStore.js    # Estado de evidências
├── services/          # Camada de comunicação com APIs
│   ├── auditService.js     # Serviços de auditoria
│   ├── evidenceService.js  # Serviços de evidências
│   └── blockchainService.js # Serviços blockchain
├── composables/       # Lógica reutilizável Vue Composition API
│   └── useNotifications.js # Composable de notificações
├── plugins/           # Plugins e extensões Vue
│   └── globals.js          # Métodos globais de formatação
├── router/            # Configuração de rotas
│   └── index.js            # Definição das rotas da aplicação
├── views/             # Páginas/views da aplicação
│   ├── Dashboard.vue       # Dashboard principal
│   └── AuditSystem.vue     # Sistema de auditoria principal
└── assets/            # Recursos estáticos
    └── css/               # Estilos personalizados
```

## 🔧 Funcionalidades Implementadas

### Sistema de Auditoria
- ✅ **Gravação de evidências**: Tela, câmera e áudio via WebRTC
- ✅ **Upload de arquivos**: Múltiplos formatos com progress tracking
- ✅ **Validação de transações**: Verificação de IDs de transação
- ✅ **Listagem de evidências**: Interface para visualizar evidências por transação

### Verificação Blockchain
- ✅ **Hash SHA-256**: Geração de hashes para integridade
- ✅ **Verificação multi-etapas**: Interface step-by-step para validação
- ✅ **Integração Polygon**: Preparado para blockchain Polygon
- ✅ **Relatórios de verificação**: Geração de relatórios de integridade

### Interface e UX
- ✅ **Dashboard responsivo**: Visão geral com estatísticas em tempo real
- ✅ **Sistema de notificações**: Notificações toast com ações customizadas
- ✅ **Navegação intuitiva**: Router com guards de autenticação
- ✅ **Design moderno**: Interface baseada em Tailwind CSS

### Gerenciamento de Estado
- ✅ **Pinia stores**: Gerenciamento reativo de evidências e auditorias
- ✅ **Persistência local**: Cache de dados para melhor UX
- ✅ **Estados de loading**: Indicadores visuais para operações assíncronas

## 🚀 Como Executar

### Pré-requisitos
```bash
# Node.js 16+ e npm
node --version  # v16+
npm --version   # v8+
```

### Instalação
```bash
# Clone o repositório
git clone <repository-url>
cd iron-code-skins/frontend

# Instale as dependências
npm install

# Execute em modo desenvolvimento
npm run dev

# Build para produção
npm run build
```

### Configuração de Ambiente
```bash
# Copie o arquivo de configuração
cp .env.example .env.local

# Configure as variáveis
VITE_API_BASE_URL=http://localhost:8000/api
VITE_BLOCKCHAIN_NETWORK=polygon-mumbai
VITE_POLYGON_RPC_URL=https://rpc-mumbai.maticvigil.com
```

## 📡 Integração com Backend

### Endpoints Esperados
```javascript
// Evidências
POST /api/evidences              # Upload de evidência
GET  /api/evidences              # Listar evidências
GET  /api/evidences/{id}         # Detalhes da evidência
PUT  /api/evidences/{id}/verify  # Verificar integridade

// Auditorias
POST /api/audits                 # Criar auditoria
GET  /api/audits/{id}            # Detalhes da auditoria
PUT  /api/audits/{id}/complete   # Finalizar auditoria

// Blockchain
POST /api/blockchain/register    # Registrar hash no blockchain
GET  /api/blockchain/verify/{hash} # Verificar hash no blockchain
```

### Formato de Dados
```javascript
// Evidência
{
  id: string,
  transaction_id: string,
  filename: string,
  file_size: number,
  mime_type: string,
  hash_sha256: string,
  blockchain_hash?: string,
  created_at: string,
  verified_at?: string
}

// Auditoria
{
  id: string,
  transaction_id: string,
  status: 'pending' | 'completed' | 'failed',
  evidences: Evidence[],
  blockchain_records: BlockchainRecord[],
  created_at: string,
  completed_at?: string
}
```

## 🔒 Recursos de Segurança

### Hash e Integridade
- **SHA-256**: Geração de hashes para todos os arquivos
- **Verificação dupla**: Hash local + verificação blockchain
- **Imutabilidade**: Registros não podem ser alterados após criação

### Autenticação e Autorização
- **JWT tokens**: Implementação pronta para autenticação
- **Guards de rota**: Proteção de rotas sensíveis
- **Sessão persistente**: Manutenção de login entre sessões

## 📱 Responsividade

A interface foi desenvolvida mobile-first com breakpoints:
- **Mobile**: < 640px
- **Tablet**: 640px - 1024px  
- **Desktop**: > 1024px

Componentes adaptam automaticamente layouts e funcionalidades.

## 🎨 Sistema de Design

### Cores Principais
- **Primary**: Blue-600 (#2563eb)
- **Success**: Green-600 (#16a34a)
- **Warning**: Yellow-600 (#ca8a04)
- **Error**: Red-600 (#dc2626)

### Tipografia
- **Headings**: Font Inter, peso 600-700
- **Body**: Font Inter, peso 400-500
- **Code**: Font Monaco, peso 400

## 🧪 Testes e Qualidade

### Estrutura de Testes (a implementar)
```bash
# Testes unitários
npm run test:unit

# Testes E2E
npm run test:e2e

# Coverage
npm run test:coverage
```

### Lint e Formatação
```bash
# ESLint
npm run lint

# Prettier
npm run format
```

## 📦 Build e Deploy

### Build de Produção
```bash
npm run build
# Gera pasta dist/ com arquivos otimizados
```

### Deploy Recomendado
- **Netlify**: Para SPAs estáticas
- **Vercel**: Para projetos Vue/Vite
- **AWS S3 + CloudFront**: Para projetos enterprise
- **Docker**: Container pronto em `/infrastructure/docker/`

## 🔄 Próximos Passos

### Sprint 3 - Melhorias
- [ ] Implementar testes unitários e E2E
- [ ] Adicionar suporte offline (PWA)
- [ ] Otimizar performance com lazy loading
- [ ] Implementar websockets para updates em tempo real

### Sprint 4 - Funcionalidades Avançadas
- [ ] Relatórios avançados com gráficos
- [ ] Export de dados (PDF, CSV, JSON)
- [ ] Sistema de permissões granulares
- [ ] Integração com múltiplas blockchains

## 🤝 Contribuição

### Padrões de Código
- **Vue 3 Composition API**: Sempre use `<script setup>` quando possível
- **TypeScript**: Gradualmente adicionar tipagem
- **Componentes**: Single File Components (.vue)
- **Commits**: Conventional Commits (feat, fix, docs, etc.)

### Estrutura de Componentes
```vue
<template>
  <!-- Template HTML -->
</template>

<script>
import { ref, computed, onMounted } from 'vue'

export default {
  name: 'ComponentName',
  components: {},
  props: {},
  emits: [],
  setup(props, { emit }) {
    // Composition API logic
    return {}
  }
}
</script>

<style scoped>
/* Tailwind CSS classes preferred */
</style>
```

## 📄 Licença

Este projeto é parte do sistema Iron Code Skins e segue as diretrizes de licenciamento da empresa.

---

**Status**: ✅ Sprint 2 Completo  
**Versão**: 1.0.0  
**Última atualização**: Janeiro 2025

Para dúvidas ou suporte, contate a equipe de desenvolvimento.
