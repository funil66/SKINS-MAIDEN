<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 mb-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Meu Carrinho</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">
          {{ cartItems.length }} item(s) no carrinho
        </p>
      </div>

      <!-- Empty Cart -->
      <div v-if="cartItems.length === 0" class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13v8a2 2 0 002 2h8a2 2 0 002-2v-8m-10 0V9a2 2 0 012-2h8a2 2 0 012 2v4.01"/>
        </svg>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Seu carrinho está vazio</h3>
        <p class="text-gray-600 dark:text-gray-400 mb-6">
          Adicione alguns itens incríveis do marketplace
        </p>
        <router-link
          to="/marketplace"
          class="inline-flex items-center px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition duration-200"
        >
          Ir ao Marketplace
        </router-link>
      </div>

      <!-- Cart Items -->
      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Items List -->
        <div class="lg:col-span-2 space-y-4">
          <div
            v-for="item in cartItems"
            :key="item.id"
            class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6"
          >
            <div class="flex items-center space-x-6">
              <!-- Item Image -->
              <div class="flex-shrink-0">
                <img
                  :src="item.image"
                  :alt="item.name"
                  class="w-24 h-24 object-cover rounded-lg"
                >
              </div>

              <!-- Item Details -->
              <div class="flex-1 min-w-0">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white truncate">
                  {{ item.name }}
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                  {{ item.condition }} | Float: {{ item.float }}
                </p>
                <div class="mt-2 flex items-center space-x-4">
                  <span
                    class="px-2 py-1 rounded text-xs font-medium"
                    :style="{ backgroundColor: getRarityColor(item.rarity), color: 'white' }"
                  >
                    {{ getRarityLabel(item.rarity) }}
                  </span>
                  <span class="text-sm text-gray-600 dark:text-gray-400">
                    Vendido por: {{ item.seller.name }}
                  </span>
                </div>
              </div>

              <!-- Price and Actions -->
              <div class="flex flex-col items-end space-y-2">
                <span class="text-xl font-bold text-purple-600 dark:text-purple-400">
                  R$ {{ formatPrice(item.price) }}
                </span>
                <button
                  @click="removeFromCart(item.id)"
                  class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 text-sm"
                >
                  Remover
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Order Summary -->
        <div class="lg:col-span-1">
          <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 sticky top-8">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
              Resumo do Pedido
            </h2>

            <div class="space-y-3">
              <!-- Subtotal -->
              <div class="flex justify-between text-sm">
                <span class="text-gray-600 dark:text-gray-400">Subtotal</span>
                <span class="text-gray-900 dark:text-white">R$ {{ formatPrice(subtotal) }}</span>
              </div>

              <!-- Service Fee -->
              <div class="flex justify-between text-sm">
                <span class="text-gray-600 dark:text-gray-400">Taxa de serviço (5%)</span>
                <span class="text-gray-900 dark:text-white">R$ {{ formatPrice(serviceFee) }}</span>
              </div>

              <!-- Total -->
              <div class="border-t border-gray-200 dark:border-gray-700 pt-3">
                <div class="flex justify-between text-base font-medium">
                  <span class="text-gray-900 dark:text-white">Total</span>
                  <span class="text-gray-900 dark:text-white">R$ {{ formatPrice(total) }}</span>
                </div>
              </div>
            </div>

            <!-- Payment Method Selection -->
            <div class="mt-6">
              <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">
                Método de Pagamento
              </h3>
              <div class="space-y-2">
                <label class="flex items-center">
                  <input
                    v-model="paymentMethod"
                    type="radio"
                    value="credit_card"
                    class="h-4 w-4 text-purple-600 border-gray-300 focus:ring-purple-500"
                  >
                  <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Cartão de Crédito</span>
                </label>
                <label class="flex items-center">
                  <input
                    v-model="paymentMethod"
                    type="radio"
                    value="pix"
                    class="h-4 w-4 text-purple-600 border-gray-300 focus:ring-purple-500"
                  >
                  <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">PIX</span>
                </label>
                <label class="flex items-center">
                  <input
                    v-model="paymentMethod"
                    type="radio"
                    value="steam_wallet"
                    class="h-4 w-4 text-purple-600 border-gray-300 focus:ring-purple-500"
                  >
                  <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Steam Wallet</span>
                </label>
              </div>
            </div>

            <!-- Checkout Button -->
            <button
              @click="proceedToCheckout"
              :disabled="processing || !paymentMethod"
              class="w-full mt-6 bg-purple-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="processing">Processando...</span>
              <span v-else>Finalizar Compra</span>
            </button>

            <!-- Security Notice -->
            <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
              <div class="flex items-start">
                <svg class="flex-shrink-0 h-5 w-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 1L5 6v3h10V6l-5-5zM8 6L10 4l2 2H8zm1 4v5h2v-5H9zm-3 0v5h2v-5H6zm8 0v5h2v-5h-2z" clip-rule="evenodd"/>
                </svg>
                <div class="ml-2">
                  <p class="text-xs text-gray-600 dark:text-gray-400">
                    🔒 Transação 100% segura com escrow
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recommended Items -->
      <div v-if="recommendedItems.length > 0" class="mt-12">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">
          Você também pode gostar
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div
            v-for="item in recommendedItems"
            :key="item.id"
            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition duration-200 cursor-pointer"
            @click="viewItem(item)"
          >
            <img
              :src="item.image"
              :alt="item.name"
              class="w-full h-32 object-cover"
            >
            <div class="p-4">
              <h3 class="text-sm font-medium text-gray-900 dark:text-white truncate">
                {{ item.name }}
              </h3>
              <p class="text-lg font-bold text-purple-600 dark:text-purple-400 mt-1">
                R$ {{ formatPrice(item.price) }}
              </p>
              <button
                @click.stop="addToCart(item)"
                class="w-full mt-2 bg-purple-600 text-white py-1 px-3 rounded text-sm hover:bg-purple-700 transition duration-200"
              >
                Adicionar
              </button>
            </div>
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
  name: 'Cart',
  setup() {
    const router = useRouter()
    const processing = ref(false)
    const paymentMethod = ref('')

    // Mock cart data - replace with store/API
    const cartItems = ref([
      {
        id: 1,
        name: 'AK-47 | Redline',
        condition: 'Field-Tested',
        float: 0.25,
        price: 150.00,
        image: '/images/ak47-redline.jpg',
        rarity: 'classified',
        seller: { name: 'SkinTrader123' }
      },
      {
        id: 2,
        name: 'AWP | Dragon Lore',
        condition: 'Factory New',
        float: 0.01,
        price: 2500.00,
        image: '/images/awp-dragonlore.jpg',
        rarity: 'covert',
        seller: { name: 'ProTrader456' }
      }
    ])

    const recommendedItems = ref([
      {
        id: 3,
        name: 'M4A4 | Howl',
        price: 1200.00,
        image: '/images/m4a4-howl.jpg'
      },
      {
        id: 4,
        name: 'Karambit | Fade',
        price: 800.00,
        image: '/images/karambit-fade.jpg'
      },
      {
        id: 5,
        name: 'Glock-18 | Fade',
        price: 180.00,
        image: '/images/glock-fade.jpg'
      },
      {
        id: 6,
        name: 'USP-S | Kill Confirmed',
        price: 95.00,
        image: '/images/usp-killconfirmed.jpg'
      }
    ])

    const rarities = {
      consumer: { label: 'Consumer Grade', color: '#b0c3d9' },
      industrial: { label: 'Industrial Grade', color: '#5e98d9' },
      milspec: { label: 'Mil-Spec', color: '#4b69ff' },
      restricted: { label: 'Restricted', color: '#8847ff' },
      classified: { label: 'Classified', color: '#d32ce6' },
      covert: { label: 'Covert', color: '#eb4b4b' },
      contraband: { label: 'Contraband', color: '#e4ae39' }
    }

    const subtotal = computed(() => {
      return cartItems.value.reduce((total, item) => total + item.price, 0)
    })

    const serviceFee = computed(() => {
      return subtotal.value * 0.05 // 5% service fee
    })

    const total = computed(() => {
      return subtotal.value + serviceFee.value
    })

    const formatPrice = (price) => {
      return new Intl.NumberFormat('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      }).format(price)
    }

    const getRarityColor = (rarity) => {
      return rarities[rarity]?.color || '#gray'
    }

    const getRarityLabel = (rarity) => {
      return rarities[rarity]?.label || rarity
    }

    const removeFromCart = (itemId) => {
      cartItems.value = cartItems.value.filter(item => item.id !== itemId)
    }

    const addToCart = (item) => {
      // Check if item already exists
      const exists = cartItems.value.find(cartItem => cartItem.id === item.id)
      if (!exists) {
        cartItems.value.push(item)
      }
    }

    const viewItem = (item) => {
      router.push(`/marketplace/item/${item.id}`)
    }

    const proceedToCheckout = async () => {
      if (!paymentMethod.value) {
        alert('Por favor, selecione um método de pagamento')
        return
      }

      try {
        processing.value = true
        
        // Mock checkout process
        await new Promise(resolve => setTimeout(resolve, 2000))
        
        // Redirect to checkout
        router.push('/checkout')
        
      } catch (error) {
        console.error('Checkout error:', error)
        alert('Erro ao processar checkout. Tente novamente.')
      } finally {
        processing.value = false
      }
    }

    onMounted(() => {
      // Load cart data from store/API
    })

    return {
      cartItems,
      recommendedItems,
      processing,
      paymentMethod,
      subtotal,
      serviceFee,
      total,
      formatPrice,
      getRarityColor,
      getRarityLabel,
      removeFromCart,
      addToCart,
      viewItem,
      proceedToCheckout
    }
  }
}
</script>
