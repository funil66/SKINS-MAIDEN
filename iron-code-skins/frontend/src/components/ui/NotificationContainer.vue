<template>
  <Teleport to="body">
    <div 
      v-if="notifications.length > 0"
      class="fixed top-4 right-4 z-50 space-y-3 max-w-sm"
    >
      <TransitionGroup
        name="notification"
        tag="div"
        class="space-y-3"
      >
        <div
          v-for="notification in notifications"
          :key="notification.id"
          :class="getNotificationClasses(notification.type)"
          class="notification-item"
        >
          <!-- Header -->
          <div class="flex items-start justify-between">
            <div class="flex items-start space-x-3 flex-1">
              <!-- Icon -->
              <div 
                class="flex-shrink-0 mt-0.5"
                v-html="getNotificationIcon(notification.type)"
              ></div>
              
              <!-- Content -->
              <div class="flex-1 min-w-0">
                <h4 
                  v-if="notification.title"
                  class="text-sm font-medium truncate"
                >
                  {{ notification.title }}
                </h4>
                
                <p 
                  v-if="notification.message"
                  class="text-sm mt-1 break-words"
                  :class="notification.title ? 'text-opacity-90' : ''"
                >
                  {{ notification.message }}
                </p>
                
                <!-- Progress Bar (for upload notifications) -->
                <div 
                  v-if="notification.showProgress && notification.progress !== undefined"
                  class="mt-2"
                >
                  <div class="w-full bg-gray-200 rounded-full h-2">
                    <div 
                      class="bg-current h-2 rounded-full transition-all duration-300"
                      :style="{ width: notification.progress + '%' }"
                    ></div>
                  </div>
                  <p class="text-xs mt-1 opacity-75">
                    {{ notification.progress }}% concluído
                  </p>
                </div>
              </div>
            </div>
            
            <!-- Close Button -->
            <button
              @click="removeNotification(notification.id)"
              class="flex-shrink-0 ml-3 p-1 rounded-md hover:bg-black hover:bg-opacity-10 transition-colors"
              :title="'Fechar notificação'"
            >
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"/>
              </svg>
            </button>
          </div>
          
          <!-- Actions -->
          <div 
            v-if="notification.actions && notification.actions.length > 0"
            class="mt-3 flex space-x-2"
          >
            <button
              v-for="action in notification.actions"
              :key="action.label"
              @click="handleNotificationAction(notification, action)"
              :class="[
                'text-xs px-3 py-1 rounded-md font-medium transition-colors',
                action.type === 'danger' 
                  ? 'bg-red-600 text-white hover:bg-red-700' 
                  : 'bg-white bg-opacity-20 hover:bg-opacity-30'
              ]"
            >
              {{ action.label }}
            </button>
          </div>
          
          <!-- Timestamp -->
          <div 
            v-if="showTimestamp"
            class="mt-2 text-xs opacity-60"
          >
            {{ $formatRelativeDate(notification.createdAt) }}
          </div>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script>
import { computed } from 'vue'
import { useNotifications } from '@/composables/useNotifications'

export default {
  name: 'NotificationContainer',
  props: {
    showTimestamp: {
      type: Boolean,
      default: false
    },
    maxNotifications: {
      type: Number,
      default: 5
    }
  },
  emits: ['action'],
  setup(props, { emit }) {
    const {
      notifications: allNotifications,
      removeNotification,
      getNotificationClasses,
      getNotificationIcon
    } = useNotifications()

    // Limitar número de notificações exibidas
    const notifications = computed(() => {
      return allNotifications.slice(-props.maxNotifications)
    })

    // Lidar com ações das notificações
    const handleNotificationAction = (notification, action) => {
      // Emitir evento para componente pai
      emit('action', {
        notification,
        action: action.action,
        actionType: action.type
      })
      
      // Ações específicas do sistema
      switch (action.action) {
        case 'retry-blockchain':
          // Lógica para tentar blockchain novamente
          console.log('Tentando blockchain novamente...')
          break
          
        case 'contact-support':
          // Abrir sistema de suporte
          console.log('Abrindo suporte...')
          break
          
        case 'stop-recording':
          // Parar gravação ativa
          console.log('Parando gravação...')
          emit('action', { action: 'stop-recording' })
          break
          
        default:
          console.log('Ação não reconhecida:', action.action)
      }
      
      // Remover notificação se a ação for destrutiva ou de resolução
      if (['retry-blockchain', 'stop-recording'].includes(action.action)) {
        removeNotification(notification.id)
      }
    }

    return {
      notifications,
      removeNotification,
      getNotificationClasses,
      getNotificationIcon,
      handleNotificationAction
    }
  }
}
</script>

<style scoped>
.notification-item {
  max-width: 100%;
  word-wrap: break-word;
  backdrop-filter: blur(8px);
}

/* Animações das notificações */
.notification-enter-active,
.notification-leave-active {
  transition: all 0.3s ease;
}

.notification-enter-from {
  opacity: 0;
  transform: translateX(100%) scale(0.95);
}

.notification-leave-to {
  opacity: 0;
  transform: translateX(100%) scale(0.95);
}

.notification-move {
  transition: transform 0.3s ease;
}

/* Efeito hover para notificações */
.notification-item:hover {
  transform: translateY(-1px);
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* Responsividade para mobile */
@media (max-width: 640px) {
  .notification-item {
    margin-left: 1rem;
    margin-right: 1rem;
    max-width: calc(100vw - 2rem);
  }
}

/* Tema escuro (se implementado futuramente) */
@media (prefers-color-scheme: dark) {
  .notification-item {
    backdrop-filter: blur(12px);
  }
}
</style>
