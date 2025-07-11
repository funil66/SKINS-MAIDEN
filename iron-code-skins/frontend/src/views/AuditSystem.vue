<template>
  <div class="audit-system">
    <!-- Page Header -->
    <div class="mb-8">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Sistema de Auditoria</h1>
          <p class="mt-1 text-sm text-gray-600">
            Grave evidências, faça upload de arquivos e mantenha registros imutáveis no blockchain
          </p>
        </div>
        
        <div class="flex items-center space-x-3">
          <!-- Quick Stats -->
          <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-200">
            <div class="text-xs text-gray-500">Evidências Hoje</div>
            <div class="text-lg font-semibold text-green-600">{{ todayEvidenceCount }}</div>
          </div>
          
          <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-200">
            <div class="text-xs text-gray-500">Verificações</div>
            <div class="text-lg font-semibold text-blue-600">{{ todayVerifications }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Transaction Selection -->
    <div class="mb-8">
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">
          Selecionar Transação para Auditoria
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="md:col-span-2">
            <label class="form-label">ID da Transação</label>
            <div class="flex space-x-2">
              <input
                type="text"
                v-model="selectedTransactionId"
                placeholder="Digite o ID da transação ou selecione uma das recentes"
                class="form-input"
              >
              <button
                @click="validateTransaction"
                :disabled="!selectedTransactionId.trim() || validating"
                class="btn-primary px-4 py-2 whitespace-nowrap"
              >
                {{ validating ? 'Validando...' : 'Validar' }}
              </button>
            </div>
          </div>
          
          <div>
            <label class="form-label">Transações Recentes</label>
            <select 
              v-model="selectedTransactionId"
              class="form-input"
              @change="validateTransaction"
            >
              <option value="">Selecione uma transação...</option>
              <option 
                v-for="transaction in recentTransactions" 
                :key="transaction.id"
                :value="transaction.id"
              >
                {{ transaction.id.slice(0, 8) }}... - {{ $formatDate(transaction.created_at) }}
              </option>
            </select>
          </div>
        </div>
        
        <!-- Transaction Info -->
        <div v-if="currentTransaction" class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
          <div class="flex items-center space-x-2 mb-2">
            <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
            </svg>
            <span class="text-sm font-medium text-green-800">Transação Válida</span>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div>
              <span class="font-medium text-green-700">Valor:</span>
              <span class="ml-2 text-green-800">{{ $formatCurrency(currentTransaction.amount) }}</span>
            </div>
            <div>
              <span class="font-medium text-green-700">Status:</span>
              <span class="ml-2 text-green-800">{{ currentTransaction.status }}</span>
            </div>
            <div>
              <span class="font-medium text-green-700">Data:</span>
              <span class="ml-2 text-green-800">{{ $formatDate(currentTransaction.created_at) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Audit Tools -->
    <div v-if="currentTransaction" class="space-y-8">
      <!-- Recording Section -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
          <h3 class="text-lg font-semibold text-gray-900 flex items-center">
            <svg class="w-5 h-5 mr-2 text-red-600" fill="currentColor" viewBox="0 0 20 20">
              <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"/>
            </svg>
            Gravação de Evidências
          </h3>
          <p class="text-sm text-gray-600 mt-1">
            Capture tela, câmera ou áudio para documentar evidências da transação
          </p>
        </div>
        
        <div class="p-6">
          <EvidenceRecorder
            :transaction-id="currentTransaction.id"
            @evidence-created="handleEvidenceCreated"
            @error="handleError"
          />
        </div>
      </div>

      <!-- File Upload Section -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
          <h3 class="text-lg font-semibold text-gray-900 flex items-center">
            <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
              <path d="M5.5 13a3.5 3.5 0 01-.369-6.98 4 4 0 117.753-1.977A4.5 4.5 0 1113.5 13H11V9.413l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13H5.5z"/>
            </svg>
            Upload de Arquivos
          </h3>
          <p class="text-sm text-gray-600 mt-1">
            Faça upload de imagens, vídeos, documentos e outros arquivos como evidências
          </p>
        </div>
        
        <div class="p-6">
          <FileUploader
            :transaction-id="currentTransaction.id"
            @files-uploaded="handleFilesUploaded"
            @upload-progress="handleUploadProgress"
            @error="handleError"
          />
        </div>
      </div>

      <!-- Evidence List -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <svg class="w-5 h-5 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"/>
                </svg>
                Evidências da Transação
              </h3>
              <p class="text-sm text-gray-600 mt-1">
                {{ transactionEvidences.length }} evidência(s) registrada(s)
              </p>
            </div>
            
            <button
              @click="refreshEvidences"
              :disabled="loadingEvidences"
              class="btn-outline px-4 py-2"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
              </svg>
              {{ loadingEvidences ? 'Atualizando...' : 'Atualizar' }}
            </button>
          </div>
        </div>
        
        <div class="p-6">
          <div v-if="loadingEvidences" class="text-center py-8">
            <svg class="animate-spin h-8 w-8 text-gray-400 mx-auto" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="text-gray-500 mt-2">Carregando evidências...</p>
          </div>
          
          <div v-else-if="transactionEvidences.length === 0" class="text-center py-8">
            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            <p class="text-gray-500">Nenhuma evidência encontrada para esta transação</p>
            <p class="text-sm text-gray-400 mt-1">Use as ferramentas acima para começar a documentar</p>
          </div>
          
          <div v-else class="space-y-4">
            <div
              v-for="evidence in transactionEvidences"
              :key="evidence.id"
              class="border border-gray-200 rounded-lg p-4"
            >
              <div class="flex items-center justify-between mb-3">
                <div class="flex items-center space-x-3">
                  <!-- File Type Icon -->
                  <div class="flex-shrink-0">
                    <svg 
                      v-if="evidence.type === 'screenshot' || evidence.mime_type?.startsWith('image/')"
                      class="w-8 h-8 text-green-500" 
                      fill="currentColor" 
                      viewBox="0 0 20 20"
                    >
                      <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"/>
                    </svg>
                    
                    <svg 
                      v-else-if="evidence.type === 'video' || evidence.mime_type?.startsWith('video/')"
                      class="w-8 h-8 text-purple-500" 
                      fill="currentColor" 
                      viewBox="0 0 20 20"
                    >
                      <path d="M2 6a2 2 0 012-2h6l2 2h6a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14 6a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2h8z"/>
                    </svg>
                    
                    <svg 
                      v-else-if="evidence.type === 'audio' || evidence.mime_type?.startsWith('audio/')"
                      class="w-8 h-8 text-blue-500" 
                      fill="currentColor" 
                      viewBox="0 0 20 20"
                    >
                      <path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.707.707L4.586 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.586l3.707-3.707a1 1 0 011.09-.217z"/>
                    </svg>
                    
                    <svg 
                      v-else
                      class="w-8 h-8 text-gray-500" 
                      fill="currentColor" 
                      viewBox="0 0 20 20"
                    >
                      <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/>
                    </svg>
                  </div>
                  
                  <div>
                    <h4 class="font-medium text-gray-900">{{ evidence.filename }}</h4>
                    <p class="text-sm text-gray-500">
                      {{ $formatFileSize(evidence.file_size) }} • 
                      {{ $formatDate(evidence.created_at) }}
                    </p>
                  </div>
                </div>
                
                <div class="flex items-center space-x-2">
                  <!-- Blockchain Status -->
                  <span 
                    :class="[
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                      evidence.blockchain_hash ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                    ]"
                  >
                    {{ evidence.blockchain_hash ? 'Verificado' : 'Pendente' }}
                  </span>
                  
                  <!-- Actions -->
                  <button
                    @click="viewEvidence(evidence)"
                    class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                  >
                    Ver
                  </button>
                  
                  <button
                    @click="verifyEvidence(evidence)"
                    class="text-green-600 hover:text-green-800 text-sm font-medium"
                  >
                    Verificar
                  </button>
                </div>
              </div>
              
              <!-- Hash Preview -->
              <div class="text-xs font-mono text-gray-500 bg-gray-50 p-2 rounded">
                SHA-256: {{ evidence.hash_sha256?.slice(0, 32) }}...
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Evidence Verification Modal -->
    <div
      v-if="showVerificationModal"
      class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
      @click="closeVerificationModal"
    >
      <div
        class="relative top-10 mx-auto p-0 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white"
        @click.stop
      >
        <HashVerifier
          :evidence="selectedEvidence"
          @verification-complete="handleVerificationComplete"
          @error="handleError"
        />
        
        <div class="p-4 border-t border-gray-200">
          <button
            @click="closeVerificationModal"
            class="btn-outline px-6 py-2"
          >
            Fechar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useAuditStore } from '@/stores/auditStore'
import EvidenceRecorder from '@/components/audit/EvidenceRecorder.vue'
import FileUploader from '@/components/audit/FileUploader.vue'
import HashVerifier from '@/components/blockchain/HashVerifier.vue'

export default {
  name: 'AuditSystem',
  components: {
    EvidenceRecorder,
    FileUploader,
    HashVerifier
  },
  setup() {
    const auditStore = useAuditStore()
    
    // State
    const selectedTransactionId = ref('')
    const currentTransaction = ref(null)
    const validating = ref(false)
    const loadingEvidences = ref(false)
    const showVerificationModal = ref(false)
    const selectedEvidence = ref(null)
    
    // Mock data (em produção viria da API)
    const recentTransactions = ref([
      {
        id: 'tx_1234567890abcdef',
        amount: 150.00,
        status: 'completed',
        created_at: new Date(Date.now() - 3600000).toISOString()
      },
      {
        id: 'tx_abcdef1234567890',
        amount: 75.50,
        status: 'pending',
        created_at: new Date(Date.now() - 7200000).toISOString()
      }
    ])

    // Computed
    const transactionEvidences = computed(() => auditStore.evidences)
    const todayEvidenceCount = computed(() => {
      const today = new Date().toDateString()
      return auditStore.evidences.filter(e => 
        new Date(e.created_at).toDateString() === today
      ).length
    })
    
    const todayVerifications = computed(() => {
      return auditStore.evidences.filter(e => e.blockchain_hash).length
    })

    // Methods
    async function validateTransaction() {
      if (!selectedTransactionId.value.trim()) return
      
      validating.value = true
      
      try {
        // Simular validação da transação
        await new Promise(resolve => setTimeout(resolve, 1000))
        
        // Mock: encontrar transação
        const transaction = recentTransactions.value.find(t => 
          t.id === selectedTransactionId.value
        ) || {
          id: selectedTransactionId.value,
          amount: 100.00,
          status: 'completed',
          created_at: new Date().toISOString()
        }
        
        currentTransaction.value = transaction
        
        // Carregar evidências da transação
        await refreshEvidences()
        
      } catch (error) {
        console.error('Erro ao validar transação:', error)
      } finally {
        validating.value = false
      }
    }

    async function refreshEvidences() {
      if (!currentTransaction.value) return
      
      loadingEvidences.value = true
      
      try {
        await auditStore.fetchEvidences(currentTransaction.value.id)
      } catch (error) {
        console.error('Erro ao carregar evidências:', error)
      } finally {
        loadingEvidences.value = false
      }
    }

    function handleEvidenceCreated(evidence) {
      console.log('Nova evidência criada:', evidence)
      // A evidência já foi adicionada ao store pelo componente
      
      // Mostrar notificação de sucesso
      // addNotification({
      //   type: 'success',
      //   title: 'Evidência Criada',
      //   message: 'Evidência gravada e enviada com sucesso'
      // })
    }

    function handleFilesUploaded(result) {
      console.log('Arquivos enviados:', result)
      
      // Atualizar lista de evidências
      refreshEvidences()
    }

    function handleUploadProgress(progress) {
      console.log('Progresso do upload:', progress)
    }

    function handleError(error) {
      console.error('Erro no sistema de auditoria:', error)
    }

    function viewEvidence(evidence) {
      // Implementar visualização da evidência
      console.log('Visualizar evidência:', evidence)
    }

    function verifyEvidence(evidence) {
      selectedEvidence.value = evidence
      showVerificationModal.value = true
    }

    function closeVerificationModal() {
      showVerificationModal.value = false
      selectedEvidence.value = null
    }

    function handleVerificationComplete(result) {
      console.log('Verificação completa:', result)
      
      // Atualizar evidência com resultado da verificação
      const evidenceIndex = auditStore.evidences.findIndex(e => 
        e.id === selectedEvidence.value.id
      )
      
      if (evidenceIndex !== -1) {
        auditStore.evidences[evidenceIndex] = {
          ...auditStore.evidences[evidenceIndex],
          verification_result: result
        }
      }
    }

    // Initialize
    onMounted(() => {
      // Auto-selecionar primeira transação recente se disponível
      if (recentTransactions.value.length > 0) {
        selectedTransactionId.value = recentTransactions.value[0].id
        validateTransaction()
      }
    })

    return {
      // State
      selectedTransactionId,
      currentTransaction,
      validating,
      loadingEvidences,
      showVerificationModal,
      selectedEvidence,
      recentTransactions,
      
      // Computed
      transactionEvidences,
      todayEvidenceCount,
      todayVerifications,
      
      // Methods
      validateTransaction,
      refreshEvidences,
      handleEvidenceCreated,
      handleFilesUploaded,
      handleUploadProgress,
      handleError,
      viewEvidence,
      verifyEvidence,
      closeVerificationModal,
      handleVerificationComplete
    }
  }
}
</script>

<style scoped>
/* Componente já utiliza classes Tailwind CSS */
</style>
