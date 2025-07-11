<template>
  <div id="app" class="min-h-screen bg-gray-50">
    <!-- Header/Navigation -->
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <!-- Logo -->
          <div class="flex items-center">
            <router-link to="/" class="flex items-center space-x-3">
              <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                </svg>
              </div>
              <span class="text-xl font-bold text-gray-900">Iron Code Skins</span>
            </router-link>
          </div>
          
          <!-- Navigation -->
          <nav class="hidden md:flex space-x-8">
            <router-link
              to="/dashboard"
              class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium transition-colors"
              :class="{ 'text-blue-600 bg-blue-50': $route.path.startsWith('/dashboard') }"
            >
              Dashboard
            </router-link>
            
            <router-link
              to="/audit"
              class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium transition-colors"
              :class="{ 'text-blue-600 bg-blue-50': $route.path.startsWith('/audit') }"
            >
              Auditoria
            </router-link>
            
            <router-link
              to="/evidence"
              class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium transition-colors"
              :class="{ 'text-blue-600 bg-blue-50': $route.path.startsWith('/evidence') }"
            >
              Evidências
            </router-link>
            
            <router-link
              to="/blockchain"
              class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium transition-colors"
              :class="{ 'text-blue-600 bg-blue-50': $route.path.startsWith('/blockchain') }"
            >
              Blockchain
            </router-link>
          </nav>
          
          <!-- User Menu -->
          <div class="flex items-center space-x-4">
            <!-- Notifications -->
            <button
              @click="toggleNotifications"
              class="text-gray-400 hover:text-gray-500 relative"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5-5-5 5h5zm0 0v3"/>
              </svg>
              <span v-if="unreadNotifications > 0" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                {{ unreadNotifications }}
              </span>
            </button>
            
            <!-- Profile Dropdown -->
            <div class="relative">
              <button
                @click="toggleProfileMenu"
                class="flex items-center space-x-2 text-gray-700 hover:text-gray-900"
              >
                <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                  <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                  </svg>
                </div>
                <span class="text-sm font-medium">{{ currentUser?.name || 'Usuário' }}</span>
              </button>
              
              <!-- Profile Dropdown Menu -->
              <div
                v-if="showProfileMenu"
                @click.away="showProfileMenu = false"
                class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg border border-gray-200 z-50"
              >
                <div class="py-1">
                  <router-link to="/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    Perfil
                  </router-link>
                  <router-link to="/settings" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    Configurações
                  </router-link>
                  <hr class="border-gray-200">
                  <button @click="logout" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    Sair
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <router-view />
    </main>

    <!-- Global Loading Overlay -->
    <div
      v-if="globalLoading"
      class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-lg p-6 shadow-xl">
        <div class="flex items-center space-x-3">
          <svg class="animate-spin h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span class="text-lg font-medium text-gray-900">{{ loadingMessage }}</span>
        </div>
      </div>
    </div>

    <!-- Toast Notifications -->
    <div class="fixed top-4 right-4 z-50 space-y-2">
      <div
        v-for="notification in notifications"
        :key="notification.id"
        :class="[
          'max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 transform transition-all duration-300',
          notification.entering ? 'translate-x-0' : 'translate-x-full'
        ]"
      >
        <div class="p-4">
          <div class="flex items-start">
            <div class="flex-shrink-0">
              <svg
                v-if="notification.type === 'success'"
                class="h-6 w-6 text-green-400"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              
              <svg
                v-else-if="notification.type === 'error'"
                class="h-6 w-6 text-red-400"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              
              <svg
                v-else-if="notification.type === 'warning'"
                class="h-6 w-6 text-yellow-400"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 15.5c-.77.833.192 2.5 1.732 2.5z"/>
              </svg>
              
              <svg
                v-else
                class="h-6 w-6 text-blue-400"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            
            <div class="ml-3 w-0 flex-1">
              <p class="text-sm font-medium text-gray-900">
                {{ notification.title }}
              </p>
              <p v-if="notification.message" class="mt-1 text-sm text-gray-500">
                {{ notification.message }}
              </p>
            </div>
            
            <div class="ml-4 flex-shrink-0 flex">
              <button
                @click="removeNotification(notification.id)"
                class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none"
              >
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"/>
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-auto">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-500">
            © 2025 Iron Code Skins. Sistema de Auditoria Blockchain v{{ $version }}
          </div>
          
          <div class="flex items-center space-x-4 text-sm text-gray-500">
            <span>Sprint 2 - Sistema de Evidências</span>
            <div class="w-2 h-2 bg-green-400 rounded-full" title="Sistema Online"></div>
          </div>
        </div>
      </div>
    </footer>

    <!-- Container de Notificações -->
    <NotificationContainer @action="handleNotificationAction" />
  </div>
