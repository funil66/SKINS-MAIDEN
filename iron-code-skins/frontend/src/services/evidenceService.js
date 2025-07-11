import axios from 'axios'

// Configuração específica para evidências com timeout maior para uploads
const evidenceClient = axios.create({
  baseURL: '/api',
  timeout: 120000, // 2 minutos para uploads grandes
  headers: {
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  }
})

// Interceptador de request
evidenceClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => Promise.reject(error)
)

// Interceptador de response
evidenceClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 413) {
      error.message = 'Arquivo muito grande. Tamanho máximo permitido: 100MB'
    } else if (error.response?.status === 415) {
      error.message = 'Tipo de arquivo não suportado'
    } else if (error.response?.status === 422) {
      error.message = error.response.data?.message || 'Dados inválidos'
    } else {
      error.message = error.response?.data?.message || 
                     error.message || 
                     'Erro no processamento'
    }
    return Promise.reject(error)
  }
)

export default {
  /**
   * Upload de evidência com progress tracking
   */
  async upload(file, metadata = {}, options = {}) {
    const formData = new FormData()
    formData.append('file', file)
    
    // Adicionar metadata como campos individuais
    Object.keys(metadata).forEach(key => {
      if (metadata[key] !== null && metadata[key] !== undefined) {
        formData.append(key, typeof metadata[key] === 'object' 
          ? JSON.stringify(metadata[key]) 
          : metadata[key]
        )
      }
    })

    return await evidenceClient.post('/evidences', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
      onUploadProgress: options.onUploadProgress,
      ...options
    })
  },

  /**
   * Upload múltiplo de evidências
   */
  async uploadMultiple(files, metadata = {}, options = {}) {
    const formData = new FormData()
    
    files.forEach((file, index) => {
      formData.append(`files[${index}]`, file)
    })
    
    Object.keys(metadata).forEach(key => {
      formData.append(key, typeof metadata[key] === 'object' 
        ? JSON.stringify(metadata[key]) 
        : metadata[key]
      )
    })

    return await evidenceClient.post('/evidences/batch', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
      onUploadProgress: options.onUploadProgress,
      timeout: 300000, // 5 minutos para uploads múltiplos
      ...options
    })
  },

  /**
   * Upload de chunk para arquivos grandes
   */
  async uploadChunk(chunk, chunkIndex, totalChunks, fileId, options = {}) {
    const formData = new FormData()
    formData.append('chunk', chunk)
    formData.append('chunk_index', chunkIndex)
    formData.append('total_chunks', totalChunks)
    formData.append('file_id', fileId)

    return await evidenceClient.post('/evidences/chunk', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
      onUploadProgress: options.onUploadProgress,
      ...options
    })
  },

  /**
   * Finalizar upload chunked
   */
  async finalizeChunkedUpload(fileId, metadata = {}) {
    return await evidenceClient.post(`/evidences/chunk/${fileId}/finalize`, metadata)
  },

  /**
   * Download de evidência
   */
  async download(evidenceId, options = {}) {
    return await evidenceClient.get(`/evidences/${evidenceId}/download`, {
      responseType: 'blob',
      onDownloadProgress: options.onDownloadProgress,
      ...options
    })
  },

  /**
   * Preview de evidência (thumbnail/preview)
   */
  async getPreview(evidenceId, size = 'medium') {
    return await evidenceClient.get(`/evidences/${evidenceId}/preview`, {
      params: { size },
      responseType: 'blob'
    })
  },

  /**
   * Buscar evidências com filtros avançados
   */
  async search(filters = {}, pagination = {}) {
    const params = {
      ...filters,
      page: pagination.page || 1,
      per_page: pagination.perPage || 20,
      sort_by: pagination.sortBy || 'created_at',
      sort_order: pagination.sortOrder || 'desc'
    }

    return await evidenceClient.get('/evidences/search', { params })
  },

  /**
   * Validar arquivo antes do upload
   */
  async validateFile(file) {
    const formData = new FormData()
    formData.append('file', file)
    formData.append('validate_only', 'true')

    return await evidenceClient.post('/evidences/validate', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      }
    })
  },

  /**
   * Gerar hash local do arquivo
   */
  async generateHash(file) {
    return new Promise((resolve, reject) => {
      const reader = new FileReader()
      
      reader.onload = async (event) => {
        try {
          // Usar Web Crypto API para SHA-256
          const hashBuffer = await crypto.subtle.digest('SHA-256', event.target.result)
          const hashArray = Array.from(new Uint8Array(hashBuffer))
          const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('')
          resolve(hashHex)
        } catch (error) {
          reject(error)
        }
      }
      
      reader.onerror = () => reject(new Error('Erro ao ler arquivo'))
      reader.readAsArrayBuffer(file)
    })
  },

  /**
   * Verificar tipos de arquivo suportados
   */
  async getSupportedTypes() {
    return await evidenceClient.get('/evidences/supported-types')
  },

  /**
   * Obter configurações de upload
   */
  async getUploadConfig() {
    return await evidenceClient.get('/evidences/upload-config')
  },

  /**
   * Comprimir imagem antes do upload
   */
  async compressImage(file, quality = 0.8, maxWidth = 1920, maxHeight = 1080) {
    return new Promise((resolve) => {
      const canvas = document.createElement('canvas')
      const ctx = canvas.getContext('2d')
      const img = new Image()

      img.onload = () => {
        // Calcular dimensões mantendo aspect ratio
        let { width, height } = img
        
        if (width > maxWidth) {
          height = (height * maxWidth) / width
          width = maxWidth
        }
        
        if (height > maxHeight) {
          width = (width * maxHeight) / height
          height = maxHeight
        }

        canvas.width = width
        canvas.height = height

        // Desenhar imagem redimensionada
        ctx.drawImage(img, 0, 0, width, height)

        // Converter para blob
        canvas.toBlob(resolve, file.type, quality)
      }

      img.src = URL.createObjectURL(file)
    })
  },

  /**
   * Obter informações EXIF de imagem
   */
  async getImageExif(file) {
    // Implementação básica - em produção usar biblioteca como exif-js
    return new Promise((resolve) => {
      const reader = new FileReader()
      
      reader.onload = (event) => {
        const arrayBuffer = event.target.result
        const dataView = new DataView(arrayBuffer)
        
        // Verificar se é JPEG e tem EXIF
        if (dataView.getUint16(0) === 0xFFD8) {
          // Básico - apenas timestamp
          resolve({
            timestamp: new Date().toISOString(),
            fileSize: file.size,
            fileType: file.type
          })
        } else {
          resolve({
            timestamp: new Date().toISOString(),
            fileSize: file.size,
            fileType: file.type
          })
        }
      }
      
      reader.readAsArrayBuffer(file.slice(0, 1024)) // Ler apenas os primeiros 1KB
    })
  },

  /**
   * Extrair frame de vídeo para thumbnail
   */
  async extractVideoFrame(file, timeSeconds = 1) {
    return new Promise((resolve, reject) => {
      const video = document.createElement('video')
      const canvas = document.createElement('canvas')
      const ctx = canvas.getContext('2d')

      video.addEventListener('loadedmetadata', () => {
        canvas.width = video.videoWidth
        canvas.height = video.videoHeight
        video.currentTime = Math.min(timeSeconds, video.duration)
      })

      video.addEventListener('seeked', () => {
        ctx.drawImage(video, 0, 0)
        canvas.toBlob(resolve, 'image/jpeg', 0.8)
      })

      video.addEventListener('error', reject)
      video.src = URL.createObjectURL(file)
    })
  }
}
