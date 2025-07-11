<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Sidebar -->
    <div class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0" :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
      <div class="flex items-center justify-center h-16 px-4 border-b border-gray-200 dark:border-gray-700">
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Admin Panel</h1>
      </div>
      
      <nav class="mt-8">
        <div class="px-4 space-y-2">
          <button
            v-for="item in navItems"
            :key="item.id"
            @click="activeSection = item.id"
            :class="[
              'w-full flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200',
              activeSection === item.id
                ? 'bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300'
                : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
            ]"
          >
            <component :is="item.icon" class="w-5 h-5 mr-3"/>
            {{ item.label }}
          </button>
        </div>
      </nav>
    </div>

    <!-- Mobile sidebar overlay -->
    <div v-if="sidebarOpen" class="fixed inset-0 z-40 lg:hidden" @click="sidebarOpen = false">
      <div class="absolute inset-0 bg-black opacity-50"></div>
    </div>

    <!-- Main content -->
    <div class="lg:pl-64">
      <!-- Top bar -->
      <div class="sticky top-0 z-30 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between h-16 px-4">
          <button
            @click="sidebarOpen = !sidebarOpen"
            class="lg:hidden p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
          </button>
          
          <div class="flex items-center space-x-4">
            <div class="relative">
              <button class="p-2 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5-5-5h5zm0-14h5l-5-5-5 5h5z"/>
                </svg>
              </button>
            </div>
            <div class="flex items-center space-x-2">
              <img class="w-8 h-8 rounded-full" src="/images/admin-avatar.jpg" alt="Admin">
              <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Admin User</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div class="p-6">
        <!-- Dashboard -->
        <div v-if="activeSection === 'dashboard'">
          <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Dashboard</h2>
          
          <!-- Stats Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
              <div class="flex items-center">
                <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-full">
                  <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                  </svg>
                </div>
                <div class="ml-4">
                  <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ stats.totalUsers }}</h3>
                  <p class="text-sm text-gray-600 dark:text-gray-400">Total Users</p>
                </div>
              </div>
            </div>
            
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
              <div class="flex items-center">
                <div class="p-3 bg-green-100 dark:bg-green-900 rounded-full">
                  <svg class="w-6 h-6 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                  </svg>
                </div>
                <div class="ml-4">
                  <h3 class="text-lg font-semibold text-gray-900 dark:text-white">R$ {{ formatCurrency(stats.totalRevenue) }}</h3>
                  <p class="text-sm text-gray-600 dark:text-gray-400">Total Revenue</p>
                </div>
              </div>
            </div>
            
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
              <div class="flex items-center">
                <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-full">
                  <svg class="w-6 h-6 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                  </svg>
                </div>
                <div class="ml-4">
                  <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ stats.totalSkins }}</h3>
                  <p class="text-sm text-gray-600 dark:text-gray-400">Total Skins</p>
                </div>
              </div>
            </div>
            
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
              <div class="flex items-center">
                <div class="p-3 bg-yellow-100 dark:bg-yellow-900 rounded-full">
                  <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                  </svg>
                </div>
                <div class="ml-4">
                  <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ stats.activeTrades }}</h3>
                  <p class="text-sm text-gray-600 dark:text-gray-400">Active Trades</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Recent Activity -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
              <h3 class="text-lg font-medium text-gray-900 dark:text-white">Recent Activity</h3>
            </div>
            <div class="p-6">
              <div class="space-y-4">
                <div v-for="activity in recentActivity" :key="activity.id" class="flex items-center space-x-4">
                  <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-gray-200 dark:bg-gray-600 rounded-full flex items-center justify-center">
                      <svg class="w-4 h-4 text-gray-600 dark:text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                      </svg>
                    </div>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-900 dark:text-white">{{ activity.description }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ formatDate(activity.timestamp) }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Users Management -->
        <div v-if="activeSection === 'users'">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Users Management</h2>
            <button class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition duration-200">
              Add User
            </button>
          </div>
          
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
              <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">All Users</h3>
                <div class="flex space-x-2">
                  <input
                    v-model="userSearch"
                    type="text"
                    placeholder="Search users..."
                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                  >
                </div>
              </div>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Joined</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                  <tr v-for="user in filteredUsers" :key="user.id">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <img class="w-10 h-10 rounded-full" :src="user.avatar" :alt="user.name">
                        <div class="ml-4">
                          <div class="text-sm font-medium text-gray-900 dark:text-white">{{ user.name }}</div>
                          <div class="text-sm text-gray-500 dark:text-gray-400">{{ user.steamId }}</div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ user.email }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="[
                        'px-2 py-1 text-xs font-medium rounded-full',
                        user.status === 'active' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' :
                        user.status === 'suspended' ? 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200' :
                        'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200'
                      ]">
                        {{ user.status }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ formatDate(user.createdAt) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <button class="text-purple-600 dark:text-purple-400 hover:text-purple-900 dark:hover:text-purple-300 mr-3">Edit</button>
                      <button class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">Suspend</button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Transactions -->
        <div v-if="activeSection === 'transactions'">
          <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Transactions</h2>
          
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
              <h3 class="text-lg font-medium text-gray-900 dark:text-white">Recent Transactions</h3>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                  </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                  <tr v-for="transaction in transactions" :key="transaction.id">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">#{{ transaction.id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ transaction.user }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ transaction.type }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">R$ {{ formatCurrency(transaction.amount) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="[
                        'px-2 py-1 text-xs font-medium rounded-full',
                        transaction.status === 'completed' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' :
                        transaction.status === 'pending' ? 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200' :
                        'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200'
                      ]">
                        {{ transaction.status }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ formatDate(transaction.date) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- System Settings -->
        <div v-if="activeSection === 'settings'">
          <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">System Settings</h2>
          
          <div class="space-y-6">
            <!-- Platform Settings -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
              <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Platform Settings</h3>
              <div class="space-y-4">
                <div class="flex items-center justify-between">
                  <div>
                    <label class="text-sm font-medium text-gray-900 dark:text-white">Maintenance Mode</label>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Enable to temporarily disable the platform</p>
                  </div>
                  <button
                    @click="toggleMaintenanceMode"
                    :class="[
                      'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2',
                      settings.maintenanceMode ? 'bg-purple-600' : 'bg-gray-200 dark:bg-gray-600'
                    ]"
                  >
                    <span
                      :class="[
                        'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                        settings.maintenanceMode ? 'translate-x-5' : 'translate-x-0'
                      ]"
                    />
                  </button>
                </div>
              </div>
            </div>

            <!-- Security Settings -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
              <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Security Settings</h3>
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Service Fee (%)</label>
                  <input
                    v-model="settings.serviceFee"
                    type="number"
                    step="0.1"
                    min="0"
                    max="10"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                  >
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Max Transaction Amount (R$)</label>
                  <input
                    v-model="settings.maxTransactionAmount"
                    type="number"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                  >
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue'

export default {
  name: 'AdminPanel',
  setup() {
    const sidebarOpen = ref(false)
    const activeSection = ref('dashboard')
    const userSearch = ref('')

    const navItems = [
      { id: 'dashboard', label: 'Dashboard', icon: 'DashboardIcon' },
      { id: 'users', label: 'Users', icon: 'UsersIcon' },
      { id: 'transactions', label: 'Transactions', icon: 'CurrencyIcon' },
      { id: 'settings', label: 'Settings', icon: 'CogIcon' }
    ]

    const stats = reactive({
      totalUsers: 15420,
      totalRevenue: 487320.50,
      totalSkins: 89651,
      activeTrades: 1247
    })

    const settings = reactive({
      maintenanceMode: false,
      serviceFee: 5.0,
      maxTransactionAmount: 10000
    })

    const recentActivity = ref([
      {
        id: 1,
        description: 'New user registration: PlayerPro123',
        timestamp: new Date('2024-01-15T10:30:00')
      },
      {
        id: 2,
        description: 'Transaction completed: AK-47 Redline sold for R$ 150.00',
        timestamp: new Date('2024-01-15T10:15:00')
      },
      {
        id: 3,
        description: 'Trade completed between SkinTrader and ProGamer',
        timestamp: new Date('2024-01-15T09:45:00')
      }
    ])

    const users = ref([
      {
        id: 1,
        name: 'PlayerPro123',
        email: 'player@example.com',
        avatar: '/images/avatar1.jpg',
        steamId: '76561198000000001',
        status: 'active',
        createdAt: new Date('2024-01-01')
      },
      {
        id: 2,
        name: 'SkinTrader456',
        email: 'trader@example.com',
        avatar: '/images/avatar2.jpg',
        steamId: '76561198000000002',
        status: 'active',
        createdAt: new Date('2024-01-02')
      },
      {
        id: 3,
        name: 'CheaterUser',
        email: 'cheater@example.com',
        avatar: '/images/avatar3.jpg',
        steamId: '76561198000000003',
        status: 'suspended',
        createdAt: new Date('2024-01-03')
      }
    ])

    const transactions = ref([
      {
        id: 1001,
        user: 'PlayerPro123',
        type: 'Purchase',
        amount: 150.00,
        status: 'completed',
        date: new Date('2024-01-15')
      },
      {
        id: 1002,
        user: 'SkinTrader456',
        type: 'Sale',
        amount: 320.50,
        status: 'pending',
        date: new Date('2024-01-15')
      },
      {
        id: 1003,
        user: 'ProGamer789',
        type: 'Trade',
        amount: 0.00,
        status: 'completed',
        date: new Date('2024-01-14')
      }
    ])

    const filteredUsers = computed(() => {
      if (!userSearch.value) return users.value
      return users.value.filter(user => 
        user.name.toLowerCase().includes(userSearch.value.toLowerCase()) ||
        user.email.toLowerCase().includes(userSearch.value.toLowerCase())
      )
    })

    const formatCurrency = (amount) => {
      return new Intl.NumberFormat('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      }).format(amount)
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

    const toggleMaintenanceMode = () => {
      settings.maintenanceMode = !settings.maintenanceMode
    }

    onMounted(() => {
      // Load admin data
    })

    return {
      sidebarOpen,
      activeSection,
      userSearch,
      navItems,
      stats,
      settings,
      recentActivity,
      users,
      transactions,
      filteredUsers,
      formatCurrency,
      formatDate,
      toggleMaintenanceMode
    }
  }
}
</script>
