<template>
  <div class="evidence-recorder bg-white rounded-lg shadow-lg p-6 max-w-4xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-2xl font-bold text-gray-900">
        <svg class="inline-block w-6 h-6 mr-2 text-red-600" fill="currentColor" viewBox="0 0 20 20">
          <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"/>
        </svg>
        Gravador de Evidências
      </h2>
      
      <div class="flex items-center space-x-2">
        <!-- Status indicator -->
        <div class="flex items-center">
          <div 
            :class="[
              'w-3 h-3 rounded-full mr-2',
              recordingStatus === 'recording' ? 'bg-red-500 animate-pulse' : 
              recordingStatus === 'paused' ? 'bg-yellow-500' : 'bg-gray-400'
            ]"
          ></div>
          <span class="text-sm font-medium text-gray-600">
            {{ statusText }}
          </span>
        </div>
        
        <!-- Timer -->
        <div class="bg-gray-100 px-3 py-1 rounded-md">
          <span class="font-mono text-lg font-semibold">{{ formattedDuration }}</span>
        </div>
      </div>
    </div>

    <!-- Recording Type Selection -->
    <div class="mb-6" v-if="!isRecording">
      <label class="block text-sm font-medium text-gray-700 mb-3">
        Tipo de Gravação
      </label>
      <div class="grid grid-cols-3 gap-4">
        <button
          v-for="type in recordingTypes"
          :key="type.value"
          @click="selectedType = type.value"
          :class="[
            'p-4 border-2 rounded-lg text-center transition-all duration-200',
            selectedType === type.value
              ? 'border-blue-500 bg-blue-50 text-blue-700'
              : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50'
          ]"
        >
          <component :is="type.icon" class="w-8 h-8 mx-auto mb-2" />
          <div class="font-medium">{{ type.label }}</div>
          <div class="text-xs text-gray-500 mt-1">{{ type.description }}</div>
        </button>
      </div>
    </div>

    <!-- Recording Options -->
    <div class="mb-6" v-if="!isRecording">
      <label class="block text-sm font-medium text-gray-700 mb-3">
        Opções de Gravação
      </label>
      <div class="space-y-3">
        <label class="flex items-center">
          <input 
            type="checkbox" 
            v-model="includeAudio"
            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
          >
          <span class="ml-2 text-sm text-gray-700">Incluir áudio</span>
        </label>
        
        <div v-if="selectedType === 'screen'">
          <label class="flex items-center">
            <input 
              type="checkbox" 
              v-model="includeSystemAudio"
              class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
            >
            <span class="ml-2 text-sm text-gray-700">Incluir áudio do sistema</span>
          </label>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm text-gray-700 mb-1">Qualidade do Vídeo</label>
            <select 
              v-model="videoQuality"
              class="w-full rounded-md border-gray-300 text-sm"
            >
              <option value="low">Baixa (720p)</option>
              <option value="medium">Média (1080p)</option>
              <option value="high">Alta (1440p)</option>
              <option value="ultra">Ultra (4K)</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm text-gray-700 mb-1">Frame Rate</label>
            <select 
              v-model="frameRate"
              class="w-full rounded-md border-gray-300 text-sm"
            >
              <option value="15">15 FPS</option>
              <option value="30">30 FPS</option>
              <option value="60">60 FPS</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Preview Area -->
    <div class="mb-6">
      <div class="bg-gray-900 rounded-lg overflow-hidden aspect-video relative">
        <video
          ref="previewVideo"
          class="w-full h-full object-contain"
          :class="{ 'opacity-0': !mediaStream }"
          autoplay
          muted
          playsinline
        ></video>
        
        <!-- Overlay when not recording -->
        <div 
          v-if="!mediaStream"
          class="absolute inset-0 flex items-center justify-center text-gray-400"
        >
          <div class="text-center">
            <svg class="w-16 h-16 mx-auto mb-4" fill="currentColor" viewBox="0 0 20 20">
              <path d="M2 6a2 2 0 012-2h6l2 2h6a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14 6a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2h8z"/>
            </svg>
            <p class="text-lg">Preview aparecerá aqui</p>
            <p class="text-sm">Clique em "Iniciar" para começar a gravação</p>
          </div>
        </div>

        <!-- Recording overlay -->
        <div 
          v-if="isRecording"
          class="absolute top-4 left-4 flex items-center bg-red-600 text-white px-3 py-1 rounded-md"
        >
          <div class="w-2 h-2 bg-white rounded-full mr-2 animate-pulse"></div>
          <span class="text-sm font-medium">GRAVANDO</span>
        </div>

        <!-- Paused overlay -->
        <div 
          v-if="isPaused"
          class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center"
        >
          <div class="text-white text-center">
            <svg class="w-12 h-12 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20">
              <path d="M6 4H8V16H6zM12 4H14V16H12z"/>
            </svg>
            <p class="text-lg font-medium">Pausado</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Controls -->
    <div class="flex items-center justify-center space-x-4">
      <!-- Initialize/Start Button -->
      <button
        v-if="!mediaStream && !isRecording"
        @click="initializeAndStart"
        :disabled="loading"
        class="btn-primary px-6 py-3 text-lg"
      >
        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
          <path d="M8 5v10l7-5z"/>
        </svg>
        {{ loading ? 'Inicializando...' : 'Iniciar Gravação' }}
      </button>

      <!-- Recording Controls -->
      <template v-if="mediaStream">
        <!-- Start -->
        <button
          v-if="!isRecording"
          @click="startRecording"
          class="btn-primary px-6 py-3"
        >
          <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path d="M8 5v10l7-5z"/>
          </svg>
          Iniciar
        </button>

        <!-- Pause -->
        <button
          v-if="isRecording && !isPaused"
          @click="pauseRecording"
          class="btn-warning px-6 py-3"
        >
          <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path d="M6 4H8V16H6zM12 4H14V16H12z"/>
          </svg>
          Pausar
        </button>

        <!-- Resume -->
        <button
          v-if="isRecording && isPaused"
          @click="resumeRecording"
          class="btn-success px-6 py-3"
        >
          <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path d="M8 5v10l7-5z"/>
          </svg>
          Continuar
        </button>

        <!-- Stop -->
        <button
          v-if="isRecording"
          @click="stopRecording"
          class="btn-danger px-6 py-3"
        >
          <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path d="M4 4h12v12H4z"/>
          </svg>
          Parar
        </button>

        <!-- Screenshot -->
        <button
          v-if="mediaStream && selectedType === 'screen'"
          @click="captureScreenshot"
          class="btn-outline px-4 py-3"
          title="Capturar Screenshot"
        >
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm0 2h12v8H4V6z"/>
          </svg>
        </button>

        <!-- Cancel -->
        <button
          v-if="!isRecording"
          @click="cancelRecording"
          class="btn-outline px-4 py-3"
        >
          Cancelar
        </button>
      </template>
    </div>

    <!-- Error Display -->
    <div v-if="error" class="mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md">
      <div class="flex">
        <svg class="w-5 h-5 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/>
        </svg>
        <div>
          <p class="font-medium">Erro na gravação:</p>
          <p class="text-sm">{{ error }}</p>
        </div>
      </div>
    </div>

    <!-- Recording Complete Modal -->
    <RecordingCompleteModal
      v-if="showCompleteModal"
      :recording="currentRecording"
      @save="handleSaveRecording"
      @discard="handleDiscardRecording"
      @close="showCompleteModal = false"
    />
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useEvidenceStore } from '@/stores/evidenceStore'
import RecordingCompleteModal from './RecordingCompleteModal.vue'

