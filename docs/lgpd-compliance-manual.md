# Manual de Compliance LGPD - Iron Code Skins

## 1. Visão Geral

Este manual descreve os procedimentos para garantir conformidade com a Lei Geral de Proteção de Dados (LGPD) na plataforma Iron Code Skins.

## 2. Estrutura de Governança

### 2.1 Encarregado de Dados (DPO)
- **Responsável:** [Nome do DPO]
- **Email:** dpo@ironcodeskins.com
- **Funções:**
  - Supervisionar tratamento de dados
  - Orientar funcionários
  - Atuar como ponto de contato com ANPD

### 2.2 Comitê de Privacidade
- Revisar políticas trimestralmente
- Avaliar novos tratamentos de dados
- Investigar incidentes de segurança

## 3. Mapeamento de Dados

### 3.1 Categorias de Dados
```
users: name, email, password, steam_id, avatar
contracts: buyer_id, seller_id, item_details, value
payments: user_id, amount, gateway_data
reviews: reviewer_id, reviewee_id, rating, comment
messages: sender_id, contract_id, content
blockchain_records: type, reference_id, tx_hash
```

### 3.2 Finalidades de Tratamento
- **Execução de contratos:** Base legal contratual
- **Prevenção de fraudes:** Interesse legítimo
- **Marketing:** Consentimento
- **Obrigações legais:** Base legal legal

## 4. Direitos dos Titulares

### 4.1 Processo de Atendimento

#### Acesso (Art. 18, I)
```bash
GET /api/v1/privacy/export-data
```

#### Retificação (Art. 18, III)
```bash
PUT /api/v1/users/{id}
```

#### Apagamento (Art. 18, VI)
```bash
POST /api/v1/privacy/request-deletion
```

#### Portabilidade (Art. 18, V)
```bash
GET /api/v1/privacy/export-data
```

### 4.2 Prazos de Atendimento
- **Solicitações simples:** 15 dias
- **Solicitações complexas:** 30 dias (prorrogáveis)
- **Comunicação de incidentes:** 72 horas

## 5. Medidas de Segurança

### 5.1 Técnicas
- **Criptografia:** AES-256 em repouso, TLS 1.3 em trânsito
- **Controle de Acesso:** RBAC (Role-Based Access Control)
- **Tokenização:** Dados de pagamento sempre tokenizados
- **Logs de Auditoria:** Todos os acessos registrados

### 5.2 Organizacionais
- **Treinamento:** Anual para todos os funcionários
- **Políticas:** Revisão semestral
- **Contratos:** Cláusulas de proteção de dados
- **Monitoramento:** 24/7 para incidentes

## 6. Processo de Incidentes

### 6.1 Detecção
- Monitoramento automatizado
- Relatórios de usuários
- Auditoria interna

### 6.2 Resposta
1. **Contenção (0-1h):** Isolar o problema
2. **Avaliação (1-8h):** Determinar impacto
3. **Notificação (8-72h):** ANPD e titulares se necessário
4. **Remediação (72h+):** Corrigir vulnerabilidades

### 6.3 Documentação
- Registro de todos os incidentes
- Análise de causa raiz
- Plano de melhoria

## 7. Cookies e Tracking

### 7.1 Categorias
- **Essenciais:** Funcionamento da plataforma
- **Performance:** Métricas de uso (anonimizadas)
- **Marketing:** Apenas com consentimento

### 7.2 Gestão de Consentimento
```javascript
// Exemplo de banner de cookies
{
  "essential": true,      // Sempre ativo
  "analytics": false,     // Requer opt-in
  "marketing": false      // Requer opt-in
}
```

## 8. Transferências Internacionais

### 8.1 Política Atual
- **Dados processados:** Apenas no Brasil
- **Provedores cloud:** Data centers nacionais
- **Exceções:** Nenhuma atualmente

### 8.2 Caso Necessário
- Verificar adequação do país
- Implementar cláusulas contratuais padrão
- Obter autorização da ANPD se necessário

## 9. Retenção de Dados

### 9.1 Períodos
```
user_data: 5 anos após último acesso
transaction_data: 10 anos (obrigação fiscal)
communication_data: 2 anos
log_data: 1 ano
marketing_data: até revogação do consentimento
```

### 9.2 Processo de Eliminação
- **Automático:** Scripts agendados
- **Manual:** Para casos especiais
- **Documentado:** Registro de eliminações

## 10. Treinamento e Conscientização

### 10.1 Programa Anual
- **Módulo 1:** Conceitos básicos LGPD
- **Módulo 2:** Procedimentos da empresa
- **Módulo 3:** Casos práticos
- **Avaliação:** Teste obrigatório

### 10.2 Materiais
- Guia rápido de bolso
- Fluxogramas de decisão
- FAQ atualizado

## 11. Auditoria e Monitoramento

### 11.1 Auditoria Interna
- **Frequência:** Semestral
- **Escopo:** Todos os processos de dados
- **Relatório:** Para alta administração

### 11.2 Métricas
- Tempo de resposta a solicitações
- Número de incidentes por mês
- Taxa de conclusão de treinamentos
- Conformidade com políticas

## 12. Relacionamento com ANPD

### 12.1 Comunicações
- **Incidentes graves:** 72 horas
- **Relatórios:** Conforme solicitação
- **Cooperação:** Total transparência

### 12.2 Documentação
- Registro de atividades de tratamento
- Relatórios de impacto quando necessário
- Evidências de conformidade

---

**Aprovado por:** [DPO]  
**Data:** 11 de julho de 2025  
**Próxima revisão:** 11 de janeiro de 2026
