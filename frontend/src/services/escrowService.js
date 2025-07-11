// Escrow Service for Secure Transactions
import axios from 'axios'

class EscrowService {
  constructor() {
    this.backendURL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'
  }

  // Create Escrow Transaction
  async createEscrow(escrowData) {
    try {
      const response = await axios.post(`${this.backendURL}/escrow/create`, {
        buyer_id: escrowData.buyerId,
        seller_id: escrowData.sellerId,
        item_id: escrowData.itemId,
        amount: escrowData.amount,
        terms: escrowData.terms,
        timeout_hours: escrowData.timeoutHours || 168 // 7 days default
      })

      return {
        success: true,
        data: {
          escrowId: response.data.escrow_id,
          status: response.data.status,
          expiresAt: response.data.expires_at,
          terms: response.data.terms
        }
      }
    } catch (error) {
      console.error('Escrow creation failed:', error)
      return {
        success: false,
        error: error.response?.data?.message || 'Erro ao criar escrow'
      }
    }
  }

  // Deposit Funds to Escrow
  async depositFunds(escrowId, paymentData) {
    try {
      const response = await axios.post(`${this.backendURL}/escrow/${escrowId}/deposit`, {
        payment_method: paymentData.method,
        payment_data: paymentData.data
      })

      return {
        success: true,
        data: {
          depositId: response.data.deposit_id,
          status: response.data.status,
          amount: response.data.amount
        }
      }
    } catch (error) {
      console.error('Escrow deposit failed:', error)
      return {
        success: false,
        error: error.response?.data?.message || 'Erro ao depositar no escrow'
      }
    }
  }

  // Transfer Item to Escrow
  async transferItem(escrowId, itemData) {
    try {
      const response = await axios.post(`${this.backendURL}/escrow/${escrowId}/transfer-item`, {
        steam_trade_offer_id: itemData.tradeOfferId,
        item_asset_id: itemData.assetId,
        verification_code: itemData.verificationCode
      })

      return {
        success: true,
        data: {
          transferId: response.data.transfer_id,
          status: response.data.status,
          verifiedAt: response.data.verified_at
        }
      }
    } catch (error) {
      console.error('Item transfer failed:', error)
      return {
        success: false,
        error: error.response?.data?.message || 'Erro ao transferir item'
      }
    }
  }

  // Release Escrow (Complete Transaction)
  async releaseEscrow(escrowId, releaseData) {
    try {
      const response = await axios.post(`${this.backendURL}/escrow/${escrowId}/release`, {
        buyer_confirmation: releaseData.buyerConfirmation,
        seller_confirmation: releaseData.sellerConfirmation,
        admin_override: releaseData.adminOverride || false
      })

      return {
        success: true,
        data: {
          releaseId: response.data.release_id,
          status: response.data.status,
          completedAt: response.data.completed_at,
          funds_released: response.data.funds_released,
          item_transferred: response.data.item_transferred
        }
      }
    } catch (error) {
      console.error('Escrow release failed:', error)
      return {
        success: false,
        error: error.response?.data?.message || 'Erro ao liberar escrow'
      }
    }
  }

  // Cancel Escrow Transaction
  async cancelEscrow(escrowId, reason) {
    try {
      const response = await axios.post(`${this.backendURL}/escrow/${escrowId}/cancel`, {
        reason: reason,
        refund_funds: true,
        return_item: true
      })

      return {
        success: true,
        data: {
          cancellationId: response.data.cancellation_id,
          status: response.data.status,
          refund_status: response.data.refund_status,
          item_return_status: response.data.item_return_status
        }
      }
    } catch (error) {
      console.error('Escrow cancellation failed:', error)
      return {
        success: false,
        error: error.response?.data?.message || 'Erro ao cancelar escrow'
      }
    }
  }

  // Get Escrow Status
  async getEscrowStatus(escrowId) {
    try {
      const response = await axios.get(`${this.backendURL}/escrow/${escrowId}/status`)
      
      return {
        success: true,
        data: {
          status: response.data.status,
          stage: response.data.stage,
          buyer_confirmed: response.data.buyer_confirmed,
          seller_confirmed: response.data.seller_confirmed,
          funds_deposited: response.data.funds_deposited,
          item_received: response.data.item_received,
          expires_at: response.data.expires_at,
          created_at: response.data.created_at,
          timeline: response.data.timeline
        }
      }
    } catch (error) {
      console.error('Escrow status check failed:', error)
      return {
        success: false,
        error: 'Erro ao verificar status do escrow'
      }
    }
  }

