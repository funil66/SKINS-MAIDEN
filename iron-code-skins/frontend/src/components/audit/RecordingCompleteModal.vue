<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
      <!-- Header -->
      <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-gray-900">
          Gravação Concluída
        </h3>
        <button
          @click="$emit('close')"
          class="text-gray-400 hover:text-gray-600 transition-colors"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Recording Info -->
      <div class="mb-6">
        <div class="bg-gray-50 rounded-lg p-4">
          <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
              <span class="font-medium text-gray-700">Duração:</span>
              <span class="ml-2">{{ formatDuration(recording?.duration || 0) }}</span>
            </div>
            <div>
              <span class="font-medium text-gray-700">Tamanho:</span>
              <span class="ml-2">{{ formatFileSize(recording?.size || 0) }}</span>
            </div>
            <div>
              <span class="font-medium text-gray-700">Tipo:</span>
              <span class="ml-2 capitalize">{{ recording?.type || 'N/A' }}</span>
            </div>
            <div>
              <span class="font-medium text-gray-700">Formato:</span>
              <span class="ml-2">{{ recording?.mimeType || 'N/A' }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Preview -->
      <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Preview da Gravação
        </label>
        <div class="bg-gray-900 rounded-lg overflow-hidden aspect-video">
          <video
            v-if="recording?.blob"
            :src="previewUrl"
            controls
            class="w-full h-full object-contain"
          ></video>
        </div>
      </div>

      <!-- Metadata Form -->
      <form @submit.prevent="handleSave">
        <div class="space-y-4 mb-6">
          <!-- Title -->
          <div>
            <label class="form-label">
              Título da Evidência *
            </label>
            <input
              type="text"
              v-model="metadata.title"
              required
              class="form-input"
              placeholder="Ex: Evidência de transação suspeita"
            >
          </div>

          <!-- Description -->
          <div>
            <label class="form-label">
              Descrição
            </label>
            <textarea
              v-model="metadata.description"
              rows="3"
              class="form-input"
              placeholder="Descreva o que foi capturado nesta evidência..."
            ></textarea>
          </div>

          <!-- Tags -->
          <div>
            <label class="form-label">
              Tags (separadas por vírgula)
            </label>
            <input
              type="text"
              v-model="tagsInput"
              class="form-input"
              placeholder="transação, suspeita, steam, skin"
            >
          </div>

          <!-- Category -->
          <div>
            <label class="form-label">
              Categoria
            </label>
            <select v-model="metadata.category" class="form-input">
              <option value="">Selecione uma categoria</option>
              <option value="transaction">Transação</option>
              <option value="communication">Comunicação</option>
              <option value="profile">Perfil do Usuário</option>
              <option value="system">Sistema</option>
              <option value="other">Outro</option>
            </select>
          </div>

          <!-- Priority -->
          <div>
            <label class="form-label">
              Prioridade
            </label>
            <div class="flex space-x-4">
              <label class="flex items-center">
                <input
                  type="radio"
                  v-model="metadata.priority"
                  value="low"
                  class="text-blue-600 focus:ring-blue-500"
                >
                <span class="ml-2 text-sm">Baixa</span>
              </label>
              <label class="flex items-center">
                <input
                  type="radio"
                  v-model="metadata.priority"
                  value="medium"
                  class="text-blue-600 focus:ring-blue-500"
                >
                <span class="ml-2 text-sm">Média</span>
              </label>
              <label class="flex items-center">
                <input
                  type="radio"
                  v-model="metadata.priority"
                  value="high"
                  class="text-blue-600 focus:ring-blue-500"
                >
                <span class="ml-2 text-sm">Alta</span>
              </label>
              <label class="flex items-center">
                <input
                  type="radio"
                  v-model="metadata.priority"
                  value="critical"
                  class="text-blue-600 focus:ring-blue-500"
                >
                <span class="ml-2 text-sm">Crítica</span>
              </label>
            </div>
          </div>

          <!-- Additional Options -->
          <div class="border-t pt-4">
            <div class="space-y-3">
              <label class="flex items-center">
                <input
                  type="checkbox"
                  v-model="metadata.encrypt"
                  class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                >
                <span class="ml-2 text-sm text-gray-700">
                  Criptografar evidência (recomendado)
                </span>
              </label>
              
              <label class="flex items-center">
                <input
                  type="checkbox"
                  v-model="metadata.blockchain_register"
                  class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                >
                <span class="ml-2 text-sm text-gray-700">
                  Registrar hash no blockchain imediatamente
                </span>
              </label>
              
              <label class="flex items-center">
                <input
                  type="checkbox"
                  v-model="metadata.notify_stakeholders"
                  class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                >
                <span class="ml-2 text-sm text-gray-700">
                  Notificar partes interessadas
                </span>
              </label>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end space-x-3">
          <button
            type="button"
            @click="$emit('discard')"
            class="btn-outline px-6 py-2"
          >
            Descartar
          </button>
          
          <button
            type="button"
            @click="saveDraft"
            class="btn-secondary px-6 py-2"
            :disabled="saving"
          >
            Salvar Rascunho
          </button>
          
          <button
            type="submit"
            class="btn-primary px-6 py-2"
            :disabled="saving || !metadata.title.trim()"
          >
            <svg v-if="saving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ saving ? 'Salvando...' : 'Salvar Evidência' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue'

export default {
  name: 'RecordingCompleteModal',
  props: {
    recording: {
      type: Object,
      required: true
    }
  },
  emits: ['save', 'discard', 'close'],
  setup(props, { emit }) {
    // State
    const saving = ref(false)
    const previewUrl = ref('')
    const tagsInput = ref('')
    
    const metadata = ref({
      title: '',
      description: '',
      tags: [],
      category: '',
      priority: 'medium',
      encrypt: true,
      blockchain_register: true,
      notify_stakeholders: false
    })

    // Computed
    const processedMetadata = computed(() => ({
      ...metadata.value,
      tags: tagsInput.value
        .split(',')
        .map(tag => tag.trim())
        .filter(tag => tag.length > 0),
      timestamp: new Date().toISOString(),
      user_agent: navigator.userAgent,
      screen_resolution: `${screen.width}x${screen.height}`,
      timezone: Intl.DateTimeFormat().resolvedOptions().timeZone
    }))

    // Methods
    function formatDuration(seconds) {
      const mins = Math.floor(seconds / 60)
      const secs = seconds % 60
      return `${mins}:${secs.toString().padStart(2, '0')}`
    }

    function formatFileSize(bytes) {
      if (bytes === 0) return '0 Bytes'
      
      const k = 1024
      const sizes = ['Bytes', 'KB', 'MB', 'GB']
      const i = Math.floor(Math.log(bytes) / Math.log(k))
      
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
    }

    async function handleSave() {
      if (!metadata.value.title.trim()) return
      
      saving.value = true
      
      try {
        await emit('save', processedMetadata.value)
      } finally {
        saving.value = false
      }
    }

    async function saveDraft() {
      saving.value = true
      
      try {
        const draftMetadata = {
          ...processedMetadata.value,
          status: 'draft'
        }
        await emit('save', draftMetadata)
      } finally {
        saving.value = false
      }
    }

    // Generate automatic title based on recording type and timestamp
    function generateAutoTitle() {
      const now = new Date()
      const typeMap = {
        screen: 'Gravação de Tela',
        camera: 'Gravação de Câmera',
        audio: 'Gravação de Áudio'
      }
      
      const type = typeMap[props.recording?.type] || 'Evidência'
      const timestamp = now.toLocaleString('pt-BR')
      
      return `${type} - ${timestamp}`
    }

    // Create preview URL
    onMounted(() => {
      if (props.recording?.blob) {
        previewUrl.value = URL.createObjectURL(props.recording.blob)
        metadata.value.title = generateAutoTitle()
      }
    })

    // Cleanup preview URL
    onUnmounted(() => {
      if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value)
      }
    })

    return {
      // State
      saving,
      previewUrl,
      tagsInput,
      metadata,
      
      // Computed
      processedMetadata,
      
      // Methods
      formatDuration,
      formatFileSize,
      handleSave,
      saveDraft
    }
  }
}
</script>

<style scoped>
/* Modal já utiliza classes Tailwind CSS */
</style>