</template>

<script>
import { ref, computed, onMounted, provide } from 'vue'
import NotificationContainer from '@/components/ui/NotificationContainer.vue'
import { useNotifications } from '@/composables/useNotifications'

export default {
  name: 'App',
  components: {
    NotificationContainer
  },
  setup() {
    // State
    const globalLoading = ref(false)
    const loadingMessage = ref('Carregando...')
    const showProfileMenu = ref(false)
    const showNotifications = ref(false)
    const notifications = ref([])
    const currentUser = ref({
      name: 'Administrador',
      email: 'admin@ironcodeskins.com'
    })

    // Computed
    const unreadNotifications = computed(() => {
      return notifications.value.filter(n => !n.read).length
    })

    // Methods
    function toggleProfileMenu() {
      showProfileMenu.value = !showProfileMenu.value
      showNotifications.value = false
    }

    function toggleNotifications() {
      showNotifications.value = !showNotifications.value
      showProfileMenu.value = false
    }

    function addNotification(notification) {
      const id = Date.now() + Math.random()
      const newNotification = {
        id,
        entering: false,
        ...notification
      }
      
      notifications.value.push(newNotification)
      
      // Trigger enter animation
      setTimeout(() => {
        const notif = notifications.value.find(n => n.id === id)
        if (notif) notif.entering = true
      }, 100)
      
      // Auto-remove after 5 seconds
      setTimeout(() => {
        removeNotification(id)
      }, 5000)
    }

    function removeNotification(id) {
      const index = notifications.value.findIndex(n => n.id === id)
      if (index > -1) {
        notifications.value[index].entering = false
        setTimeout(() => {
          notifications.value.splice(index, 1)
        }, 300)
      }
    }

    function showGlobalLoading(message = 'Carregando...') {
      loadingMessage.value = message
      globalLoading.value = true
    }

    function hideGlobalLoading() {
      globalLoading.value = false
    }

    function logout() {
      // Clear authentication
      localStorage.removeItem('auth_token')
      
      // Redirect to login
      this.$router.push('/login')
      
      addNotification({
        type: 'success',
        title: 'Logout realizado',
        message: 'Você foi desconectado com sucesso'
      })
    }

    // Handler para ações de notificação
    function handleNotificationAction(event) {
      console.log('Ação de notificação:', event)
      
      switch (event.action) {
        case 'stop-recording':
          // Emitir evento global para parar gravação
          window.dispatchEvent(new CustomEvent('stop-recording'))
          break
          
        case 'retry-blockchain':
          // Tentar operação blockchain novamente
          console.log('Tentando blockchain novamente...')
          break
          
        case 'contact-support':
          // Abrir chat de suporte ou email
          window.open('mailto:suporte@ironcodeskins.com?subject=Suporte Sistema Auditoria')
          break
          
        default:
          console.warn('Ação não implementada:', event.action)
      }
    }

    // Provide global methods to child components
    const provide = {
      addNotification,
      showGlobalLoading,
      hideGlobalLoading
    }

    // Global error handler
    window.addEventListener('unhandledrejection', (event) => {
      console.error('Unhandled promise rejection:', event.reason)
      addNotification({
        type: 'error',
        title: 'Erro não tratado',
        message: 'Ocorreu um erro inesperado. Verifique o console para mais detalhes.'
      })
    })

    // Initialize app
    onMounted(() => {
      // Show welcome notification
      addNotification({
        type: 'success',
        title: 'Sistema Iniciado',
        message: 'Iron Code Skins - Sistema de Auditoria Blockchain ativo'
      })
    })

    return {
      // State
      globalLoading,
      loadingMessage,
      showProfileMenu,
      showNotifications,
      notifications,
      currentUser,
      
      // Computed
      unreadNotifications,
      isLoading: computed(() => globalLoading.value),
      
      // Methods
      toggleProfileMenu,
      toggleNotifications,
      addNotification,
      removeNotification,
      showGlobalLoading,
      hideGlobalLoading,
      logout,
      handleNotificationAction,
      
      // Provide
      provide
    }
  }
}
</script>

<style>
/* Global styles são aplicados via Tailwind CSS no main.css */

/* Transições customizadas */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

/* Loading overlay personalizado */
.loading-overlay {
  backdrop-filter: blur(4px);
}
</style>
