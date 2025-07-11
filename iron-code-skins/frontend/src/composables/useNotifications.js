// composables/useNotifications.js
import { ref, reactive } from 'vue'

// Estado global para notificações
const notifications = ref([])
let notificationId = 0

export const useNotifications = () => {
  // Adicionar nova notificação
  const addNotification = (notification) => {
    const id = ++notificationId
    
    const newNotification = {
      id,
      type: notification.type || 'info', // success, error, warning, info
      title: notification.title || '',
      message: notification.message || '',
      duration: notification.duration || 5000, // ms
      persistent: notification.persistent || false,
      actions: notification.actions || [],
      createdAt: new Date().toISOString()
    }
    
    notifications.value.push(newNotification)
    
    // Auto-remover se não for persistente
    if (!newNotification.persistent && newNotification.duration > 0) {
      setTimeout(() => {
        removeNotification(id)
      }, newNotification.duration)
    }
    
    return id
  }

  // Remover notificação
  const removeNotification = (id) => {
    const index = notifications.value.findIndex(n => n.id === id)
    if (index !== -1) {
      notifications.value.splice(index, 1)
    }
  }

  // Limpar todas as notificações
  const clearNotifications = () => {
    notifications.value = []
  }

  // Métodos de conveniência para diferentes tipos
  const success = (title, message, options = {}) => {
    return addNotification({
      type: 'success',
      title,
      message,
      ...options
    })
  }

  const error = (title, message, options = {}) => {
    return addNotification({
      type: 'error',
      title,
      message,
      persistent: true, // Erros são persistentes por padrão
      ...options
    })
  }

  const warning = (title, message, options = {}) => {
    return addNotification({
      type: 'warning',
      title,
      message,
      duration: 7000, // Warnings ficam mais tempo
      ...options
    })
  }

  const info = (title, message, options = {}) => {
    return addNotification({
      type: 'info',
      title,
      message,
      ...options
    })
  }

  // Notificações específicas do sistema de auditoria
  const evidenceUploaded = (filename) => {
    return success(
      'Evidência Enviada',
      `Arquivo "${filename}" foi enviado e processado com sucesso.`
    )
  }

  const evidenceVerified = (filename) => {
    return success(
      'Evidência Verificada',
      `A integridade do arquivo "${filename}" foi confirmada no blockchain.`
    )
  }

  const blockchainError = (message) => {
    return error(
      'Erro no Blockchain',
      message || 'Falha na comunicação com a rede blockchain.',
      {
        actions: [
          {
            label: 'Tentar Novamente',
            action: 'retry-blockchain'
          },
          {
            label: 'Suporte',
            action: 'contact-support'
          }
        ]
      }
    )
  }

  const uploadProgress = (filename, progress) => {
    // Para notificações de progresso, removemos a anterior e adicionamos nova
    const existingIndex = notifications.value.findIndex(n => 
      n.title === 'Upload em Progresso' && n.message.includes(filename)
    )
    
    if (existingIndex !== -1) {
      notifications.value.splice(existingIndex, 1)
    }
    
    if (progress < 100) {
      return addNotification({
        type: 'info',
        title: 'Upload em Progresso',
        message: `Enviando "${filename}" - ${progress}%`,
        persistent: true,
        showProgress: true,
        progress
      })
    }
  }

  const recordingStarted = (type) => {
    const typeLabels = {
      screen: 'tela',
      camera: 'câmera',
      audio: 'áudio'
    }
    
    return info(
      'Gravação Iniciada',
      `Gravação de ${typeLabels[type] || type} em andamento.`,
      {
        persistent: true,
        actions: [
          {
            label: 'Parar Gravação',
            action: 'stop-recording',
            type: 'danger'
          }
        ]
      }
    )
  }

  const recordingStopped = (duration, filename) => {
    return success(
      'Gravação Finalizada',
      `Gravação de ${duration} salva como "${filename}".`
    )
  }

  // Helper para mapear tipos de notificação para classes CSS
  const getNotificationClasses = (type) => {
    const baseClasses = 'rounded-lg shadow-lg border p-4 mb-3 transition-all duration-300'
    
    const typeClasses = {
      success: 'bg-green-50 border-green-200 text-green-800',
      error: 'bg-red-50 border-red-200 text-red-800',
      warning: 'bg-yellow-50 border-yellow-200 text-yellow-800',
      info: 'bg-blue-50 border-blue-200 text-blue-800'
    }
    
    return `${baseClasses} ${typeClasses[type] || typeClasses.info}`
  }

  // Helper para ícones de notificação
  const getNotificationIcon = (type) => {
    const icons = {
      success: `
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
        </svg>
      `,
      error: `
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"/>
        </svg>
      `,
      warning: `
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"/>
        </svg>
      `,
      info: `
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"/>
        </svg>
      `
    }
    
    return icons[type] || icons.info
  }

  return {
    // Estado
    notifications: notifications.value,
    
    // Métodos principais
    addNotification,
    removeNotification,
    clearNotifications,
    
    // Métodos de conveniência
    success,
    error,
    warning,
    info,
    
    // Métodos específicos do domínio
    evidenceUploaded,
    evidenceVerified,
    blockchainError,
    uploadProgress,
    recordingStarted,
    recordingStopped,
    
    // Helpers
    getNotificationClasses,
    getNotificationIcon
  }
}
