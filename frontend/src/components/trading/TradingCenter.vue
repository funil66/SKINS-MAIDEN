<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Centro de Trocas</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
              Troque seus itens de forma segura com outros jogadores
            </p>
          </div>
          
          <div class="mt-4 lg:mt-0 flex space-x-4">
            <button
              @click="createNewTrade"
              class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 transition duration-200"
            >
              Nova Troca
            </button>
            <button
              @click="openInventory"
              class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 transition duration-200"
            >
              Meu Inventário
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Trade Filters -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Status da Troca
            </label>
            <select
              v-model="filters.status"
              class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
            >
              <option value="">Todas</option>
              <option value="active">Ativas</option>
              <option value="pending">Pendentes</option>
              <option value="completed">Concluídas</option>
              <option value="cancelled">Canceladas</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Tipo de Item
            </label>
            <select
              v-model="filters.itemType"
              class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
            >
              <option value="">Todos</option>
              <option value="knife">Facas</option>
              <option value="rifle">Rifles</option>
              <option value="pistol">Pistolas</option>
              <option value="gloves">Luvas</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Valor Estimado
            </label>
            <select
              v-model="filters.valueRange"
              class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
            >
              <option value="">Qualquer valor</option>
              <option value="0-100">R$ 0 - 100</option>
              <option value="100-500">R$ 100 - 500</option>
              <option value="500-1000">R$ 500 - 1000</option>
              <option value="1000+">R$ 1000+</option>
            </select>
          </div>
          
          <div class="flex items-end">
            <button
              @click="applyFilters"
              class="w-full bg-purple-600 text-white py-2 px-4 rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 transition duration-200"
            >
              Filtrar
            </button>
          </div>
        </div>
      </div>

      <!-- Active Trades -->
      <div class="grid grid-cols-1 gap-6">
        <div
          v-for="trade in filteredTrades"
          :key="trade.id"
          class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden"
        >
          <!-- Trade Header -->
          <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2">
                  <img
                    :src="trade.initiator.avatar"
                    :alt="trade.initiator.name"
                    class="w-8 h-8 rounded-full"
                  >
                  <span class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ trade.initiator.name }}
                  </span>
                </div>
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"/>
                </svg>
                <div class="flex items-center space-x-2">
                  <img
                    :src="trade.target.avatar"
                    :alt="trade.target.name"
                    class="w-8 h-8 rounded-full"
                  >
                  <span class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ trade.target.name }}
                  </span>
                </div>
              </div>
              
              <div class="flex items-center space-x-4">
                <span
                  :class="[
                    'px-3 py-1 rounded-full text-xs font-medium',
                    getStatusClass(trade.status)
                  ]"
                >
                  {{ getStatusLabel(trade.status) }}
                </span>
                <span class="text-sm text-gray-600 dark:text-gray-400">
                  {{ formatDate(trade.createdAt) }}
                </span>
              </div>
            </div>
          </div>

          <!-- Trade Items -->
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
              <!-- Initiator Items -->
              <div>
                <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-4">
                  {{ trade.initiator.name }} oferece:
                </h3>
                <div class="grid grid-cols-2 gap-3">
                  <div
                    v-for="item in trade.initiatorItems"
                    :key="item.id"
                    class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3"
                  >
                    <img
                      :src="item.image"
                      :alt="item.name"
                      class="w-full h-16 object-cover rounded mb-2"
                    >
                    <div class="text-xs font-medium text-gray-900 dark:text-white truncate">
                      {{ item.name }}
                    </div>
                    <div class="text-xs text-gray-600 dark:text-gray-400">
                      R$ {{ formatPrice(item.value) }}
                    </div>
                  </div>
                </div>
                <div class="mt-3 text-sm font-medium text-gray-900 dark:text-white">
                  Total: R$ {{ formatPrice(trade.initiatorValue) }}
                </div>
              </div>

              <!-- Target Items -->
              <div>
                <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-4">
                  {{ trade.target.name }} recebe:
                </h3>
                <div class="grid grid-cols-2 gap-3">
                  <div
                    v-for="item in trade.targetItems"
                    :key="item.id"
                    class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3"
                  >
                    <img
                      :src="item.image"
                      :alt="item.name"
                      class="w-full h-16 object-cover rounded mb-2"
                    >
                    <div class="text-xs font-medium text-gray-900 dark:text-white truncate">
                      {{ item.name }}
                    </div>
                    <div class="text-xs text-gray-600 dark:text-gray-400">
                      R$ {{ formatPrice(item.value) }}
                    </div>
                  </div>
                </div>
                <div class="mt-3 text-sm font-medium text-gray-900 dark:text-white">
                  Total: R$ {{ formatPrice(trade.targetValue) }}
                </div>
              </div>
            </div>

            <!-- Trade Actions -->
            <div class="mt-6 flex justify-between items-center pt-4 border-t border-gray-200 dark:border-gray-700">
              <div class="flex items-center space-x-2">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                  Diferença de valor:
                </span>
                <span
                  :class="[
                    'text-sm font-medium',
                    trade.valueDifference > 0 ? 'text-green-600 dark:text-green-400' : 
                    trade.valueDifference < 0 ? 'text-red-600 dark:text-red-400' : 
                    'text-gray-600 dark:text-gray-400'
                  ]"
                >
                  {{ trade.valueDifference > 0 ? '+' : '' }}R$ {{ formatPrice(Math.abs(trade.valueDifference)) }}
                </span>
              </div>
              
              <div class="flex space-x-3">
                <button
                  v-if="trade.status === 'pending' && canRespondToTrade(trade)"
                  @click="declineTrade(trade.id)"
                  class="px-4 py-2 text-sm bg-red-600 text-white rounded hover:bg-red-700 transition duration-200"
                >
                  Recusar
                </button>
                <button
                  v-if="trade.status === 'pending' && canRespondToTrade(trade)"
                  @click="acceptTrade(trade.id)"
                  class="px-4 py-2 text-sm bg-green-600 text-white rounded hover:bg-green-700 transition duration-200"
                >
                  Aceitar
                </button>
                <button
                  v-if="trade.status === 'active'"
                  @click="viewTradeDetails(trade.id)"
                  class="px-4 py-2 text-sm bg-purple-600 text-white rounded hover:bg-purple-700 transition duration-200"
                >
                  Ver Detalhes
                </button>
                <button
                  v-if="canCancelTrade(trade)"
                  @click="cancelTrade(trade.id)"
                  class="px-4 py-2 text-sm bg-gray-600 text-white rounded hover:bg-gray-700 transition duration-200"
                >
                  Cancelar
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="filteredTrades.length === 0" class="text-center py-12">
        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
        </svg>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
          Nenhuma troca encontrada
        </h3>
        <p class="text-gray-600 dark:text-gray-400 mb-6">
          Comece criando uma nova troca ou ajuste os filtros
        </p>
        <button
          @click="createNewTrade"
          class="bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 transition duration-200"
        >
          Criar Nova Troca
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

