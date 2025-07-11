import axios from 'axios'

// Configuração base do axios
const apiClient = axios.create({
  baseURL: '/api',
  timeout: 30000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  }
})

// Interceptador de request para adicionar token de autenticação
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Interceptador de response para tratar erros globais
apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Token expirado ou inválido
      localStorage.removeItem('auth_token')
      window.location.href = '/login'
    }
    
    // Melhorar mensagens de erro
    const message = error.response?.data?.message || 
                   error.response?.data?.error || 
                   error.message || 
                   'Erro de conexão'
    
    error.message = message
    return Promise.reject(error)
  }
)

export default {
  /**
   * Buscar evidências por transação
   */
  async getEvidences(transactionId = null, filters = {}) {
    const params = { ...filters }
    if (transactionId) {
      params.transaction_id = transactionId
    }
    
    return await apiClient.get('/evidences', { params })
  },

  /**
   * Buscar evidência específica
   */
  async getEvidence(evidenceId) {
    return await apiClient.get(`/evidences/${evidenceId}`)
  },

  /**
   * Upload de evidência
   */
  async uploadEvidence(formData, options = {}) {
    return await apiClient.post('/evidences', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
      ...options
    })
  },

  /**
   * Verificar hash da evidência no blockchain
   */
  async verifyHash(evidenceId) {
    return await apiClient.post(`/evidences/${evidenceId}/verify`)
  },

  /**
   * Buscar logs de auditoria
   */
  async getAuditLogs(evidenceId = null, filters = {}) {
    const params = { ...filters }
    if (evidenceId) {
      params.evidence_id = evidenceId
    }
    
    return await apiClient.get('/audit-logs', { params })
  },

  /**
   * Criar log de auditoria
   */
  async createAuditLog(data) {
    return await apiClient.post('/audit-logs', data)
  },

  /**
   * Buscar registros blockchain
   */
  async getBlockchainRecords(evidenceId = null, filters = {}) {
    const params = { ...filters }
    if (evidenceId) {
      params.evidence_id = evidenceId
    }
    
    return await apiClient.get('/blockchain-records', { params })
  },

  /**
   * Verificar status do blockchain
   */
  async getBlockchainStatus() {
    return await apiClient.get('/blockchain/status')
  },

  /**
   * Buscar transação no blockchain
   */
  async getBlockchainTransaction(txHash) {
    return await apiClient.get(`/blockchain/transaction/${txHash}`)
  },

  /**
   * Gerar relatório de auditoria
   */
  async generateAuditReport(filters = {}) {
    return await apiClient.post('/audit/report', filters, {
      responseType: 'blob' // Para download de arquivo
    })
  },

  /**
   * Verificar integridade completa
   */
  async verifyCompleteIntegrity(evidenceId) {
    return await apiClient.post(`/evidences/${evidenceId}/verify-complete`)
  },

  /**
   * Buscar estatísticas de auditoria
   */
  async getAuditStats(period = '30d') {
    return await apiClient.get('/audit/stats', {
      params: { period }
    })
  },

  /**
   * Webhook para notificações blockchain
   */
  async registerWebhook(data) {
    return await apiClient.post('/webhooks/blockchain', data)
  },

  /**
   * Buscar timeline de evidências
   */
  async getEvidenceTimeline(transactionId, filters = {}) {
    return await apiClient.get(`/transactions/${transactionId}/timeline`, {
      params: filters
    })
  }
}
