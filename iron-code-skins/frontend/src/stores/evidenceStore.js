import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import evidenceService from '@/services/evidenceService'
import CryptoJS from 'crypto-js'

export const useEvidenceStore = defineStore('evidence', () => {
  // State
  const evidences = ref([])
  const currentRecording = ref(null)
  const mediaStream = ref(null)
  const mediaRecorder = ref(null)
  const recordedChunks = ref([])
  const isRecording = ref(false)
  const isPaused = ref(false)
  const recordingDuration = ref(0)
  const recordingStartTime = ref(null)
  const recordingType = ref('screen') // 'screen', 'camera', 'audio'
  const capturedScreenshots = ref([])
  const loading = ref(false)
  const error = ref(null)

  // Recording timer interval
  let recordingInterval = null

  // Getters
  const formattedDuration = computed(() => {
    const minutes = Math.floor(recordingDuration.value / 60)
    const seconds = recordingDuration.value % 60
    return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`
  })

  const canRecord = computed(() => !isRecording.value && !loading.value)
  const canPause = computed(() => isRecording.value && !isPaused.value)
  const canResume = computed(() => isRecording.value && isPaused.value)
  const canStop = computed(() => isRecording.value)

  const recordingStatus = computed(() => {
    if (!isRecording.value) return 'stopped'
    if (isPaused.value) return 'paused'
    return 'recording'
  })

  // Actions
  async function initializeRecording(type = 'screen', options = {}) {
    error.value = null
    recordingType.value = type

    try {
      let stream
      
      switch (type) {
        case 'screen':
          stream = await navigator.mediaDevices.getDisplayMedia({
            video: {
              mediaSource: 'screen',
              width: { ideal: 1920 },
              height: { ideal: 1080 },
              frameRate: { ideal: 30, max: 60 }
            },
            audio: options.includeAudio || false
          })
          break
          
        case 'camera':
          stream = await navigator.mediaDevices.getUserMedia({
            video: {
              width: { ideal: 1280 },
              height: { ideal: 720 },
              frameRate: { ideal: 30 }
            },
            audio: options.includeAudio || false
          })
          break
          
        case 'audio':
          stream = await navigator.mediaDevices.getUserMedia({
            audio: {
              echoCancellation: true,
              noiseSuppression: true,
              sampleRate: 44100
            }
          })
          break
          
        default:
          throw new Error('Tipo de gravação não suportado')
      }

      mediaStream.value = stream
      
      // Configurar MediaRecorder
      const mimeType = getSupportedMimeType()
      mediaRecorder.value = new MediaRecorder(stream, {
        mimeType,
        videoBitsPerSecond: options.videoBitrate || 2500000,
        audioBitsPerSecond: options.audioBitrate || 128000
      })

      // Event listeners
      mediaRecorder.value.ondataavailable = (event) => {
        if (event.data.size > 0) {
          recordedChunks.value.push(event.data)
        }
      }

      mediaRecorder.value.onstop = () => {
        stopRecordingTimer()
        createRecordingFile()
      }

      mediaRecorder.value.onerror = (event) => {
        error.value = `Erro na gravação: ${event.error}`
        console.error('MediaRecorder error:', event.error)
      }

      return true
    } catch (err) {
      error.value = `Erro ao inicializar gravação: ${err.message}`
      console.error('Erro ao inicializar gravação:', err)
      return false
    }
  }

  function startRecording() {
    if (!mediaRecorder.value || mediaRecorder.value.state !== 'inactive') {
      error.value = 'Gravador não está pronto'
      return false
    }

    try {
      recordedChunks.value = []
      mediaRecorder.value.start(1000) // Captura dados a cada 1 segundo
      
      isRecording.value = true
      isPaused.value = false
      recordingStartTime.value = Date.now()
      recordingDuration.value = 0
      
      startRecordingTimer()
      
      return true
    } catch (err) {
      error.value = `Erro ao iniciar gravação: ${err.message}`
      console.error('Erro ao iniciar gravação:', err)
      return false
    }
  }

  function pauseRecording() {
    if (!mediaRecorder.value || mediaRecorder.value.state !== 'recording') {
      return false
    }

    try {
      mediaRecorder.value.pause()
      isPaused.value = true
      stopRecordingTimer()
      return true
    } catch (err) {
      error.value = `Erro ao pausar gravação: ${err.message}`
      return false
    }
  }

  function resumeRecording() {
    if (!mediaRecorder.value || mediaRecorder.value.state !== 'paused') {
      return false
    }

    try {
      mediaRecorder.value.resume()
      isPaused.value = false
      startRecordingTimer()
      return true
    } catch (err) {
      error.value = `Erro ao retomar gravação: ${err.message}`
      return false
    }
  }

  function stopRecording() {
    if (!mediaRecorder.value || mediaRecorder.value.state === 'inactive') {
      return false
    }

    try {
      mediaRecorder.value.stop()
      
      // Parar todas as tracks do stream
      if (mediaStream.value) {
        mediaStream.value.getTracks().forEach(track => track.stop())
      }
      
      isRecording.value = false
      isPaused.value = false
      
      return true
    } catch (err) {
      error.value = `Erro ao parar gravação: ${err.message}`
      return false
    }
  }

  async function captureScreenshot() {
    if (!mediaStream.value) {
      error.value = 'Stream de mídia não disponível'
      return null
    }

    try {
      const video = document.createElement('video')
      video.srcObject = mediaStream.value
      video.play()

      return new Promise((resolve, reject) => {
        video.onloadedmetadata = () => {
          const canvas = document.createElement('canvas')
          canvas.width = video.videoWidth
          canvas.height = video.videoHeight
          
          const ctx = canvas.getContext('2d')
          ctx.drawImage(video, 0, 0)
          
          canvas.toBlob((blob) => {
            if (blob) {
              const screenshot = {
                id: generateId(),
                blob,
                timestamp: Date.now(),
                width: canvas.width,
                height: canvas.height,
                type: 'image/png'
              }
              
              capturedScreenshots.value.push(screenshot)
              resolve(screenshot)
            } else {
              reject(new Error('Falha ao capturar screenshot'))
            }
          }, 'image/png')
        }
      })
    } catch (err) {
      error.value = `Erro ao capturar screenshot: ${err.message}`
      return null
    }
  }

  async function uploadEvidence(file, metadata = {}) {
    loading.value = true
    error.value = null

    try {
      // Gerar hash SHA-256 do arquivo
      const hash = await generateFileHash(file)
      
      // Preparar metadata completa
      const completeMetadata = {
        ...metadata,
        hash_sha256: hash,
        timestamp: Date.now(),
        user_agent: navigator.userAgent,
        file_size: file.size,
        mime_type: file.type
      }

      const response = await evidenceService.upload(file, completeMetadata)
      
      evidences.value.unshift(response.data)
      return response.data
    } catch (err) {
      error.value = `Erro ao fazer upload: ${err.message}`
      console.error('Erro no upload:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  // Helper functions
  function getSupportedMimeType() {
    const types = [
      'video/webm;codecs=vp9,opus',
      'video/webm;codecs=vp8,opus',
      'video/webm',
      'video/mp4'
    ]
    
    return types.find(type => MediaRecorder.isTypeSupported(type)) || 'video/webm'
  }

  function startRecordingTimer() {
    recordingInterval = setInterval(() => {
      recordingDuration.value = Math.floor((Date.now() - recordingStartTime.value) / 1000)
    }, 1000)
  }

  function stopRecordingTimer() {
    if (recordingInterval) {
      clearInterval(recordingInterval)
      recordingInterval = null
    }
  }

  function createRecordingFile() {
    if (recordedChunks.value.length === 0) {
      error.value = 'Nenhum dado gravado'
      return null
    }

    const mimeType = getSupportedMimeType()
    const blob = new Blob(recordedChunks.value, { type: mimeType })
    
    currentRecording.value = {
      id: generateId(),
      blob,
      mimeType,
      size: blob.size,
      duration: recordingDuration.value,
      type: recordingType.value,
      timestamp: recordingStartTime.value
    }

    return currentRecording.value
  }

  async function generateFileHash(file) {
    return new Promise((resolve, reject) => {
      const reader = new FileReader()
      
      reader.onload = (event) => {
        const wordArray = CryptoJS.lib.WordArray.create(event.target.result)
        const hash = CryptoJS.SHA256(wordArray).toString()
        resolve(hash)
      }
      
      reader.onerror = () => reject(new Error('Erro ao ler arquivo para hash'))
      reader.readAsArrayBuffer(file)
    })
  }

  function generateId() {
    return Math.random().toString(36).substr(2, 9) + Date.now().toString(36)
  }

  function clearCurrentRecording() {
    currentRecording.value = null
    recordedChunks.value = []
    recordingDuration.value = 0
    recordingStartTime.value = null
  }

  function clearScreenshots() {
    capturedScreenshots.value = []
  }

  function clearError() {
    error.value = null
  }

  // Cleanup function
  function cleanup() {
    if (isRecording.value) {
      stopRecording()
    }
    
    if (mediaStream.value) {
      mediaStream.value.getTracks().forEach(track => track.stop())
      mediaStream.value = null
    }
    
    mediaRecorder.value = null
    stopRecordingTimer()
  }

  // Initialize store
  function $reset() {
    cleanup()
    evidences.value = []
    currentRecording.value = null
    capturedScreenshots.value = []
    recordingType.value = 'screen'
    loading.value = false
    error.value = null
  }

  return {
    // State
    evidences,
    currentRecording,
    mediaStream,
    mediaRecorder,
    isRecording,
    isPaused,
    recordingDuration,
    recordingType,
    capturedScreenshots,
    loading,
    error,
    
    // Getters
    formattedDuration,
    canRecord,
    canPause,
    canResume,
    canStop,
    recordingStatus,
    
    // Actions
    initializeRecording,
    startRecording,
    pauseRecording,
    resumeRecording,
    stopRecording,
    captureScreenshot,
    uploadEvidence,
    clearCurrentRecording,
    clearScreenshots,
    clearError,
    cleanup,
    $reset
  }
})