  // Dispute Escrow Transaction
  async createDispute(escrowId, disputeData) {
    try {
      const response = await axios.post(`${this.backendURL}/escrow/${escrowId}/dispute`, {
        reason: disputeData.reason,
        description: disputeData.description,
        evidence: disputeData.evidence,
        disputer_id: disputeData.disputerId
      })

      return {
        success: true,
        data: {
          disputeId: response.data.dispute_id,
          status: response.data.status,
          created_at: response.data.created_at
        }
      }
    } catch (error) {
      console.error('Dispute creation failed:', error)
      return {
        success: false,
        error: error.response?.data?.message || 'Erro ao criar disputa'
      }
    }
  }

  // Get User Escrows
  async getUserEscrows(userId, status = null) {
    try {
      const params = status ? { status } : {}
      const response = await axios.get(`${this.backendURL}/users/${userId}/escrows`, { params })
      
      return {
        success: true,
        data: response.data.escrows
      }
    } catch (error) {
      console.error('Failed to get user escrows:', error)
      return {
        success: false,
        error: 'Erro ao buscar escrows do usuário'
      }
    }
  }

  // Extend Escrow Timeout
  async extendTimeout(escrowId, additionalHours) {
    try {
      const response = await axios.post(`${this.backendURL}/escrow/${escrowId}/extend`, {
        additional_hours: additionalHours
      })

      return {
        success: true,
        data: {
          new_expires_at: response.data.new_expires_at,
          extended_by: response.data.extended_by
        }
      }
    } catch (error) {
      console.error('Escrow extension failed:', error)
      return {
        success: false,
        error: error.response?.data?.message || 'Erro ao estender prazo do escrow'
      }
    }
  }

  // Get Escrow Statistics
  async getEscrowStats() {
    try {
      const response = await axios.get(`${this.backendURL}/escrow/statistics`)
      
      return {
        success: true,
        data: {
          total_escrows: response.data.total_escrows,
          active_escrows: response.data.active_escrows,
          completed_escrows: response.data.completed_escrows,
          disputed_escrows: response.data.disputed_escrows,
          total_volume: response.data.total_volume,
          success_rate: response.data.success_rate
        }
      }
    } catch (error) {
      console.error('Failed to get escrow statistics:', error)
      return {
        success: false,
        error: 'Erro ao buscar estatísticas do escrow'
      }
    }
  }

  // Verify Item Authenticity
  async verifyItemAuthenticity(itemData) {
    try {
      const response = await axios.post(`${this.backendURL}/escrow/verify-item`, {
        item_hash: itemData.hash,
        steam_id: itemData.steamId,
        asset_id: itemData.assetId,
        inspect_link: itemData.inspectLink
      })

      return {
        success: true,
        data: {
          is_authentic: response.data.is_authentic,
          verification_score: response.data.verification_score,
          warnings: response.data.warnings,
          verified_at: response.data.verified_at
        }
      }
    } catch (error) {
      console.error('Item verification failed:', error)
      return {
        success: false,
        error: error.response?.data?.message || 'Erro ao verificar autenticidade do item'
      }
    }
  }

  // Get Escrow Timeline
  async getEscrowTimeline(escrowId) {
    try {
      const response = await axios.get(`${this.backendURL}/escrow/${escrowId}/timeline`)
      
      return {
        success: true,
        data: response.data.timeline
      }
    } catch (error) {
      console.error('Failed to get escrow timeline:', error)
      return {
        success: false,
        error: 'Erro ao buscar histórico do escrow'
      }
    }
  }

  // Format Escrow Status
  formatStatus(status) {
    const statusMap = {
      'created': 'Criado',
      'funded': 'Financiado',
      'item_deposited': 'Item Depositado',
      'completed': 'Completado',
      'cancelled': 'Cancelado',
      'disputed': 'Em Disputa',
      'expired': 'Expirado'
    }

    return statusMap[status] || status
  }

  // Calculate Escrow Fees
  calculateEscrowFees(amount) {
    const baseFee = amount * 0.01 // 1% base fee
    const securityFee = Math.min(amount * 0.005, 50) // 0.5% security fee, max R$ 50
    const total = baseFee + securityFee

    return {
      base_fee: baseFee,
      security_fee: securityFee,
      total_fee: total,
      net_amount: amount - total
    }
  }
}

export default new EscrowService()
