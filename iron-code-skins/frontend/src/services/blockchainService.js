import axios from 'axios'

// Cliente específico para operações blockchain
const blockchainClient = axios.create({
  baseURL: '/api/blockchain',
  timeout: 60000, // 1 minuto para operações blockchain
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  }
})

// Interceptadores
blockchainClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => Promise.reject(error)
)

blockchainClient.interceptors.response.use(
  (response) => response,
  (error) => {
    // Tratar erros específicos do blockchain
    if (error.response?.status === 503) {
      error.message = 'Serviço blockchain temporariamente indisponível'
    } else if (error.response?.status === 429) {
      error.message = 'Muitas solicitações. Tente novamente em alguns segundos'
    } else {
      error.message = error.response?.data?.message || 
                     error.message || 
                     'Erro na operação blockchain'
    }
    return Promise.reject(error)
  }
)

export default {
  /**
   * Verificar status da rede blockchain
   */
  async getNetworkStatus() {
    return await blockchainClient.get('/status')
  },

  /**
   * Obter informações do contrato inteligente
   */
  async getContractInfo() {
    return await blockchainClient.get('/contract')
  },

  /**
   * Registrar hash no blockchain
   */
  async registerHash(evidenceId, hash, metadata = {}) {
    return await blockchainClient.post('/register', {
      evidence_id: evidenceId,
      hash,
      metadata
    })
  },

  /**
   * Verificar hash no blockchain
   */
  async verifyHash(hash) {
    return await blockchainClient.post('/verify', { hash })
  },

  /**
   * Buscar transação por hash
   */
  async getTransaction(txHash) {
    return await blockchainClient.get(`/transaction/${txHash}`)
  },

  /**
   * Buscar transações por endereço
   */
  async getTransactionsByAddress(address, filters = {}) {
    return await blockchainClient.get(`/address/${address}/transactions`, {
      params: filters
    })
  },

  /**
   * Buscar transações por contrato
   */
  async getContractTransactions(filters = {}) {
    return await blockchainClient.get('/contract/transactions', {
      params: filters
    })
  },

  /**
   * Obter estimativa de gas
   */
  async estimateGas(operation, data) {
    return await blockchainClient.post('/gas/estimate', {
      operation,
      data
    })
  },

  /**
   * Obter preço atual do gas
   */
  async getGasPrice() {
    return await blockchainClient.get('/gas/price')
  },

  /**
   * Buscar eventos do contrato
   */
  async getContractEvents(filters = {}) {
    return await blockchainClient.get('/contract/events', {
      params: filters
    })
  },

  /**
   * Verificar confirmações de transação
   */
  async getTransactionConfirmations(txHash) {
    return await blockchainClient.get(`/transaction/${txHash}/confirmations`)
  },

  /**
   * Monitorar transação (polling)
   */
  async monitorTransaction(txHash, onUpdate, maxAttempts = 30) {
    let attempts = 0
    
    const poll = async () => {
      try {
        attempts++
        const response = await this.getTransaction(txHash)
        const transaction = response.data
        
        onUpdate(transaction)
        
        // Se confirmada ou erro, parar polling
        if (transaction.status === 'confirmed' || 
            transaction.status === 'failed' || 
            attempts >= maxAttempts) {
          return transaction
        }
        
        // Continuar polling a cada 5 segundos
        setTimeout(poll, 5000)
      } catch (error) {
        if (attempts < maxAttempts) {
          setTimeout(poll, 5000)
        } else {
          throw error
        }
      }
    }
    
    return poll()
  },

  /**
   * Obter prova de existência
   */
  async getProofOfExistence(hash) {
    return await blockchainClient.get(`/proof/${hash}`)
  },

  /**
   * Gerar prova criptográfica
   */
  async generateCryptographicProof(evidenceId) {
    return await blockchainClient.post(`/proof/generate`, {
      evidence_id: evidenceId
    })
  },

  /**
   * Verificar prova criptográfica
   */
  async verifyCryptographicProof(proof) {
    return await blockchainClient.post('/proof/verify', proof)
  },

  /**
   * Obter merkle tree para evidências
   */
  async getMerkleTree(evidenceIds) {
    return await blockchainClient.post('/merkle/generate', {
      evidence_ids: evidenceIds
    })
  },

  /**
   * Verificar merkle proof
   */
  async verifyMerkleProof(proof, root, leaf) {
    return await blockchainClient.post('/merkle/verify', {
      proof,
      root,
      leaf
    })
  },

  /**
   * Obter estatísticas da rede
   */
  async getNetworkStats() {
    return await blockchainClient.get('/stats')
  },

  /**
   * Obter histórico de preços de gas
   */
  async getGasHistory(period = '24h') {
    return await blockchainClient.get('/gas/history', {
      params: { period }
    })
  },

  /**
   * Configurar webhook para eventos blockchain
   */
  async setupWebhook(url, events = ['hash_registered', 'transaction_confirmed']) {
    return await blockchainClient.post('/webhook', {
      url,
      events
    })
  },

  /**
   * Remover webhook
   */
  async removeWebhook(webhookId) {
    return await blockchainClient.delete(`/webhook/${webhookId}`)
  },

  /**
   * Testar conexão com blockchain
   */
  async testConnection() {
    return await blockchainClient.get('/test')
  },

  /**
   * Obter endereço da carteira do sistema
   */
  async getSystemWallet() {
    return await blockchainClient.get('/wallet')
  },

  /**
   * Obter saldo da carteira
   */
  async getWalletBalance(address) {
    return await blockchainClient.get(`/wallet/${address}/balance`)
  },

  /**
   * Utility: Converter wei para ether
   */
  weiToEther(wei) {
    return wei / Math.pow(10, 18)
  },

  /**
   * Utility: Converter ether para wei
   */
  etherToWei(ether) {
    return ether * Math.pow(10, 18)
  },

  /**
   * Utility: Formatar endereço blockchain
   */
  formatAddress(address, length = 8) {
    if (!address) return ''
    return `${address.slice(0, length)}...${address.slice(-length)}`
  },

  /**
   * Utility: Validar endereço blockchain
   */
  isValidAddress(address) {
    return /^0x[a-fA-F0-9]{40}$/.test(address)
  },

  /**
   * Utility: Validar hash de transação
   */
  isValidTxHash(hash) {
    return /^0x[a-fA-F0-9]{64}$/.test(hash)
  }
}
