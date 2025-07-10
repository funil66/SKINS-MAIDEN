# Módulos do Sistema - Iron Code Skins

## 1. Módulo de Autenticação e Autorização

### 1.1 Funcionalidades
- Login via Steam OAuth
- Login tradicional (email/senha)
- Multi-Factor Authentication (MFA)
- Gestão de sessões
- Roles e permissões (RBAC)

### 1.2 Componentes Técnicos
```
- AuthController.php
- SteamAuthService.php
- JWTManager.php
- PermissionMiddleware.php
- SessionRepository.php
```

## 2. Módulo de KYC (Know Your Customer)

### 1.1 Funcionalidades
- Upload de documentos
- Verificação biométrica (selfie)
- Validação de CPF/CNPJ
- Comprovante de residência
- Status de verificação em tempo real

### 1.2 Integrações
- Serviço de OCR para documentos
- API de validação de CPF (Receita Federal)
- Serviço de liveness detection
- Background check service

## 3. Módulo de Transações

### 1.1 Funcionalidades
- Criação de ordem de escrow
- Depósito de fundos
- Verificação de inventário Steam
- Liberação condicional
- Disputa e mediação

### 1.2 Estados da Transação
```
INITIATED -> FUNDED -> ITEM_DEPOSITED -> IN_VERIFICATION 
-> APPROVED -> COMPLETED | DISPUTED -> RESOLVED | CANCELLED
```

## 4. Módulo de Pagamentos

### 1.1 Métodos Suportados
- PIX (instantâneo)
- Cartão de Crédito/Débito
- Criptomoedas (BTC, ETH, USDT)
- Boleto bancário

### 1.2 Funcionalidades
- Gateway abstraction layer
- Retry mechanism
- Reconciliação automática
- Relatórios fiscais
- Split de pagamentos

## 5. Módulo de Auditoria

### 1.1 WebApp Client-Side
- Hash verification
- Screenshot automático
- Gravação de tela
- Assinatura digital (e-CPF)
- Timestamp confiável

### 1.2 Audit Trail
- Logs imutáveis
- Chain of custody
- Exportação para compliance
- Busca avançada

## 6. Módulo de Reputação (LRA)

### 1.1 Componentes
- Score calculation engine
- Historical transaction data
- Behavioral analytics
- Fraud detection patterns

### 1.2 Blockchain Integration
- Daily hash anchoring
- Merkle tree construction
- Smart contract interaction
- IPFS for data storage

## 7. Módulo de Comunicação

### 1.1 Canais
- Email transacional
- SMS para alertas críticos
- Push notifications (web)
- Chat integrado

### 1.2 Templates
- Confirmação de transação
- Alertas de segurança
- Atualizações de status
- Marketing (opt-in)

## 8. Módulo de Suporte

### 1.1 Funcionalidades
- Sistema de tickets
- Base de conhecimento
- Chat ao vivo (Premium)
- Escalação automática

### 1.2 SLA Management
- Tempo de resposta tracking
- Priorização por tier
- Métricas de satisfação
- Relatórios gerenciais

## 9. Módulo de Compliance

### 1.1 LGPD
- Consentimento management
- Data export (takeout)
- Direito ao esquecimento
- Audit de acessos

### 1.2 Anti-Lavagem
- Transaction monitoring
- Suspicious activity reports
- PEP screening
- Sanctions list checking

## 10. Módulo de Analytics

### 1.1 Business Intelligence
- Dashboard executivo
- Métricas de conversão
- Análise de cohort
- Previsão de churn

### 1.2 Operational Metrics
- Performance monitoring
- Error tracking
- Usage patterns
- Cost analysis

## 11. Módulo Admin

### 1.1 Gestão de Usuários
- CRUD completo
- Bloqueio/desbloqueio
- Histórico de ações
- Impersonation (com audit)

### 1.2 Configurações
- Feature flags
- Limites e taxas
- Integrações
- Maintenance mode

## 12. Módulo de Marketplace (Fase 3)

### 1.1 Anúncios
- Criação com verificação
- Sistema de ofertas
- Histórico de preços
- Alertas de oportunidades

### 1.2 Verificação
- Inventário em tempo real
- Float/pattern verification
- Price fairness check
- Scam detection