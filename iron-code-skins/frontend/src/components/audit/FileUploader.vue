<template>
  <div class="file-uploader bg-white rounded-lg shadow-lg p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-lg font-semibold text-gray-900">
        <svg class="inline-block w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
        </svg>
        Upload de Evidências
      </h3>
      
      <div v-if="uploadProgress > 0 && uploadProgress < 100" class="text-sm text-gray-600">
        {{ uploadProgress }}% completo
      </div>
    </div>

    <!-- Drag & Drop Area -->
    <div
      @drop="handleDrop"
      @dragover="handleDragOver"
      @dragenter="handleDragEnter"
      @dragleave="handleDragLeave"
      @click="triggerFileInput"
      :class="[
        'border-2 border-dashed rounded-lg p-8 text-center cursor-pointer transition-all duration-200',
        isDragging 
          ? 'border-blue-500 bg-blue-50' 
          : 'border-gray-300 hover:border-gray-400 hover:bg-gray-50'
      ]"
    >
      <input
        ref="fileInput"
        type="file"
        multiple
        accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.txt"
        @change="handleFileSelect"
        class="hidden"
      >
      
      <div v-if="!isUploading">
        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" stroke="currentColor" fill="none" viewBox="0 0 48 48">
          <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        
        <h4 class="text-lg font-medium text-gray-900 mb-2">
          Arrastar arquivos ou clique para selecionar
        </h4>
        
        <p class="text-sm text-gray-600 mb-4">
          Suporte para imagens, vídeos, áudios e documentos
        </p>
        
        <div class="text-xs text-gray-500">
          Tamanho máximo: {{ maxFileSize }}MB por arquivo
        </div>
      </div>
      
      <!-- Upload Progress -->
      <div v-else class="space-y-4">
        <div class="flex items-center justify-center">
          <svg class="animate-spin h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </div>
        
        <div>
          <div class="text-sm font-medium text-gray-900 mb-2">
            Fazendo upload...
          </div>
          
          <!-- Progress Bar -->
          <div class="w-full bg-gray-200 rounded-full h-2">
            <div 
              class="bg-blue-600 h-2 rounded-full transition-all duration-300"
              :style="{ width: uploadProgress + '%' }"
            ></div>
          </div>
          
          <div class="text-xs text-gray-500 mt-1">
            {{ Math.round(uploadProgress) }}% de {{ totalFiles }} arquivo(s)
          </div>
        </div>
      </div>
    </div>

    <!-- Selected Files List -->
    <div v-if="selectedFiles.length > 0 && !isUploading" class="mt-6">
      <h4 class="text-sm font-medium text-gray-900 mb-3">
        Arquivos Selecionados ({{ selectedFiles.length }})
      </h4>
      
      <div class="space-y-2 max-h-64 overflow-y-auto">
        <div
          v-for="(file, index) in selectedFiles"
          :key="file.id"
          class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
        >
          <div class="flex items-center space-x-3">
            <!-- File Type Icon -->
            <div class="flex-shrink-0">
              <svg 
                v-if="file.type.startsWith('image/')"
                class="w-6 h-6 text-green-500" 
                fill="currentColor" 
                viewBox="0 0 20 20"
              >
                <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"/>
              </svg>
              
              <svg 
                v-else-if="file.type.startsWith('video/')"
                class="w-6 h-6 text-purple-500" 
                fill="currentColor" 
                viewBox="0 0 20 20"
              >
                <path d="M2 6a2 2 0 012-2h6l2 2h6a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14 6a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2h8z"/>
              </svg>
              
              <svg 
                v-else-if="file.type.startsWith('audio/')"
                class="w-6 h-6 text-blue-500" 
                fill="currentColor" 
                viewBox="0 0 20 20"
              >
                <path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.707.707L4.586 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.586l3.707-3.707a1 1 0 011.09-.217zM15.657 6.343a1 1 0 011.414 0A9.972 9.972 0 0119 12a9.972 9.972 0 01-1.929 5.657 1 1 0 11-1.414-1.414A7.971 7.971 0 0017 12c0-2.21-.895-4.21-2.343-5.657a1 1 0 010-1.414z"/>
              </svg>
              
              <svg 
                v-else
                class="w-6 h-6 text-gray-500" 
                fill="currentColor" 
                viewBox="0 0 20 20"
              >
                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/>
              </svg>
            </div>
            
            <!-- File Info -->
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900 truncate">
                {{ file.name }}
              </p>
              <p class="text-xs text-gray-500">
                {{ formatFileSize(file.size) }} • {{ getFileTypeLabel(file.type) }}
              </p>
            </div>
          </div>
          
          <!-- Remove Button -->
          <button
            @click="removeFile(index)"
            class="text-red-400 hover:text-red-600 transition-colors"
            title="Remover arquivo"
          >
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"/>
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Metadata Form -->
    <div v-if="selectedFiles.length > 0 && !isUploading" class="mt-6 space-y-4">
      <h4 class="text-sm font-medium text-gray-900">
        Informações das Evidências
      </h4>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="form-label">Categoria</label>
          <select v-model="metadata.category" class="form-input">
            <option value="">Selecione...</option>
            <option value="transaction">Transação</option>
            <option value="communication">Comunicação</option>
            <option value="profile">Perfil</option>
            <option value="system">Sistema</option>
            <option value="other">Outro</option>
          </select>
        </div>
        
        <div>
          <label class="form-label">Prioridade</label>
          <select v-model="metadata.priority" class="form-input">
            <option value="low">Baixa</option>
            <option value="medium">Média</option>
            <option value="high">Alta</option>
            <option value="critical">Crítica</option>
          </select>
        </div>
      </div>
      
      <div>
        <label class="form-label">Descrição</label>
        <textarea
          v-model="metadata.description"
          rows="3"
          class="form-input"
          placeholder="Descreva o conteúdo das evidências..."
        ></textarea>
      </div>
      
      <div>
        <label class="form-label">Tags (separadas por vírgula)</label>
        <input
          type="text"
          v-model="metadata.tags"
          class="form-input"
          placeholder="ex: transação, suspeita, steam"
        >
      </div>
    </div>

    <!-- Actions -->
    <div v-if="selectedFiles.length > 0 && !isUploading" class="mt-6 flex items-center justify-end space-x-3">
      <button
        @click="clearFiles"
        class="btn-outline px-4 py-2"
      >
        Limpar Tudo
      </button>
      
      <button
        @click="startUpload"
        class="btn-primary px-6 py-2"
        :disabled="selectedFiles.length === 0"
      >
        Enviar {{ selectedFiles.length }} arquivo(s)
      </button>
    </div>

    <!-- Error Messages -->
    <div v-if="errors.length > 0" class="mt-4">
      <div class="bg-red-50 border border-red-200 rounded-md p-4">
        <div class="flex">
          <svg class="w-5 h-5 text-red-400 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/>
          </svg>
          <div>
            <h4 class="text-sm font-medium text-red-800 mb-1">
              Erros encontrados:
            </h4>
            <ul class="text-sm text-red-700 space-y-1">
              <li v-for="error in errors" :key="error">• {{ error }}</li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Success Messages -->
    <div v-if="successMessages.length > 0" class="mt-4">
      <div class="bg-green-50 border border-green-200 rounded-md p-4">
        <div class="flex">
          <svg class="w-5 h-5 text-green-400 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
          </svg>
          <div>
            <h4 class="text-sm font-medium text-green-800 mb-1">
              Uploads concluídos:
            </h4>
            <ul class="text-sm text-green-700 space-y-1">
              <li v-for="message in successMessages" :key="message">• {{ message }}</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue'