export default {
  name: 'TradingCenter',
  setup() {
    const router = useRouter()
    const currentUserId = ref(1) // Mock current user ID

    const filters = reactive({
      status: '',
      itemType: '',
      valueRange: ''
    })

    // Mock trades data
    const trades = ref([
      {
        id: 1,
        status: 'pending',
        createdAt: new Date('2024-01-15'),
        initiator: {
          id: 2,
          name: 'TraderPro123',
          avatar: '/images/avatar1.jpg'
        },
        target: {
          id: 1,
          name: 'CurrentUser',
          avatar: '/images/current-user-avatar.jpg'
        },
        initiatorItems: [
          {
            id: 1,
            name: 'AK-47 | Redline',
            image: '/images/ak47-redline.jpg',
            value: 150.00
          },
          {
            id: 2,
            name: 'Glock-18 | Fade',
            image: '/images/glock-fade.jpg',
            value: 80.00
          }
        ],
        targetItems: [
          {
            id: 3,
            name: 'AWP | Asiimov',
            image: '/images/awp-asiimov.jpg',
            value: 200.00
          }
        ],
        initiatorValue: 230.00,
        targetValue: 200.00,
        valueDifference: -30.00
      },
      {
        id: 2,
        status: 'active',
        createdAt: new Date('2024-01-14'),
        initiator: {
          id: 1,
          name: 'CurrentUser',
          avatar: '/images/current-user-avatar.jpg'
        },
        target: {
          id: 3,
          name: 'SkinCollector',
          avatar: '/images/avatar2.jpg'
        },
        initiatorItems: [
          {
            id: 4,
            name: 'Karambit | Fade',
            image: '/images/karambit-fade.jpg',
            value: 800.00
          }
        ],
        targetItems: [
          {
            id: 5,
            name: 'M4A4 | Howl',
            image: '/images/m4a4-howl.jpg',
            value: 750.00
          },
          {
            id: 6,
            name: 'Desert Eagle | Blaze',
            image: '/images/deagle-blaze.jpg',
            value: 100.00
          }
        ],
        initiatorValue: 800.00,
        targetValue: 850.00,
        valueDifference: 50.00
      }
    ])

    const filteredTrades = computed(() => {
      let result = trades.value

      if (filters.status) {
        result = result.filter(trade => trade.status === filters.status)
      }

      // Add more filter logic as needed

      return result
    })

    const formatPrice = (price) => {
      return new Intl.NumberFormat('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      }).format(price)
    }

    const formatDate = (date) => {
      return new Intl.DateTimeFormat('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      }).format(date)
    }

    const getStatusClass = (status) => {
      const classes = {
        pending: 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200',
        active: 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200',
        completed: 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200',
        cancelled: 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200'
      }
      return classes[status] || classes.pending
    }

    const getStatusLabel = (status) => {
      const labels = {
        pending: 'Pendente',
        active: 'Ativa',
        completed: 'Concluída',
        cancelled: 'Cancelada'
      }
      return labels[status] || status
    }

    const canRespondToTrade = (trade) => {
      return trade.target.id === currentUserId.value && trade.status === 'pending'
    }

    const canCancelTrade = (trade) => {
      return (trade.initiator.id === currentUserId.value || trade.target.id === currentUserId.value) &&
             (trade.status === 'pending' || trade.status === 'active')
    }

    const applyFilters = () => {
      // Filters are automatically applied via computed property
    }

    const createNewTrade = () => {
      router.push('/trading/new')
    }

    const openInventory = () => {
      router.push('/inventory')
    }

    const acceptTrade = async (tradeId) => {
      try {
        // Mock API call
        const trade = trades.value.find(t => t.id === tradeId)
        if (trade) {
          trade.status = 'active'
        }
        console.log('Trade accepted:', tradeId)
      } catch (error) {
        console.error('Error accepting trade:', error)
      }
    }

    const declineTrade = async (tradeId) => {
      try {
        // Mock API call
        const trade = trades.value.find(t => t.id === tradeId)
        if (trade) {
          trade.status = 'cancelled'
        }
        console.log('Trade declined:', tradeId)
      } catch (error) {
        console.error('Error declining trade:', error)
      }
    }

    const cancelTrade = async (tradeId) => {
      try {
        // Mock API call
        const trade = trades.value.find(t => t.id === tradeId)
        if (trade) {
          trade.status = 'cancelled'
        }
        console.log('Trade cancelled:', tradeId)
      } catch (error) {
        console.error('Error cancelling trade:', error)
      }
    }

    const viewTradeDetails = (tradeId) => {
      router.push(`/trading/trade/${tradeId}`)
    }

    onMounted(() => {
      // Load trades data
    })

    return {
      filters,
      trades,
      filteredTrades,
      formatPrice,
      formatDate,
      getStatusClass,
      getStatusLabel,
      canRespondToTrade,
      canCancelTrade,
      applyFilters,
      createNewTrade,
      openInventory,
      acceptTrade,
      declineTrade,
      cancelTrade,
      viewTradeDetails
    }
  }
}
</script>
