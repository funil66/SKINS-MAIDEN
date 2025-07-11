<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Header -->
    <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <!-- Logo -->
          <div class="flex items-center">
            <img src="/logo.svg" alt="Iron Code Skins" class="h-8 w-auto">
            <span class="ml-2 text-xl font-bold text-gray-900 dark:text-white">Iron Code Skins</span>
          </div>

          <!-- Navigation -->
          <nav class="hidden md:flex space-x-8">
            <router-link to="/dashboard" class="text-purple-600 dark:text-purple-400 font-medium">
              Dashboard
            </router-link>
            <router-link to="/marketplace" class="text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400">
              Marketplace
            </router-link>
            <router-link to="/inventory" class="text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400">
              Inventário
            </router-link>
            <router-link to="/trades" class="text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400">
              Trades
            </router-link>
          </nav>

          <!-- User Menu -->
          <div class="flex items-center space-x-4">
            <!-- Notifications -->
            <button class="relative p-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17H4l5 5v-5z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <span v-if="notifications.length > 0" class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-400"></span>
            </button>

            <!-- User Avatar -->
            <div class="flex items-center space-x-3">
              <img :src="user.avatar || '/default-avatar.png'" alt="Avatar" class="h-8 w-8 rounded-full">
              <span class="hidden md:block text-sm font-medium text-gray-900 dark:text-white">{{ user.name }}</span>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <!-- Welcome Section -->
      <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
          Bem-vindo, {{ user.name }}!
        </h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
          Aqui está um resumo da sua atividade hoje.
        </p>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-8 w-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                    Saldo Total
                  </dt>
                  <dd class="text-lg font-medium text-gray-900 dark:text-white">
                    R$ {{ formatCurrency(stats.totalBalance) }}
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-8 w-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                    Itens no Inventário
                  </dt>
                  <dd class="text-lg font-medium text-gray-900 dark:text-white">
                    {{ stats.inventoryCount }}
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-8 w-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                    Trades Ativas
                  </dt>
                  <dd class="text-lg font-medium text-gray-900 dark:text-white">
                    {{ stats.activeTrades }}
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-8 w-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                    Reputação
                  </dt>
                  <dd class="text-lg font-medium text-gray-900 dark:text-white">
                    {{ stats.reputation }}/5.0
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-8">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white">Ações Rápidas</h3>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <router-link
              to="/marketplace"
              class="flex items-center p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/30 transition duration-200"
            >
              <svg class="h-8 w-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
              </svg>
              <div class="ml-4">
                <p class="text-sm font-medium text-purple-600 dark:text-purple-400">Comprar Skins</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Explore o marketplace</p>
              </div>
            </router-link>

            <router-link
              to="/inventory"
              class="flex items-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30 transition duration-200"
            >
              <svg class="h-8 w-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
              </svg>
              <div class="ml-4">
                <p class="text-sm font-medium text-green-600 dark:text-green-400">Vender Skins</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Monetize seu inventário</p>
              </div>
            </router-link>

            <router-link
              to="/steam-sync"
              class="flex items-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition duration-200"
            >
              <svg class="h-8 w-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
              </svg>
              <div class="ml-4">
                <p class="text-sm font-medium text-blue-600 dark:text-blue-400">Sincronizar Steam</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Atualizar inventário</p>
              </div>
            </router-link>
          </div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white">Atividade Recente</h3>
        </div>
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
          <div v-for="activity in recentActivity" :key="activity.id" class="p-6">
            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div :class="getActivityIcon(activity.type)" class="h-8 w-8 rounded-full flex items-center justify-center">
                    <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path v-if="activity.type === 'trade'" d="M8 5a1 1 0 100 2h5.586l-1.293 1.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L13.586 5H8zM12 15a1 1 0 100-2H6.414l1.293-1.293a1 1 0 10-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L6.414 15H12z"/>
                      <path v-else-if="activity.type === 'purchase'" d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/>
                      <path v-else d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-900 dark:text-white">{{ activity.description }}</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">{{ formatDate(activity.createdAt) }}</p>
                </div>
              </div>
              <div class="text-sm font-medium" :class="getActivityAmountClass(activity.type)">
                {{ activity.amount ? formatCurrency(activity.amount) : '' }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'

export default {
  name: 'Dashboard',
  setup() {
    const authStore = useAuthStore()
    const user = ref(authStore.user)
    const notifications = ref([])
    
    const stats = reactive({
      totalBalance: 1250.00,
      inventoryCount: 47,
      activeTrades: 3,
      reputation: 4.8
    })

    const recentActivity = ref([
      {
        id: 1,
        type: 'trade',
        description: 'Trade finalizado: AK-47 Redline',
        amount: 150.00,
        createdAt: new Date(Date.now() - 1000 * 60 * 30) // 30 min ago
      },
      {
        id: 2,
        type: 'purchase',
        description: 'Compra realizada: AWP Dragon Lore',
        amount: -2500.00,
        createdAt: new Date(Date.now() - 1000 * 60 * 60 * 2) // 2 hours ago
      },
      {
        id: 3,
        type: 'sale',
        description: 'Venda concluída: Glock Fade',
        amount: 300.00,
        createdAt: new Date(Date.now() - 1000 * 60 * 60 * 5) // 5 hours ago
      }
    ])

    const formatCurrency = (amount) => {
      return new Intl.NumberFormat('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      }).format(Math.abs(amount))
    }

    const formatDate = (date) => {
      return new Intl.RelativeTimeFormat('pt-BR', { numeric: 'auto' }).format(
        Math.round((date - new Date()) / (1000 * 60)),
        'minute'
      )
    }

    const getActivityIcon = (type) => {
      const icons = {
        trade: 'bg-yellow-500',
        purchase: 'bg-red-500',
        sale: 'bg-green-500'
      }
      return icons[type] || 'bg-gray-500'
    }

    const getActivityAmountClass = (type) => {
      return type === 'purchase' ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'
    }

    onMounted(() => {
      // Load dashboard data
      console.log('Dashboard mounted')
    })

    return {
      user,
      notifications,
      stats,
      recentActivity,
      formatCurrency,
      formatDate,
      getActivityIcon,
      getActivityAmountClass
    }
  }
}
</script>
