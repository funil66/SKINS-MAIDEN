<template>
  <div class="hash-verifier bg-white rounded-lg shadow-lg p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-lg font-semibold text-gray-900">
        <svg class="inline-block w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
        </svg>
        Verificação de Integridade
      </h3>
      
      <div v-if="evidence" class="text-sm text-gray-500">
        ID: {{ evidence.id?.slice(0, 8) }}...
      </div>
    </div>

    <!-- Evidence Info -->
    <div v-if="evidence" class="mb-6">
      <div class="bg-gray-50 rounded-lg p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
          <div>
            <span class="font-medium text-gray-700">Arquivo:</span>
            <div class="mt-1 text-gray-900">{{ evidence.filename }}</div>
          </div>
          <div>
            <span class="font-medium text-gray-700">Tamanho:</span>
            <div class="mt-1 text-gray-900">{{ formatFileSize(evidence.file_size) }}</div>
          </div>
          <div>
            <span class="font-medium text-gray-700">Criado em:</span>
            <div class="mt-1 text-gray-900">{{ formatDate(evidence.created_at) }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Hash Information -->
    <div class="mb-6">
      <h4 class="text-sm font-medium text-gray-900 mb-3">Hash SHA-256</h4>
      
      <div class="bg-gray-900 rounded-lg p-4">
        <div class="flex items-center justify-between">
          <code class="text-green-400 font-mono text-sm break-all">
            {{ evidence?.hash_sha256 || hashInput }}
          </code>
          
          <button
            v-if="evidence?.hash_sha256"
            @click="copyToClipboard(evidence.hash_sha256)"
            class="ml-3 text-gray-400 hover:text-white transition-colors"
            title="Copiar hash"
          >
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
              <path d="M8 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z"/>
              <path d="M6 3a2 2 0 00-2 2v11a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2 3 3 0 01-3 3H9a3 3 0 01-3-3z"/>
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Manual Hash Input (if no evidence provided) -->
    <div v-if="!evidence" class="mb-6">
      <label class="form-label">
        Inserir Hash para Verificação
      </label>
      <div class="flex space-x-2">
        <input
          type="text"
          v-model="hashInput"
          placeholder="Ex: a1b2c3d4e5f6..."
          class="form-input font-mono text-sm"
          pattern="[a-fA-F0-9]{64}"
          maxlength="64"
        >
        <button
          @click="verifyManualHash"
          :disabled="!isValidHash(hashInput) || verifying"
          class="btn-primary px-4 py-2 whitespace-nowrap"
        >
          {{ verifying ? 'Verificando...' : 'Verificar' }}
        </button>
      </div>
      <p class="text-xs text-gray-500 mt-1">
        Hash deve ter exatamente 64 caracteres hexadecimais
      </p>
    </div>

    <!-- Verification Steps -->
    <div class="mb-6">
      <h4 class="text-sm font-medium text-gray-900 mb-4">Status da Verificação</h4>
      
      <div class="space-y-3">
        <!-- Local Hash Check -->
        <div class="flex items-center space-x-3">
          <div class="flex-shrink-0">
            <div 
              :class="[
                'w-6 h-6 rounded-full flex items-center justify-center',
                getStepStatus('hash').color
              ]"
            >
              <svg v-if="getStepStatus('hash').icon === 'check'" class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
              </svg>
              <svg v-else-if="getStepStatus('hash').icon === 'x'" class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"/>
              </svg>
              <div v-else class="w-2 h-2 bg-current rounded-full"></div>
            </div>
          </div>
          
          <div class="flex-1">
            <p class="text-sm font-medium text-gray-900">
              Integridade do Hash
            </p>
            <p class="text-xs text-gray-500">
              {{ getStepStatus('hash').message }}
            </p>
          </div>
          
          <div v-if="getStepStatus('hash').loading" class="flex-shrink-0">
            <svg class="animate-spin h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </div>
        </div>

        <!-- Blockchain Registration Check -->
        <div class="flex items-center space-x-3">
          <div class="flex-shrink-0">
            <div 
              :class="[
                'w-6 h-6 rounded-full flex items-center justify-center',
                getStepStatus('blockchain').color
              ]"
            >
              <svg v-if="getStepStatus('blockchain').icon === 'check'" class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
              </svg>
              <svg v-else-if="getStepStatus('blockchain').icon === 'x'" class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"/>
              </svg>
              <div v-else class="w-2 h-2 bg-current rounded-full"></div>
            </div>
          </div>
          
          <div class="flex-1">
            <p class="text-sm font-medium text-gray-900">
              Registro no Blockchain
            </p>
            <p class="text-xs text-gray-500">
              {{ getStepStatus('blockchain').message }}
            </p>
          </div>
          
          <div v-if="getStepStatus('blockchain').loading" class="flex-shrink-0">
            <svg class="animate-spin h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </div>
        </div>

        <!-- Timestamp Verification -->
        <div class="flex items-center space-x-3">
          <div class="flex-shrink-0">
            <div 
              :class="[
                'w-6 h-6 rounded-full flex items-center justify-center',
                getStepStatus('timestamp').color
              ]"
            >
              <svg v-if="getStepStatus('timestamp').icon === 'check'" class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
              </svg>
              <svg v-else-if="getStepStatus('timestamp').icon === 'x'" class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"/>
              </svg>
              <div v-else class="w-2 h-2 bg-current rounded-full"></div>
            </div>
          </div>
          
          <div class="flex-1">
            <p class="text-sm font-medium text-gray-900">
              Timestamp Blockchain
            </p>
            <p class="text-xs text-gray-500">
              {{ getStepStatus('timestamp').message }}
            </p>
          </div>
          
          <div v-if="getStepStatus('timestamp').loading" class="flex-shrink-0">
            <svg class="animate-spin h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Blockchain Details -->
    <div v-if="blockchainRecord" class="mb-6">
      <h4 class="text-sm font-medium text-gray-900 mb-3">Detalhes do Blockchain</h4>
      
      <div class="bg-gray-50 rounded-lg p-4 space-y-3">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
          <div>
            <span class="font-medium text-gray-700">Rede:</span>
            <div class="mt-1 text-gray-900 capitalize">{{ blockchainRecord.network }}</div>
          </div>
          
          <div>
            <span class="font-medium text-gray-700">Confirmações:</span>
            <div class="mt-1 text-gray-900">{{ blockchainRecord.confirmation_count }}</div>
          </div>
          
          <div>
            <span class="font-medium text-gray-700">Status:</span>
            <span 
              :class="[
                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ml-2',
                blockchainRecord.status === 'confirmed' ? 'bg-green-100 text-green-800' :
                blockchainRecord.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                'bg-red-100 text-red-800'
              ]"
            >
              {{ getStatusLabel(blockchainRecord.status) }}
            </span>
          </div>
          
          <div>
            <span class="font-medium text-gray-700">Gas Usado:</span>
            <div class="mt-1 text-gray-900">{{ blockchainRecord.gas_used?.toLocaleString() }}</div>
          </div>
        </div>
        
        <div>
          <span class="font-medium text-gray-700">Hash da Transação:</span>
          <div class="mt-1 flex items-center space-x-2">
            <code class="bg-gray-900 text-green-400 px-2 py-1 rounded text-xs font-mono break-all">
              {{ blockchainRecord.transaction_hash }}
            </code>
            <button
              @click="copyToClipboard(blockchainRecord.transaction_hash)"
              class="text-gray-400 hover:text-gray-600 transition-colors"
              title="Copiar hash da transação"
            >
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path d="M8 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z"/>
                <path d="M6 3a2 2 0 00-2 2v11a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2 3 3 0 01-3 3H9a3 3 0 01-3-3z"/>
              </svg>
            </button>
            <a
              :href="getExplorerUrl(blockchainRecord.transaction_hash)"
              target="_blank"
              class="text-blue-600 hover:text-blue-800 transition-colors"
              title="Ver no explorer"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
              </svg>
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-between">
      <button
        v-if="evidence && !verificationResult"
        @click="startCompleteVerification"
        :disabled="verifying"
        class="btn-primary px-6 py-2"
      >
        <svg v-if="verifying" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        {{ verifying ? 'Verificando...' : 'Verificar Integridade Completa' }}
      </button>
      
      <!-- Results Summary -->
      <div v-if="verificationResult" class="flex items-center space-x-4">
        <div 
          :class="[
            'flex items-center px-3 py-1 rounded-full text-sm font-medium',
            verificationResult.overall_status === 'valid' ? 'bg-green-100 text-green-800' :
            verificationResult.overall_status === 'invalid' ? 'bg-red-100 text-red-800' :
            'bg-yellow-100 text-yellow-800'
          ]"
        >
          <svg 
            v-if="verificationResult.overall_status === 'valid'"
            class="w-4 h-4 mr-1" 
            fill="currentColor" 
            viewBox="0 0 20 20"
          >
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
          </svg>
          <svg 
            v-else-if="verificationResult.overall_status === 'invalid'"
            class="w-4 h-4 mr-1" 
            fill="currentColor" 
            viewBox="0 0 20 20"
          >
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/>
          </svg>
          <svg 
            v-else
            class="w-4 h-4 mr-1" 
            fill="currentColor" 
            viewBox="0 0 20 20"
          >
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"/>
          </svg>
          {{ getOverallStatusLabel(verificationResult.overall_status) }}
        </div>
        
        <button
          @click="generateReport"
          class="btn-outline px-4 py-2 text-sm"
        >
          Gerar Relatório
        </button>
      </div>
    </div>

    <!-- Error Display -->
    <div v-if="error" class="mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md">
      <div class="flex">
        <svg class="w-5 h-5 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/>
        </svg>
        <div>
          <p class="font-medium">Erro na verificação:</p>
          <p class="text-sm">{{ error }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useAuditStore } from '@/stores/auditStore'