export default {
  name: 'EvidenceRecorder',
  components: {
    RecordingCompleteModal
  },
  props: {
    transactionId: {
      type: String,
      required: true
    }
  },
  emits: ['evidence-created', 'error'],
  setup(props, { emit }) {
    const evidenceStore = useEvidenceStore()
    
    // Local state
    const selectedType = ref('screen')
    const includeAudio = ref(true)
    const includeSystemAudio = ref(false)
    const videoQuality = ref('medium')
    const frameRate = ref('30')
    const loading = ref(false)
    const showCompleteModal = ref(false)
    
    // Refs
    const previewVideo = ref(null)
    
    // Recording types configuration
    const recordingTypes = [
      {
        value: 'screen',
        label: 'Tela',
        description: 'Gravar a tela inteira',
        icon: 'svg' // Screen icon component
      },
      {
        value: 'camera',
        label: 'Câmera',
        description: 'Gravar pela webcam',
        icon: 'svg' // Camera icon component
      },
      {
        value: 'audio',
        label: 'Áudio',
        description: 'Gravar apenas áudio',
        icon: 'svg' // Microphone icon component
      }
    ]

    // Computed
    const mediaStream = computed(() => evidenceStore.mediaStream)
    const isRecording = computed(() => evidenceStore.isRecording)
    const isPaused = computed(() => evidenceStore.isPaused)
    const recordingDuration = computed(() => evidenceStore.recordingDuration)
    const formattedDuration = computed(() => evidenceStore.formattedDuration)
    const recordingStatus = computed(() => evidenceStore.recordingStatus)
    const currentRecording = computed(() => evidenceStore.currentRecording)
    const error = computed(() => evidenceStore.error)

    const statusText = computed(() => {
      switch (recordingStatus.value) {
        case 'recording': return 'Gravando'
        case 'paused': return 'Pausado'
        default: return 'Pronto'
      }
    })

    // Methods
    async function initializeAndStart() {
      loading.value = true
      
      try {
        const options = {
          includeAudio: includeAudio.value,
          includeSystemAudio: includeSystemAudio.value,
          videoBitrate: getVideoBitrate(),
          audioBitrate: 128000
        }

        const success = await evidenceStore.initializeRecording(selectedType.value, options)
        
        if (success) {
          await startRecording()
        }
      } catch (err) {
        emit('error', err.message)
      } finally {
        loading.value = false
      }
    }

    async function startRecording() {
      const success = await evidenceStore.startRecording()
      if (!success && error.value) {
        emit('error', error.value)
      }
    }

    async function pauseRecording() {
      await evidenceStore.pauseRecording()
    }

    async function resumeRecording() {
      await evidenceStore.resumeRecording()
    }

    async function stopRecording() {
      const success = await evidenceStore.stopRecording()
      if (success) {
        showCompleteModal.value = true
      }
    }

    async function captureScreenshot() {
      try {
        const screenshot = await evidenceStore.captureScreenshot()
        if (screenshot) {
          // Auto-save screenshot
          await handleSaveEvidence(screenshot.blob, {
            type: 'screenshot',
            timestamp: screenshot.timestamp
          })
        }
      } catch (err) {
        emit('error', err.message)
      }
    }

    function cancelRecording() {
      evidenceStore.cleanup()
    }

    async function handleSaveRecording(metadata) {
      if (!currentRecording.value) return

      try {
        await handleSaveEvidence(currentRecording.value.blob, {
          type: 'video',
          duration: currentRecording.value.duration,
          recording_type: currentRecording.value.type,
          ...metadata
        })
        
        evidenceStore.clearCurrentRecording()
        showCompleteModal.value = false
      } catch (err) {
        emit('error', err.message)
      }
    }

    function handleDiscardRecording() {
      evidenceStore.clearCurrentRecording()
      showCompleteModal.value = false
    }

    async function handleSaveEvidence(blob, metadata) {
      const file = new File([blob], `evidence_${Date.now()}.${getFileExtension(blob.type)}`, {
        type: blob.type
      })

      const completeMetadata = {
        transaction_id: props.transactionId,
        ...metadata
      }

      const evidence = await evidenceStore.uploadEvidence(file, completeMetadata)
      emit('evidence-created', evidence)
    }

    function getVideoBitrate() {
      const bitrateMap = {
        low: 1000000,      // 1 Mbps
        medium: 2500000,   // 2.5 Mbps
        high: 5000000,     // 5 Mbps
        ultra: 10000000    // 10 Mbps
      }
      return bitrateMap[videoQuality.value] || bitrateMap.medium
    }

    function getFileExtension(mimeType) {
      const extensions = {
        'video/webm': 'webm',
        'video/mp4': 'mp4',
        'audio/webm': 'webm',
        'audio/mp4': 'm4a',
        'image/png': 'png',
        'image/jpeg': 'jpg'
      }
      return extensions[mimeType] || 'bin'
    }

    // Watch media stream to update preview
    watch(mediaStream, (stream) => {
      if (previewVideo.value && stream) {
        previewVideo.value.srcObject = stream
      }
    })

    // Cleanup on unmount
    onUnmounted(() => {
      evidenceStore.cleanup()
    })

    return {
      // State
      selectedType,
      includeAudio,
      includeSystemAudio,
      videoQuality,
      frameRate,
      loading,
      showCompleteModal,
      previewVideo,
      recordingTypes,
      
      // Computed
      mediaStream,
      isRecording,
      isPaused,
      recordingDuration,
      formattedDuration,
      recordingStatus,
      currentRecording,
      error,
      statusText,
      
      // Methods
      initializeAndStart,
      startRecording,
      pauseRecording,
      resumeRecording,
      stopRecording,
      captureScreenshot,
      cancelRecording,
      handleSaveRecording,
      handleDiscardRecording
    }
  }
}
</script>

<style scoped>
/* Componente já utiliza classes Tailwind CSS */
</style>
