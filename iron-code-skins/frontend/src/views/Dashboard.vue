<template>
  <div class="dashboard">
    <!-- Welcome Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
      <p class="mt-1 text-sm text-gray-600">
        Visão geral do sistema de auditoria Iron Code Skins
      </p>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <!-- Evidências Hoje -->
      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">
                  Evidências Hoje
                </dt>
                <dd class="text-lg font-medium text-gray-900">
                  {{ stats.evidencesToday }}
                </dd>
              </dl>
            </div>
          </div>
        </div>
        <div class="bg-gray-50 px-5 py-3">
          <div class="text-sm">
            <router-link to="/audit" class="font-medium text-blue-600 hover:text-blue-500">
              Ver todas
            </router-link>
          </div>
        </div>
      </div>

      <!-- Verificações Blockchain -->
      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
              </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">
                  Verificações Blockchain
                </dt>
                <dd class="text-lg font-medium text-gray-900">
                  {{ stats.blockchainVerifications }}
                </dd>
              </dl>
            </div>
          </div>
        </div>
        <div class="bg-gray-50 px-5 py-3">
          <div class="text-sm">
            <a href="#" class="font-medium text-blue-600 hover:text-blue-500">
              Verificar integridade
            </a>
          </div>
        </div>
      </div>

      <!-- Transações Auditadas -->
      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
              </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">
                  Transações Auditadas
                </dt>
                <dd class="text-lg font-medium text-gray-900">
                  {{ stats.auditedTransactions }}
                </dd>
              </dl>
            </div>
          </div>
        </div>
        <div class="bg-gray-50 px-5 py-3">
          <div class="text-sm">
            <router-link to="/audit" class="font-medium text-blue-600 hover:text-blue-500">
              Iniciar auditoria
            </router-link>
          </div>
        </div>
      </div>

      <!-- Integridade do Sistema -->
      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div 
                :class="[
                  'h-6 w-6 rounded-full',
                  systemHealth === 'healthy' ? 'bg-green-500' : 
                  systemHealth === 'warning' ? 'bg-yellow-500' : 'bg-red-500'
                ]"
              ></div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">
                  Status do Sistema
                </dt>
                <dd class="text-lg font-medium text-gray-900">
                  {{ systemStatusText }}
                </dd>
              </dl>
            </div>
          </div>
        </div>
        <div class="bg-gray-50 px-5 py-3">
          <div class="text-sm">
            <a href="#" class="font-medium text-blue-600 hover:text-blue-500">
              Ver detalhes
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="mb-8">
      <h3 class="text-lg font-medium text-gray-900 mb-4">Ações Rápidas</h3>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Iniciar Nova Auditoria -->
        <router-link
          to="/audit"
          class="relative group bg-white p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-blue-500 rounded-lg shadow hover:shadow-md transition-shadow"
        >
          <div>
            <span class="rounded-lg inline-flex p-3 bg-blue-50 text-blue-600 ring-4 ring-white">
              <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
              </svg>
            </span>
          </div>
          <div class="mt-8">
            <h3 class="text-lg font-medium">
              <span class="absolute inset-0" aria-hidden="true"></span>
              Nova Auditoria
            </h3>
            <p class="mt-2 text-sm text-gray-500">
              Iniciar processo de auditoria para uma nova transação
            </p>
          </div>
        </router-link>

        <!-- Verificar Integridade -->
        <div class="relative group bg-white p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-green-500 rounded-lg shadow hover:shadow-md transition-shadow cursor-pointer"
             @click="startIntegrityCheck">
          <div>
            <span class="rounded-lg inline-flex p-3 bg-green-50 text-green-600 ring-4 ring-white">
              <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </span>
          </div>
          <div class="mt-8">
            <h3 class="text-lg font-medium">
              Verificar Integridade
            </h3>
            <p class="mt-2 text-sm text-gray-500">
              Validar integridade de evidências no blockchain
            </p>
          </div>
        </div>

        <!-- Relatórios -->
        <div class="relative group bg-white p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-purple-500 rounded-lg shadow hover:shadow-md transition-shadow cursor-pointer"
             @click="generateReport">
          <div>
            <span class="rounded-lg inline-flex p-3 bg-purple-50 text-purple-600 ring-4 ring-white">
              <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
            </span>
          </div>
          <div class="mt-8">
            <h3 class="text-lg font-medium">
              Gerar Relatório
            </h3>
            <p class="mt-2 text-sm text-gray-500">
              Criar relatório de auditoria e compliance
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white shadow rounded-lg">
      <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Atividade Recente</h3>
      </div>
      
      <div class="divide-y divide-gray-200">
        <div 
          v-for="activity in recentActivity" 
          :key="activity.id"
          class="px-6 py-4"
        >
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
              <div 
                :class="[
                  'w-8 h-8 rounded-full flex items-center justify-center',
                  activity.type === 'evidence' ? 'bg-green-100 text-green-600' :
                  activity.type === 'verification' ? 'bg-blue-100 text-blue-600' :
                  activity.type === 'audit' ? 'bg-purple-100 text-purple-600' :
                  'bg-gray-100 text-gray-600'
                ]"
              >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                  <path v-if="activity.type === 'evidence'" fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"/>
                  <path v-else-if="activity.type === 'verification'" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                  <path v-else fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/>
                </svg>
              </div>
              
              <div>
                <p class="text-sm font-medium text-gray-900">
                  {{ activity.description }}
                </p>
                <p class="text-xs text-gray-500">
                  {{ $formatRelativeDate(activity.timestamp) }}
                </p>
              </div>
            </div>
            
            <div class="text-sm text-gray-400">
              {{ activity.user }}
            </div>
          </div>
        </div>
      </div>
      
      <div v-if="recentActivity.length === 0" class="px-6 py-8 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma atividade</h3>
        <p class="mt-1 text-sm text-gray-500">Comece criando sua primeira auditoria.</p>
        <div class="mt-6">
          <router-link
            to="/audit"
            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
          >
            <svg class="-ml-1 mr-2 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"/>
            </svg>
            Nova Auditoria
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'

