<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Progress Steps -->
      <div class="mb-8">
        <nav aria-label="Progress">
          <ol class="flex items-center justify-center">
            <li class="relative">
              <div class="flex items-center">
                <div class="flex items-center justify-center w-8 h-8 bg-purple-600 rounded-full">
                  <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                  </svg>
                </div>
                <span class="ml-2 text-sm font-medium text-gray-900 dark:text-white">Carrinho</span>
              </div>
            </li>
            <li class="relative ml-8">
              <div class="flex items-center">
                <div class="flex items-center justify-center w-8 h-8 bg-purple-600 rounded-full">
                  <span class="text-white text-sm font-medium">2</span>
                </div>
                <span class="ml-2 text-sm font-medium text-purple-600 dark:text-purple-400">Checkout</span>
              </div>
            </li>
            <li class="relative ml-8">
              <div class="flex items-center">
                <div class="flex items-center justify-center w-8 h-8 bg-gray-300 dark:bg-gray-600 rounded-full">
                  <span class="text-gray-500 dark:text-gray-400 text-sm font-medium">3</span>
                </div>
                <span class="ml-2 text-sm font-medium text-gray-500 dark:text-gray-400">Pagamento</span>
              </div>
            </li>
            <li class="relative ml-8">
              <div class="flex items-center">
                <div class="flex items-center justify-center w-8 h-8 bg-gray-300 dark:bg-gray-600 rounded-full">
                  <span class="text-gray-500 dark:text-gray-400 text-sm font-medium">4</span>
                </div>
                <span class="ml-2 text-sm font-medium text-gray-500 dark:text-gray-400">Confirmação</span>
              </div>
            </li>
          </ol>
        </nav>
      </div>

      <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Finalizar Compra</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">
            Revise seus dados e confirme a compra
          </p>
        </div>

        <div class="p-6">
          <!-- Order Summary -->
          <div class="mb-8">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
              Resumo do Pedido
            </h2>
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
              <div class="space-y-3">
                <div
                  v-for="item in orderItems"
                  :key="item.id"
                  class="flex items-center justify-between"
                >
                  <div class="flex items-center space-x-3">
                    <img
                      :src="item.image"
                      :alt="item.name"
                      class="w-12 h-12 object-cover rounded"
                    >
                    <div>
                      <div class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ item.name }}
                      </div>
                      <div class="text-xs text-gray-600 dark:text-gray-400">
                        {{ item.condition }}
                      </div>
                    </div>
                  </div>
                  <div class="text-sm font-medium text-gray-900 dark:text-white">
                    R$ {{ formatPrice(item.price) }}
                  </div>
                </div>
                
                <div class="border-t border-gray-200 dark:border-gray-600 pt-3">
                  <div class="flex justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Subtotal</span>
                    <span class="text-gray-900 dark:text-white">R$ {{ formatPrice(subtotal) }}</span>
                  </div>
                  <div class="flex justify-between text-sm mt-1">
                    <span class="text-gray-600 dark:text-gray-400">Taxa de serviço</span>
                    <span class="text-gray-900 dark:text-white">R$ {{ formatPrice(serviceFee) }}</span>
                  </div>
                  <div class="flex justify-between text-base font-medium mt-2 pt-2 border-t border-gray-200 dark:border-gray-600">
                    <span class="text-gray-900 dark:text-white">Total</span>
                    <span class="text-gray-900 dark:text-white">R$ {{ formatPrice(total) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Steam Account Verification -->
          <div class="mb-8">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
              Conta Steam
            </h2>
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
              <div class="flex items-center space-x-4">
                <img
                  :src="steamAccount.avatar"
                  :alt="steamAccount.name"
                  class="w-12 h-12 rounded-full"
                >
                <div>
                  <div class="font-medium text-gray-900 dark:text-white">{{ steamAccount.name }}</div>
                  <div class="text-sm text-gray-600 dark:text-gray-400">Steam ID: {{ steamAccount.steamId }}</div>
                </div>
                <div class="ml-auto">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                    Verificado
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Payment Method -->
          <div class="mb-8">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
              Método de Pagamento
            </h2>
            
            <div class="space-y-3">
              <!-- Credit Card -->
              <label class="relative flex items-center p-4 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                <input
                  v-model="paymentMethod"
                  type="radio"
                  value="credit_card"
                  class="h-4 w-4 text-purple-600 border-gray-300 focus:ring-purple-500"
                >
                <div class="ml-3 flex items-center space-x-3">
                  <div class="flex space-x-2">
                    <svg class="w-8 h-6" viewBox="0 0 38 24" fill="none">
                      <rect width="38" height="24" rx="4" fill="#1A1F71"/>
                      <path d="M15 12L17 10L19 12L17 14L15 12Z" fill="white"/>
                    </svg>
                    <svg class="w-8 h-6" viewBox="0 0 38 24" fill="none">
                      <rect width="38" height="24" rx="4" fill="#EB001B"/>
                      <circle cx="19" cy="12" r="7" fill="#FF5F00"/>
                      <circle cx="13" cy="12" r="7" fill="#EB001B"/>
                      <circle cx="25" cy="12" r="7" fill="#F79E1B"/>
                    </svg>
                  </div>
                  <div>
                    <div class="font-medium text-gray-900 dark:text-white">Cartão de Crédito/Débito</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Visa, Mastercard, Elo</div>
                  </div>
                </div>
              </label>

              <!-- PIX -->
              <label class="relative flex items-center p-4 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                <input
                  v-model="paymentMethod"
                  type="radio"
                  value="pix"
                  class="h-4 w-4 text-purple-600 border-gray-300 focus:ring-purple-500"
                >
                <div class="ml-3 flex items-center space-x-3">
                  <div class="w-8 h-6 bg-teal-500 rounded flex items-center justify-center">
                    <span class="text-white text-xs font-bold">PIX</span>
                  </div>
                  <div>
                    <div class="font-medium text-gray-900 dark:text-white">PIX</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Pagamento instantâneo</div>
                  </div>
                </div>
              </label>

              <!-- Steam Wallet -->
              <label class="relative flex items-center p-4 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                <input
                  v-model="paymentMethod"
                  type="radio"
                  value="steam_wallet"
                  class="h-4 w-4 text-purple-600 border-gray-300 focus:ring-purple-500"
                >
                <div class="ml-3 flex items-center space-x-3">
                  <div class="w-8 h-6 bg-blue-600 rounded flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/>
                    </svg>
                  </div>
                  <div>
                    <div class="font-medium text-gray-900 dark:text-white">Steam Wallet</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Saldo: R$ 2.350,00</div>
                  </div>
                </div>
              </label>
            </div>
          </div>

          <!-- Terms and Conditions -->
          <div class="mb-8">
            <label class="flex items-start">
              <input
                v-model="agreeToTerms"
                type="checkbox"
                class="h-4 w-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500 mt-1"
              >
              <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                Eu concordo com os 
                <a href="/terms" class="text-purple-600 dark:text-purple-400 hover:underline">Termos de Serviço</a>
                e 
                <a href="/privacy" class="text-purple-600 dark:text-purple-400 hover:underline">Política de Privacidade</a>
              </span>
            </label>
          </div>

          <!-- Security Notice -->
          <div class="mb-8 p-4 bg-blue-50 dark:bg-blue-900 rounded-lg">
            <div class="flex">
              <svg class="flex-shrink-0 h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
              </svg>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                  Sistema de Escrow Ativo
                </h3>
                <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                  <p>
                    Seus itens ficarão seguros em nossa custódia até que o pagamento seja confirmado.
                    Você tem até 7 dias para cancelar se houver algum problema.
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex flex-col sm:flex-row gap-4">
            <button
              @click="$router.go(-1)"
              class="flex-1 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 py-3 px-6 rounded-lg font-medium hover:bg-gray-400 dark:hover:bg-gray-500 focus:outline-none transition duration-200"
            >
              Voltar
            </button>
            <button
              @click="processPayment"
              :disabled="!canProceed"
              class="flex-1 bg-purple-600 text-white py-3 px-6 rounded-lg font-medium hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="processing">Processando...</span>
              <span v-else>Confirmar Compra - R$ {{ formatPrice(total) }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

export default {
  name: 'Checkout',
  setup() {
    const router = useRouter()
    const processing = ref(false)
    const paymentMethod = ref('')
    const agreeToTerms = ref(false)

    // Mock data - replace with store/API
    const orderItems = ref([
      {
        id: 1,
        name: 'AK-47 | Redline',
        condition: 'Field-Tested',
        price: 150.00,
        image: '/images/ak47-redline.jpg'
      },
      {
        id: 2,
        name: 'AWP | Dragon Lore',
        condition: 'Factory New',
        price: 2500.00,
        image: '/images/awp-dragonlore.jpg'
      }
    ])

    const steamAccount = ref({
      name: 'PlayerPro123',
      steamId: '76561198000000000',
      avatar: '/images/default-avatar.jpg'
    })

    const subtotal = computed(() => {
      return orderItems.value.reduce((total, item) => total + item.price, 0)
    })

    const serviceFee = computed(() => {
      return subtotal.value * 0.05
    })

    const total = computed(() => {
      return subtotal.value + serviceFee.value
    })

    const canProceed = computed(() => {
      return paymentMethod.value && agreeToTerms.value && !processing.value
    })

    const formatPrice = (price) => {
      return new Intl.NumberFormat('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      }).format(price)
    }

    const processPayment = async () => {
      if (!canProceed.value) return

      try {
        processing.value = true

        // Mock payment processing
        await new Promise(resolve => setTimeout(resolve, 3000))

        // Redirect to confirmation page
        router.push('/purchase/confirmation')

      } catch (error) {
        console.error('Payment error:', error)
        alert('Erro ao processar pagamento. Tente novamente.')
      } finally {
        processing.value = false
      }
    }

    onMounted(() => {
      // Load checkout data
    })

    return {
      processing,
      paymentMethod,
      agreeToTerms,
      orderItems,
      steamAccount,
      subtotal,
      serviceFee,
      total,
      canProceed,
      formatPrice,
      processPayment
    }
  }
}
</script>