import { useEvidenceStore } from '@/stores/evidenceStore'
import evidenceService from '@/services/evidenceService'

export default {
  name: 'FileUploader',
  props: {
    transactionId: {
      type: String,
      required: true
    },
    maxFileSize: {
      type: Number,
      default: 100 // MB
    },
    allowedTypes: {
      type: Array,
      default: () => ['image/*', 'video/*', 'audio/*', 'application/pdf', 'text/*']
    }
  },
  emits: ['files-uploaded', 'upload-progress', 'error'],
  setup(props, { emit }) {
    const evidenceStore = useEvidenceStore()
    
    // State
    const selectedFiles = ref([])
    const isDragging = ref(false)
    const isUploading = ref(false)
    const uploadProgress = ref(0)
    const errors = ref([])
    const successMessages = ref([])
    const fileInput = ref(null)
    
    const metadata = ref({
      category: '',
      priority: 'medium',
      description: '',
      tags: ''
    })

    // Computed
    const totalFiles = computed(() => selectedFiles.value.length)

    // Methods
    function handleDragOver(e) {
      e.preventDefault()
    }

    function handleDragEnter(e) {
      e.preventDefault()
      isDragging.value = true
    }

    function handleDragLeave(e) {
      e.preventDefault()
      if (!e.currentTarget.contains(e.relatedTarget)) {
        isDragging.value = false
      }
    }

    function handleDrop(e) {
      e.preventDefault()
      isDragging.value = false
      
      const files = Array.from(e.dataTransfer.files)
      addFiles(files)
    }

    function triggerFileInput() {
      if (!isUploading.value) {
        fileInput.value?.click()
      }
    }

    function handleFileSelect(e) {
      const files = Array.from(e.target.files)
      addFiles(files)
      
      // Reset input
      e.target.value = ''
    }

    function addFiles(files) {
      errors.value = []
      
      const validFiles = files.filter(file => {
        const validation = validateFile(file)
        if (!validation.valid) {
          errors.value.push(`${file.name}: ${validation.error}`)
          return false
        }
        return true
      })

      validFiles.forEach(file => {
        // Add unique ID and preview URL if image
        const fileWithId = {
          ...file,
          id: generateFileId(),
          previewUrl: file.type.startsWith('image/') ? URL.createObjectURL(file) : null
        }
        
        selectedFiles.value.push(fileWithId)
      })
    }

    function validateFile(file) {
      // Check file size
      if (file.size > props.maxFileSize * 1024 * 1024) {
        return {
          valid: false,
          error: `Arquivo muito grande (máx: ${props.maxFileSize}MB)`
        }
      }

      // Check file type
      const isValidType = props.allowedTypes.some(type => {
        if (type.endsWith('*')) {
          return file.type.startsWith(type.slice(0, -1))
        }
        return file.type === type
      })

      if (!isValidType) {
        return {
          valid: false,
          error: 'Tipo de arquivo não suportado'
        }
      }

      return { valid: true }
    }

    function removeFile(index) {
      const file = selectedFiles.value[index]
      if (file.previewUrl) {
        URL.revokeObjectURL(file.previewUrl)
      }
      selectedFiles.value.splice(index, 1)
    }

    function clearFiles() {
      selectedFiles.value.forEach(file => {
        if (file.previewUrl) {
          URL.revokeObjectURL(file.previewUrl)
        }
      })
      selectedFiles.value = []
      errors.value = []
      successMessages.value = []
    }

    async function startUpload() {
      if (selectedFiles.value.length === 0) return

      isUploading.value = true
      uploadProgress.value = 0
      errors.value = []
      successMessages.value = []

      try {
        for (let i = 0; i < selectedFiles.value.length; i++) {
          const file = selectedFiles.value[i]
          
          try {
            const fileMetadata = {
              transaction_id: props.transactionId,
              ...metadata.value,
              tags: metadata.value.tags
                .split(',')
                .map(tag => tag.trim())
                .filter(tag => tag.length > 0),
              file_index: i,
              total_files: selectedFiles.value.length
            }

            await evidenceService.upload(file, fileMetadata, {
              onUploadProgress: (progressEvent) => {
                const fileProgress = Math.round(
                  (progressEvent.loaded * 100) / progressEvent.total
                )
                const totalProgress = ((i * 100) + fileProgress) / selectedFiles.value.length
                uploadProgress.value = Math.round(totalProgress)
                
                emit('upload-progress', {
                  fileIndex: i,
                  fileProgress,
                  totalProgress: uploadProgress.value
                })
              }
            })

            successMessages.value.push(`${file.name} enviado com sucesso`)
            
          } catch (error) {
            errors.value.push(`${file.name}: ${error.message}`)
            console.error(`Erro no upload de ${file.name}:`, error)
          }
        }

        if (successMessages.value.length > 0) {
          emit('files-uploaded', {
            successful: successMessages.value.length,
            total: selectedFiles.value.length
          })
        }

        // Clear files after successful upload
        if (errors.value.length === 0) {
          setTimeout(() => {
            clearFiles()
          }, 3000)
        }

      } catch (error) {
        errors.value.push(`Erro geral no upload: ${error.message}`)
        emit('error', error)
      } finally {
        isUploading.value = false
      }
    }

    function formatFileSize(bytes) {
      if (bytes === 0) return '0 Bytes'
      
      const k = 1024
      const sizes = ['Bytes', 'KB', 'MB', 'GB']
      const i = Math.floor(Math.log(bytes) / Math.log(k))
      
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
    }

    function getFileTypeLabel(mimeType) {
      if (mimeType.startsWith('image/')) return 'Imagem'
      if (mimeType.startsWith('video/')) return 'Vídeo'
      if (mimeType.startsWith('audio/')) return 'Áudio'
      if (mimeType.includes('pdf')) return 'PDF'
      if (mimeType.startsWith('text/')) return 'Texto'
      return 'Documento'
    }

    function generateFileId() {
      return Math.random().toString(36).substr(2, 9) + Date.now().toString(36)
    }

    return {
      // State
      selectedFiles,
      isDragging,
      isUploading,
      uploadProgress,
      errors,
      successMessages,
      fileInput,
      metadata,
      
      // Computed
      totalFiles,
      
      // Methods
      handleDragOver,
      handleDragEnter,
      handleDragLeave,
      handleDrop,
      triggerFileInput,
      handleFileSelect,
      removeFile,
      clearFiles,
      startUpload,
      formatFileSize,
      getFileTypeLabel
    }
  }
}
</script>

<style scoped>
/* Componente já utiliza classes Tailwind CSS */
</style>
