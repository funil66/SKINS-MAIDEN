import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import auditService from '@/services/auditService'

export const useAuditStore = defineStore('audit', () => {
  // State
  const evidences = ref([])
  const currentEvidence = ref(null)
  const isRecording = ref(false)
  const isUploading = ref(false)
  const uploadProgress = ref(0)
  const auditLogs = ref([])
  const blockchainRecords = ref([])
  const loading = ref(false)
  const error = ref(null)

  // Getters
  const totalEvidences = computed(() => evidences.value.length)
  const verifiedEvidences = computed(() => 
    evidences.value.filter(evidence => evidence.blockchain_hash).length
  )
  const recentEvidences = computed(() => 
    evidences.value
      .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
      .slice(0, 10)
  )
  const evidencesByType = computed(() => {
    const types = {}
    evidences.value.forEach(evidence => {
      types[evidence.type] = (types[evidence.type] || 0) + 1
    })
    return types
  })

  // Actions
  async function fetchEvidences(transactionId = null) {
    loading.value = true
    error.value = null
    
    try {
      const response = await auditService.getEvidences(transactionId)
      evidences.value = response.data
    } catch (err) {
      error.value = err.message || 'Erro ao carregar evidências'
      console.error('Erro ao buscar evidências:', err)
    } finally {
      loading.value = false
    }
  }

  async function uploadEvidence(file, metadata = {}) {
    isUploading.value = true
    uploadProgress.value = 0
    error.value = null

    try {
      const formData = new FormData()
      formData.append('file', file)
      formData.append('metadata', JSON.stringify(metadata))
      formData.append('transaction_id', metadata.transaction_id)

      const response = await auditService.uploadEvidence(formData, {
        onUploadProgress: (progressEvent) => {
          uploadProgress.value = Math.round(
            (progressEvent.loaded * 100) / progressEvent.total
          )
        }
      })

      evidences.value.unshift(response.data)
      return response.data
    } catch (err) {
      error.value = err.message || 'Erro ao fazer upload da evidência'
      console.error('Erro no upload:', err)
      throw err
    } finally {
      isUploading.value = false
      uploadProgress.value = 0
    }
  }

  async function verifyEvidenceHash(evidenceId) {
    loading.value = true
    error.value = null

    try {
      const response = await auditService.verifyHash(evidenceId)
      
      // Atualizar evidência com resultado da verificação
      const evidenceIndex = evidences.value.findIndex(e => e.id === evidenceId)
      if (evidenceIndex !== -1) {
        evidences.value[evidenceIndex] = {
          ...evidences.value[evidenceIndex],
          ...response.data
        }
      }

      return response.data
    } catch (err) {
      error.value = err.message || 'Erro ao verificar hash da evidência'
      console.error('Erro na verificação:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  async function fetchAuditLogs(evidenceId) {
    loading.value = true
    error.value = null

    try {
      const response = await auditService.getAuditLogs(evidenceId)
      auditLogs.value = response.data
    } catch (err) {
      error.value = err.message || 'Erro ao carregar logs de auditoria'
      console.error('Erro ao buscar logs:', err)
    } finally {
      loading.value = false
    }
  }

  async function fetchBlockchainRecords(evidenceId) {
    loading.value = true
    error.value = null

    try {
      const response = await auditService.getBlockchainRecords(evidenceId)
      blockchainRecords.value = response.data
    } catch (err) {
      error.value = err.message || 'Erro ao carregar registros blockchain'
      console.error('Erro ao buscar registros blockchain:', err)
    } finally {
      loading.value = false
    }
  }

  function startRecording() {
    isRecording.value = true
    error.value = null
  }

  function stopRecording() {
    isRecording.value = false
  }

  function setCurrentEvidence(evidence) {
    currentEvidence.value = evidence
  }

  function clearError() {
    error.value = null
  }

  function clearEvidences() {
    evidences.value = []
    currentEvidence.value = null
  }

  // Initialize store
  function $reset() {
    evidences.value = []
    currentEvidence.value = null
    isRecording.value = false
    isUploading.value = false
    uploadProgress.value = 0
    auditLogs.value = []
    blockchainRecords.value = []
    loading.value = false
    error.value = null
  }

  return {
    // State
    evidences,
    currentEvidence,
    isRecording,
    isUploading,
    uploadProgress,
    auditLogs,
    blockchainRecords,
    loading,
    error,
    
    // Getters
    totalEvidences,
    verifiedEvidences,
    recentEvidences,
    evidencesByType,
    
    // Actions
    fetchEvidences,
    uploadEvidence,
    verifyEvidenceHash,
    fetchAuditLogs,
    fetchBlockchainRecords,
    startRecording,
    stopRecording,
    setCurrentEvidence,
    clearError,
    clearEvidences,
    $reset
  }
})
