// plugins/globals.js
export default {
  install(app) {
    // Formatação de moeda
    app.config.globalProperties.$formatCurrency = (value) => {
      if (typeof value !== 'number') return value
      
      return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      }).format(value)
    }

    // Formatação de data
    app.config.globalProperties.$formatDate = (dateString) => {
      if (!dateString) return ''
      
      const date = new Date(dateString)
      
      if (isNaN(date.getTime())) return ''
      
      return new Intl.DateTimeFormat('pt-BR', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
      }).format(date)
    }

    // Formatação de data relativa
    app.config.globalProperties.$formatRelativeDate = (dateString) => {
      if (!dateString) return ''
      
      const date = new Date(dateString)
      const now = new Date()
      const diffInMs = now - date
      const diffInMinutes = Math.floor(diffInMs / (1000 * 60))
      const diffInHours = Math.floor(diffInMinutes / 60)
      const diffInDays = Math.floor(diffInHours / 24)

      if (diffInMinutes < 1) {
        return 'Agora'
      } else if (diffInMinutes < 60) {
        return `${diffInMinutes} min atrás`
      } else if (diffInHours < 24) {
        return `${diffInHours}h atrás`
      } else if (diffInDays < 7) {
        return `${diffInDays} dias atrás`
      } else {
        return app.config.globalProperties.$formatDate(dateString)
      }
    }

    // Formatação de tamanho de arquivo
    app.config.globalProperties.$formatFileSize = (bytes) => {
      if (typeof bytes !== 'number' || bytes === 0) return '0 B'
      
      const k = 1024
      const sizes = ['B', 'KB', 'MB', 'GB', 'TB']
      const i = Math.floor(Math.log(bytes) / Math.log(k))
      
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
    }

    // Formatação de hash (truncar com reticências)
    app.config.globalProperties.$formatHash = (hash, length = 8) => {
      if (!hash || typeof hash !== 'string') return ''
      
      if (hash.length <= length * 2) return hash
      
      return `${hash.slice(0, length)}...${hash.slice(-length)}`
    }

    // Formatação de duração (em segundos para texto legível)
    app.config.globalProperties.$formatDuration = (seconds) => {
      if (typeof seconds !== 'number' || seconds < 0) return '0s'
      
      const hours = Math.floor(seconds / 3600)
      const minutes = Math.floor((seconds % 3600) / 60)
      const secs = seconds % 60
      
      const parts = []
      
      if (hours > 0) {
        parts.push(`${hours}h`)
      }
      
      if (minutes > 0) {
        parts.push(`${minutes}m`)
      }
      
      if (secs > 0 || parts.length === 0) {
        parts.push(`${secs}s`)
      }
      
      return parts.join(' ')
    }

    // Formatação de status com cores
    app.config.globalProperties.$formatStatus = (status) => {
      const statusMap = {
        'completed': { text: 'Concluído', class: 'text-green-600 bg-green-100' },
        'pending': { text: 'Pendente', class: 'text-yellow-600 bg-yellow-100' },
        'failed': { text: 'Falhou', class: 'text-red-600 bg-red-100' },
        'processing': { text: 'Processando', class: 'text-blue-600 bg-blue-100' },
        'cancelled': { text: 'Cancelado', class: 'text-gray-600 bg-gray-100' },
        'verified': { text: 'Verificado', class: 'text-green-600 bg-green-100' },
        'unverified': { text: 'Não Verificado', class: 'text-red-600 bg-red-100' }
      }
      
      return statusMap[status] || { text: status, class: 'text-gray-600 bg-gray-100' }
    }

    // Formatação de tipo de arquivo com ícone
    app.config.globalProperties.$getFileTypeIcon = (mimeType, fileType) => {
      if (fileType === 'screenshot' || mimeType?.startsWith('image/')) {
        return {
          icon: 'photo',
          class: 'text-green-500',
          label: 'Imagem'
        }
      }
      
      if (fileType === 'video' || mimeType?.startsWith('video/')) {
        return {
          icon: 'video-camera',
          class: 'text-purple-500',
          label: 'Vídeo'
        }
      }
      
      if (fileType === 'audio' || mimeType?.startsWith('audio/')) {
        return {
          icon: 'volume-up',
          class: 'text-blue-500',
          label: 'Áudio'
        }
      }
      
      if (mimeType?.includes('pdf')) {
        return {
          icon: 'document-text',
          class: 'text-red-500',
          label: 'PDF'
        }
      }
      
      if (mimeType?.includes('word') || mimeType?.includes('document')) {
        return {
          icon: 'document-text',
          class: 'text-blue-500',
          label: 'Documento'
        }
      }
      
      if (mimeType?.includes('excel') || mimeType?.includes('spreadsheet')) {
        return {
          icon: 'table',
          class: 'text-green-500',
          label: 'Planilha'
        }
      }
      
      return {
        icon: 'document',
        class: 'text-gray-500',
        label: 'Arquivo'
      }
    }

    // Validação de transação ID
    app.config.globalProperties.$isValidTransactionId = (id) => {
      if (!id || typeof id !== 'string') return false
      
      // Verifica se tem formato válido (pelo menos 8 caracteres alfanuméricos)
      return /^[a-zA-Z0-9_-]{8,}$/.test(id)
    }

    // Copiar texto para clipboard
    app.config.globalProperties.$copyToClipboard = async (text) => {
      try {
        if (navigator.clipboard && window.isSecureContext) {
          await navigator.clipboard.writeText(text)
          return true
        } else {
          // Fallback para navegadores mais antigos
          const textArea = document.createElement('textarea')
          textArea.value = text
          textArea.style.position = 'fixed'
          textArea.style.left = '-999999px'
          textArea.style.top = '-999999px'
          document.body.appendChild(textArea)
          textArea.focus()
          textArea.select()
          
          const result = document.execCommand('copy')
          document.body.removeChild(textArea)
          
          return result
        }
      } catch (error) {
        console.error('Erro ao copiar texto:', error)
        return false
      }
    }

    // Baixar arquivo
    app.config.globalProperties.$downloadFile = (url, filename) => {
      const link = document.createElement('a')
      link.href = url
      link.download = filename || 'download'
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
    }

    // Gerar cor baseada em string (para avatars, etc)
    app.config.globalProperties.$generateColorFromString = (str) => {
      let hash = 0
      for (let i = 0; i < str.length; i++) {
        hash = str.charCodeAt(i) + ((hash << 5) - hash)
      }
      
      const hue = hash % 360
      return `hsl(${hue}, 70%, 50%)`
    }

    // Debounce para pesquisas
    app.config.globalProperties.$debounce = (func, wait) => {
      let timeout
      return function executedFunction(...args) {
        const later = () => {
          clearTimeout(timeout)
          func(...args)
        }
        clearTimeout(timeout)
        timeout = setTimeout(later, wait)
      }
    }

    // Verificar se dispositivo é mobile
    app.config.globalProperties.$isMobile = () => {
      return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)
    }

    // Gerar ID único
    app.config.globalProperties.$generateId = () => {
      return Math.random().toString(36).substr(2, 9) + Date.now().toString(36)
    }
  }
}