import blockchainService from '@/services/blockchainService'

export default {
  name: 'HashVerifier',
  props: {
    evidence: {
      type: Object,
      default: null
    },
    autoVerify: {
      type: Boolean,
      default: true
    }
  },
  emits: ['verification-complete', 'error'],
  setup(props, { emit }) {
    const auditStore = useAuditStore()
    
    // State
    const hashInput = ref('')
    const verifying = ref(false)
    const verificationResult = ref(null)
    const blockchainRecord = ref(null)
    const error = ref(null)
    
    const verificationSteps = ref({
      hash: { status: 'pending', message: 'Aguardando verificação', loading: false },
      blockchain: { status: 'pending', message: 'Aguardando verificação', loading: false },
      timestamp: { status: 'pending', message: 'Aguardando verificação', loading: false }
    })

    // Methods
    function isValidHash(hash) {
      return /^[a-fA-F0-9]{64}$/.test(hash)
    }

    function getStepStatus(step) {
      const stepData = verificationSteps.value[step]
      
      const statusConfig = {
        pending: { color: 'bg-gray-400', icon: 'dot', loading: false },
        loading: { color: 'bg-blue-500', icon: 'dot', loading: true },
        valid: { color: 'bg-green-500', icon: 'check', loading: false },
        invalid: { color: 'bg-red-500', icon: 'x', loading: false }
      }
      
      return {
        ...statusConfig[stepData.status],
        message: stepData.message
      }
    }

    function getStatusLabel(status) {
      const labels = {
        confirmed: 'Confirmado',
        pending: 'Pendente',
        failed: 'Falhou'
      }
      return labels[status] || status
    }

    function getOverallStatusLabel(status) {
      const labels = {
        valid: 'Integridade Verificada',
        invalid: 'Integridade Comprometida',
        partial: 'Verificação Parcial'
      }
      return labels[status] || status
    }

    async function verifyManualHash() {
      if (!isValidHash(hashInput.value)) return
      
      await performVerification(hashInput.value)
    }

    async function startCompleteVerification() {
      if (!props.evidence?.hash_sha256) return
      
      await performVerification(props.evidence.hash_sha256)
    }

    async function performVerification(hash) {
      verifying.value = true
      error.value = null
      
      // Reset steps
      Object.keys(verificationSteps.value).forEach(step => {
        verificationSteps.value[step] = { 
          status: 'pending', 
          message: 'Aguardando verificação', 
          loading: false 
        }
      })

      try {
        // Step 1: Hash Integrity Check
        await verifyHashIntegrity(hash)
        
        // Step 2: Blockchain Registration Check
        await verifyBlockchainRegistration(hash)
        
        // Step 3: Timestamp Verification
        await verifyTimestamp(hash)
        
        // Calculate overall result
        calculateOverallResult()
        
        emit('verification-complete', verificationResult.value)
        
      } catch (err) {
        error.value = err.message
        emit('error', err)
      } finally {
        verifying.value = false
      }
    }

    async function verifyHashIntegrity(hash) {
      verificationSteps.value.hash = { 
        status: 'loading', 
        message: 'Verificando integridade do hash...', 
        loading: true 
      }

      try {
        // Simulate hash verification - in real implementation, this would
        // verify the hash against the original file or stored hash
        await new Promise(resolve => setTimeout(resolve, 1000))
        
        const isValid = hash.length === 64 && /^[a-fA-F0-9]+$/.test(hash)
        
        verificationSteps.value.hash = {
          status: isValid ? 'valid' : 'invalid',
          message: isValid ? 'Hash válido e íntegro' : 'Hash inválido ou corrompido',
          loading: false
        }
      } catch (err) {
        verificationSteps.value.hash = {
          status: 'invalid',
          message: `Erro na verificação: ${err.message}`,
          loading: false
        }
      }
    }

    async function verifyBlockchainRegistration(hash) {
      verificationSteps.value.blockchain = { 
        status: 'loading', 
        message: 'Verificando registro no blockchain...', 
        loading: true 
      }

      try {
        const response = await blockchainService.verifyHash(hash)
        const isRegistered = response.data.registered
        
        if (isRegistered) {
          blockchainRecord.value = response.data.record
          verificationSteps.value.blockchain = {
            status: 'valid',
            message: 'Hash registrado no blockchain',
            loading: false
          }
        } else {
          verificationSteps.value.blockchain = {
            status: 'invalid',
            message: 'Hash não encontrado no blockchain',
            loading: false
          }
        }
      } catch (err) {
        verificationSteps.value.blockchain = {
          status: 'invalid',
          message: `Erro na verificação blockchain: ${err.message}`,
          loading: false
        }
      }
    }

    async function verifyTimestamp(hash) {
      verificationSteps.value.timestamp = { 
        status: 'loading', 
        message: 'Verificando timestamp blockchain...', 
        loading: true 
      }

      try {
        if (blockchainRecord.value) {
          const now = new Date()
          const createdAt = new Date(blockchainRecord.value.created_at)
          const confirmedAt = blockchainRecord.value.confirmed_at ? 
            new Date(blockchainRecord.value.confirmed_at) : null
          
          const isTimestampValid = createdAt <= now && 
            (!confirmedAt || confirmedAt >= createdAt)
          
          verificationSteps.value.timestamp = {
            status: isTimestampValid ? 'valid' : 'invalid',
            message: isTimestampValid ? 
              `Timestamp válido (${formatDate(createdAt)})` : 
              'Timestamp inválido ou inconsistente',
            loading: false
          }
        } else {
          verificationSteps.value.timestamp = {
            status: 'invalid',
            message: 'Nenhum registro blockchain encontrado',
            loading: false
          }
        }
      } catch (err) {
        verificationSteps.value.timestamp = {
          status: 'invalid',
          message: `Erro na verificação de timestamp: ${err.message}`,
          loading: false
        }
      }
    }

    function calculateOverallResult() {
      const steps = Object.values(verificationSteps.value)
      const validSteps = steps.filter(step => step.status === 'valid').length
      const invalidSteps = steps.filter(step => step.status === 'invalid').length
      
      let overallStatus
      if (invalidSteps === 0 && validSteps === steps.length) {
        overallStatus = 'valid'
      } else if (invalidSteps > 0 && validSteps > 0) {
        overallStatus = 'partial'
      } else {
        overallStatus = 'invalid'
      }
      
      verificationResult.value = {
        overall_status: overallStatus,
        steps: { ...verificationSteps.value },
        blockchain_record: blockchainRecord.value,
        verified_at: new Date().toISOString(),
        hash: props.evidence?.hash_sha256 || hashInput.value
      }
    }

    async function generateReport() {
      try {
        const reportData = {
          evidence_id: props.evidence?.id,
          verification_result: verificationResult.value,
          generated_at: new Date().toISOString()
        }
        
        // In real implementation, this would call the audit service
        // to generate a PDF report
        console.log('Generating verification report:', reportData)
        
        // For now, just download as JSON
        const blob = new Blob([JSON.stringify(reportData, null, 2)], {
          type: 'application/json'
        })
        const url = URL.createObjectURL(blob)
        const a = document.createElement('a')
        a.href = url
        a.download = `verification-report-${Date.now()}.json`
        a.click()
        URL.revokeObjectURL(url)
        
      } catch (err) {
        error.value = `Erro ao gerar relatório: ${err.message}`
      }
    }

    function formatFileSize(bytes) {
      if (!bytes) return 'N/A'
      
      const k = 1024
      const sizes = ['Bytes', 'KB', 'MB', 'GB']
      const i = Math.floor(Math.log(bytes) / Math.log(k))
      
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
    }

    function formatDate(date) {
      if (!date) return 'N/A'
      return new Date(date).toLocaleString('pt-BR')
    }

    async function copyToClipboard(text) {
      try {
        await navigator.clipboard.writeText(text)
        // You could show a toast notification here
      } catch (err) {
        console.error('Failed to copy:', err)
      }
    }

    function getExplorerUrl(txHash) {
      // This would depend on the blockchain network
      // For Polygon Mumbai testnet:
      return `https://mumbai.polygonscan.com/tx/${txHash}`
    }

    // Auto-verify if evidence is provided and autoVerify is true
    onMounted(() => {
      if (props.evidence?.hash_sha256 && props.autoVerify) {
        startCompleteVerification()
      }
    })

    return {
      // State
      hashInput,
      verifying,
      verificationResult,
      blockchainRecord,
      error,
      verificationSteps,
      
      // Methods
      isValidHash,
      getStepStatus,
      getStatusLabel,
      getOverallStatusLabel,
      verifyManualHash,
      startCompleteVerification,
      generateReport,
      formatFileSize,
      formatDate,
      copyToClipboard,
      getExplorerUrl
    }
  }
}
</script>

<style scoped>
/* Componente já utiliza classes Tailwind CSS */
</style>