export default {
  name: 'Dashboard',
  setup() {
    // State
    const stats = ref({
      evidencesToday: 12,
      blockchainVerifications: 8,
      auditedTransactions: 24,
      totalEvidences: 156
    })
    
    const systemHealth = ref('healthy') // healthy, warning, error
    const recentActivity = ref([])

    // Computed
    const systemStatusText = computed(() => {
      const statusMap = {
        healthy: 'Saudável',
        warning: 'Atenção',
        error: 'Erro'
      }
      return statusMap[systemHealth.value] || 'Desconhecido'
    })

    // Methods
    async function loadDashboardData() {
      try {
        // Simular carregamento de dados
        await new Promise(resolve => setTimeout(resolve, 1000))
        
        // Mock data para atividade recente
        recentActivity.value = [
          {
            id: 1,
            type: 'evidence',
            description: 'Nova evidência de tela gravada para TX-12345',
            timestamp: new Date(Date.now() - 300000).toISOString(), // 5 min ago
            user: 'Admin'
          },
          {
            id: 2,
            type: 'verification',
            description: 'Integridade verificada no blockchain para arquivo IMG-001',
            timestamp: new Date(Date.now() - 900000).toISOString(), // 15 min ago
            user: 'Sistema'
          },
          {
            id: 3,
            type: 'audit',
            description: 'Auditoria iniciada para transação TX-12344',
            timestamp: new Date(Date.now() - 1800000).toISOString(), // 30 min ago
            user: 'Admin'
          }
        ]
        
        // Atualizar stats baseado em dados reais (mockado)
        stats.value = {
          evidencesToday: 12,
          blockchainVerifications: 8,
          auditedTransactions: 24,
          totalEvidences: 156
        }
        
      } catch (error) {
        console.error('Erro ao carregar dados do dashboard:', error)
      }
    }

    function startIntegrityCheck() {
      console.log('Iniciando verificação de integridade...')
      // Implementar verificação de integridade
    }

    function generateReport() {
      console.log('Gerando relatório...')
      // Implementar geração de relatório
    }

    // Lifecycle
    onMounted(() => {
      loadDashboardData()
      
      // Atualizar dados a cada 30 segundos
      setInterval(loadDashboardData, 30000)
    })

    return {
      // State
      stats,
      systemHealth,
      recentActivity,
      
      // Computed
      systemStatusText,
      
      // Methods
      loadDashboardData,
      startIntegrityCheck,
      generateReport
    }
  }
}
</script>

<style scoped>
/* Estilos específicos do dashboard usando Tailwind CSS */
</style>