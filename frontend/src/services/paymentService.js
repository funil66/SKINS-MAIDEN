// Payment Services Integration
import axios from 'axios'

class PaymentService {
  constructor() {
    this.backendURL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'
    this.pixKey = import.meta.env.VITE_PIX_KEY
  }

  // PIX Payment Processing
  async createPixPayment(paymentData) {
    try {
      const response = await axios.post(`${this.backendURL}/payments/pix/create`, {
        amount: paymentData.amount,
        description: paymentData.description,
        user_id: paymentData.userId,
        order_id: paymentData.orderId,
        callback_url: `${window.location.origin}/payment/callback`
      })

      return {
        success: true,
        data: {
          pixCode: response.data.pix_code,
          qrCode: response.data.qr_code,
          expiresAt: response.data.expires_at,
          transactionId: response.data.transaction_id
        }
      }
    } catch (error) {
      console.error('PIX payment creation failed:', error)
      return {
        success: false,
        error: error.response?.data?.message || 'Erro ao criar pagamento PIX'
      }
    }
  }

  // Credit Card Payment Processing
  async createCreditCardPayment(paymentData) {
    try {
      const response = await axios.post(`${this.backendURL}/payments/card/create`, {
        amount: paymentData.amount,
        card_number: paymentData.cardNumber,
        card_holder: paymentData.cardHolder,
        expiry_month: paymentData.expiryMonth,
        expiry_year: paymentData.expiryYear,
        cvv: paymentData.cvv,
        installments: paymentData.installments || 1,
        user_id: paymentData.userId,
        order_id: paymentData.orderId
      })

      return {
        success: true,
        data: {
          transactionId: response.data.transaction_id,
          status: response.data.status,
          authorizationCode: response.data.authorization_code
        }
      }
    } catch (error) {
      console.error('Credit card payment failed:', error)
      return {
        success: false,
        error: error.response?.data?.message || 'Erro ao processar cartão de crédito'
      }
    }
  }

  // Steam Wallet Payment
  async createSteamWalletPayment(paymentData) {
    try {
      const response = await axios.post(`${this.backendURL}/payments/steam/create`, {
        amount: paymentData.amount,
        steam_id: paymentData.steamId,
        user_id: paymentData.userId,
        order_id: paymentData.orderId,
        items: paymentData.items
      })

      return {
        success: true,
        data: {
          tradeOfferId: response.data.trade_offer_id,
          tradeUrl: response.data.trade_url,
          transactionId: response.data.transaction_id
        }
      }
    } catch (error) {
      console.error('Steam wallet payment failed:', error)
      return {
        success: false,
        error: error.response?.data?.message || 'Erro ao processar pagamento Steam'
      }
    }
  }

  // Check Payment Status
  async checkPaymentStatus(transactionId) {
    try {
      const response = await axios.get(`${this.backendURL}/payments/status/${transactionId}`)
      
      return {
        success: true,
        data: {
          status: response.data.status,
          amount: response.data.amount,
          paidAt: response.data.paid_at,
          method: response.data.payment_method
        }
      }
    } catch (error) {
      console.error('Payment status check failed:', error)
      return {
        success: false,
        error: 'Erro ao verificar status do pagamento'
      }
    }
  }

  // Refund Payment
  async refundPayment(transactionId, reason) {
    try {
      const response = await axios.post(`${this.backendURL}/payments/refund`, {
        transaction_id: transactionId,
        reason: reason
      })

      return {
        success: true,
        data: {
          refundId: response.data.refund_id,
          amount: response.data.amount,
          status: response.data.status
        }
      }
    } catch (error) {
      console.error('Payment refund failed:', error)
      return {
        success: false,
        error: error.response?.data?.message || 'Erro ao processar reembolso'
      }
    }
  }

  // Generate PIX QR Code
  generatePixQRCode(pixCode) {
    // Use a QR code library to generate QR from PIX code
    import('qrcode').then(QRCode => {
      return QRCode.toDataURL(pixCode, {
        width: 256,
        margin: 2,
        color: {
          dark: '#000000',
          light: '#FFFFFF'
        }
      })
    })
  }

  // Validate Credit Card
  validateCreditCard(cardNumber) {
    // Luhn algorithm for credit card validation
    const num = cardNumber.replace(/\s/g, '')
    let sum = 0
    let isEven = false

    for (let i = num.length - 1; i >= 0; i--) {
      let digit = parseInt(num[i])

      if (isEven) {
        digit *= 2
        if (digit > 9) {
          digit -= 9
        }
      }

      sum += digit
      isEven = !isEven
    }

    return sum % 10 === 0
  }

  // Get Card Brand
  getCardBrand(cardNumber) {
    const num = cardNumber.replace(/\s/g, '')
    
    if (/^4/.test(num)) return 'visa'
    if (/^5[1-5]/.test(num) || /^2[2-7]/.test(num)) return 'mastercard'
    if (/^3[47]/.test(num)) return 'amex'
    if (/^6(?:011|5)/.test(num)) return 'discover'
    if (/^(?:2131|1800|35)/.test(num)) return 'jcb'
    
    return 'unknown'
  }

  // Format Currency
  formatCurrency(amount, currency = 'BRL') {
    return new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: currency
    }).format(amount)
  }

  // Calculate Fees
  calculateFees(amount, method) {
    const feeRates = {
      pix: 0.02, // 2%
      credit_card: 0.035, // 3.5%
      steam_wallet: 0.05 // 5%
    }

    const feeRate = feeRates[method] || 0.03
    const fee = amount * feeRate
    const total = amount + fee

    return {
      amount: amount,
      fee: fee,
      total: total,
      feeRate: feeRate * 100
    }
  }

  // Webhook Handler for Payment Updates
  async handleWebhook(webhookData) {
    try {
      const response = await axios.post(`${this.backendURL}/payments/webhook`, webhookData)
      return response.data
    } catch (error) {
      console.error('Webhook handling failed:', error)
      throw error
    }
  }

  // Get Payment Methods
  async getPaymentMethods(userId) {
    try {
      const response = await axios.get(`${this.backendURL}/users/${userId}/payment-methods`)
      return response.data
    } catch (error) {
      console.error('Failed to get payment methods:', error)
      return []
    }
  }

  // Save Payment Method
  async savePaymentMethod(userId, methodData) {
    try {
      const response = await axios.post(`${this.backendURL}/users/${userId}/payment-methods`, {
        type: methodData.type,
        card_last_four: methodData.cardLastFour,
        card_brand: methodData.cardBrand,
        card_holder: methodData.cardHolder,
        is_default: methodData.isDefault
      })
      return response.data
    } catch (error) {
      console.error('Failed to save payment method:', error)
      throw error
    }
  }

  // Delete Payment Method
  async deletePaymentMethod(methodId) {
    try {
      await axios.delete(`${this.backendURL}/payment-methods/${methodId}`)
      return true
    } catch (error) {
      console.error('Failed to delete payment method:', error)
      return false
    }
  }
}

export default new PaymentService()
