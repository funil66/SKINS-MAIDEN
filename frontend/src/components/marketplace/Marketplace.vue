<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Marketplace</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
              Descubra e compre as melhores skins de CS2
            </p>
          </div>
          
          <!-- Search Bar -->
          <div class="mt-4 lg:mt-0 flex-1 max-w-lg lg:ml-8">
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
              </div>
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Buscar skins..."
                class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md leading-5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                @input="handleSearch"
              >
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex flex-col lg:flex-row gap-8">
        <!-- Sidebar Filters -->
        <div class="w-full lg:w-64 flex-shrink-0">
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Filtros</h3>
            
            <!-- Price Range -->
            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Faixa de Preço
              </label>
              <div class="flex space-x-2">
                <input
                  v-model="filters.minPrice"
                  type="number"
                  placeholder="Min"
                  class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                >
                <input
                  v-model="filters.maxPrice"
                  type="number"
                  placeholder="Max"
                  class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                >
              </div>
            </div>

            <!-- Weapon Type -->
            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Tipo de Arma
              </label>
              <select
                v-model="filters.weaponType"
                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
              >
                <option value="">Todas</option>
                <option value="rifle">Rifles</option>
                <option value="pistol">Pistolas</option>
                <option value="sniper">Snipers</option>
                <option value="shotgun">Shotguns</option>
                <option value="smg">SMGs</option>
                <option value="knife">Facas</option>
                <option value="gloves">Luvas</option>
              </select>
            </div>

            <!-- Rarity -->
            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Raridade
              </label>
              <div class="space-y-2">
                <label v-for="rarity in rarities" :key="rarity.value" class="flex items-center">
                  <input
                    v-model="filters.rarity"
                    :value="rarity.value"
                    type="checkbox"
                    class="h-4 w-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500"
                  >
                  <span class="ml-2 text-sm text-gray-700 dark:text-gray-300" :style="{ color: rarity.color }">
                    {{ rarity.label }}
                  </span>
                </label>
              </div>
            </div>

            <!-- Float Range -->
            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Float (Condição)
              </label>
              <select
                v-model="filters.condition"
                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
              >
                <option value="">Qualquer</option>
                <option value="fn">Factory New</option>
                <option value="mw">Minimal Wear</option>
                <option value="ft">Field-Tested</option>
                <option value="ww">Well-Worn</option>
                <option value="bs">Battle-Scarred</option>
              </select>
            </div>

            <!-- Apply Filters Button -->
            <button
              @click="applyFilters"
              class="w-full bg-purple-600 text-white py-2 px-4 rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 transition duration-200"
            >
              Aplicar Filtros
            </button>

            <!-- Clear Filters -->
            <button
              @click="clearFilters"
              class="w-full mt-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 py-2 px-4 rounded-md hover:bg-gray-400 dark:hover:bg-gray-500 focus:outline-none transition duration-200"
            >
              Limpar Filtros
            </button>
          </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1">
          <!-- Sort and View Options -->
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
            <div class="flex items-center space-x-4">
              <span class="text-sm text-gray-700 dark:text-gray-300">
                {{ filteredSkins.length }} itens encontrados
              </span>
            </div>
            
            <div class="flex items-center space-x-4 mt-4 sm:mt-0">
              <!-- Sort Options -->
              <select
                v-model="sortBy"
                class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                @change="handleSort"
              >
                <option value="price_asc">Preço: Menor para Maior</option>
                <option value="price_desc">Preço: Maior para Menor</option>
                <option value="name_asc">Nome: A-Z</option>
                <option value="name_desc">Nome: Z-A</option>
                <option value="float_asc">Float: Menor para Maior</option>
                <option value="float_desc">Float: Maior para Menor</option>
              </select>

              <!-- View Toggle -->
              <div class="flex border border-gray-300 dark:border-gray-600 rounded-md">
                <button
                  @click="viewMode = 'grid'"
                  :class="[
                    'px-3 py-2 text-sm',
                    viewMode === 'grid'
                      ? 'bg-purple-600 text-white'
                      : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600'
                  ]"
                >
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                  </svg>
                </button>
                <button
                  @click="viewMode = 'list'"
                  :class="[
                    'px-3 py-2 text-sm',
                    viewMode === 'list'
                      ? 'bg-purple-600 text-white'
                      : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600'
                  ]"
                >
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                  </svg>
                </button>
              </div>
            </div>
          </div>

          <!-- Loading State -->
          <div v-if="loading" class="flex justify-center items-center h-64">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-600"></div>
          </div>

          <!-- Grid View -->
          <div v-else-if="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <div
              v-for="skin in paginatedSkins"
              :key="skin.id"
              class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition duration-200 cursor-pointer"
              @click="viewSkinDetails(skin)"
            >
              <div class="aspect-w-1 aspect-h-1 w-full">
                <img
                  :src="skin.image"
                  :alt="skin.name"
                  class="w-full h-48 object-cover"
                >
              </div>
              <div class="p-4">
                <h3 class="text-sm font-medium text-gray-900 dark:text-white truncate">
                  {{ skin.name }}
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  {{ skin.condition }} | Float: {{ skin.float }}
                </p>
                <div class="mt-2 flex items-center justify-between">
                  <span class="text-lg font-bold text-purple-600 dark:text-purple-400">
                    R$ {{ formatPrice(skin.price) }}
                  </span>
                  <button
                    @click.stop="addToCart(skin)"
                    class="bg-purple-600 text-white px-3 py-1 rounded text-xs hover:bg-purple-700 transition duration-200"
                  >
                    Comprar
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- List View -->
          <div v-else class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
              <div
                v-for="skin in paginatedSkins"
                :key="skin.id"
                class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition duration-200"
                @click="viewSkinDetails(skin)"
              >
                <div class="flex items-center space-x-4">
                  <img
                    :src="skin.image"
                    :alt="skin.name"
                    class="w-16 h-16 object-cover rounded"
                  >
                  <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white truncate">
                      {{ skin.name }}
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                      {{ skin.condition }} | Float: {{ skin.float }}
                    </p>
                  </div>
                  <div class="flex items-center space-x-4">
                    <span class="text-lg font-bold text-purple-600 dark:text-purple-400">
                      R$ {{ formatPrice(skin.price) }}
                    </span>
                    <button
                      @click.stop="addToCart(skin)"
                      class="bg-purple-600 text-white px-4 py-2 rounded text-sm hover:bg-purple-700 transition duration-200"
                    >
                      Comprar
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="totalPages > 1" class="mt-8 flex justify-center">
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
              <button
                @click="currentPage = Math.max(1, currentPage - 1)"
                :disabled="currentPage === 1"
                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50"
              >
                ←
              </button>
              
              <button
                v-for="page in visiblePages"
                :key="page"
                @click="currentPage = page"
                :class="[
                  'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                  page === currentPage
                    ? 'z-10 bg-purple-50 dark:bg-purple-900 border-purple-500 text-purple-600 dark:text-purple-400'
                    : 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700'
                ]"
              >
                {{ page }}
              </button>
              
              <button
                @click="currentPage = Math.min(totalPages, currentPage + 1)"
                :disabled="currentPage === totalPages"
                class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50"
              >
                →
              </button>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

export default {
  name: 'Marketplace',
  setup() {
    const router = useRouter()
    const loading = ref(false)
    const searchQuery = ref('')
    const viewMode = ref('grid')
    const sortBy = ref('price_asc')
    const currentPage = ref(1)
    const itemsPerPage = 20

    const filters = reactive({
      minPrice: '',
      maxPrice: '',
      weaponType: '',
      rarity: [],
      condition: ''
    })

    const rarities = [
      { value: 'consumer', label: 'Consumer Grade', color: '#b0c3d9' },
      { value: 'industrial', label: 'Industrial Grade', color: '#5e98d9' },
      { value: 'milspec', label: 'Mil-Spec', color: '#4b69ff' },
      { value: 'restricted', label: 'Restricted', color: '#8847ff' },
      { value: 'classified', label: 'Classified', color: '#d32ce6' },
      { value: 'covert', label: 'Covert', color: '#eb4b4b' },
      { value: 'contraband', label: 'Contraband', color: '#e4ae39' }
    ]

    // Mock data - replace with API call
    const skins = ref([
      {
        id: 1,
        name: 'AK-47 | Redline',
        condition: 'Field-Tested',
        float: 0.25,
        price: 150.00,
        image: '/images/ak47-redline.jpg',
        weaponType: 'rifle',
        rarity: 'classified'
      },
      {
        id: 2,
        name: 'AWP | Dragon Lore',
        condition: 'Factory New',
        float: 0.01,
        price: 2500.00,
        image: '/images/awp-dragonlore.jpg',
        weaponType: 'sniper',
        rarity: 'covert'
      },
      // Add more mock items...
    ])

    const filteredSkins = computed(() => {
      let result = skins.value

      // Search filter
      if (searchQuery.value) {
        result = result.filter(skin =>
          skin.name.toLowerCase().includes(searchQuery.value.toLowerCase())
        )
      }

      // Price filter
      if (filters.minPrice) {
        result = result.filter(skin => skin.price >= parseFloat(filters.minPrice))
      }
      if (filters.maxPrice) {
        result = result.filter(skin => skin.price <= parseFloat(filters.maxPrice))
      }

      // Weapon type filter
      if (filters.weaponType) {
        result = result.filter(skin => skin.weaponType === filters.weaponType)
      }

      // Rarity filter
      if (filters.rarity.length > 0) {
        result = result.filter(skin => filters.rarity.includes(skin.rarity))
      }

      // Sort
      result.sort((a, b) => {
        switch (sortBy.value) {
          case 'price_asc':
            return a.price - b.price
          case 'price_desc':
            return b.price - a.price
          case 'name_asc':
            return a.name.localeCompare(b.name)
          case 'name_desc':
            return b.name.localeCompare(a.name)
          case 'float_asc':
            return a.float - b.float
          case 'float_desc':
            return b.float - a.float
          default:
            return 0
        }
      })

      return result
    })

    const totalPages = computed(() => {
      return Math.ceil(filteredSkins.value.length / itemsPerPage)
    })

    const paginatedSkins = computed(() => {
      const start = (currentPage.value - 1) * itemsPerPage
      const end = start + itemsPerPage
      return filteredSkins.value.slice(start, end)
    })

    const visiblePages = computed(() => {
      const pages = []
      const total = totalPages.value
      const current = currentPage.value
      
      if (total <= 7) {
        for (let i = 1; i <= total; i++) {
          pages.push(i)
        }
      } else {
        if (current <= 4) {
          for (let i = 1; i <= 5; i++) {
            pages.push(i)
          }
          pages.push('...')
          pages.push(total)
        } else if (current >= total - 3) {
          pages.push(1)
          pages.push('...')
          for (let i = total - 4; i <= total; i++) {
            pages.push(i)
          }
        } else {
          pages.push(1)
          pages.push('...')
          for (let i = current - 2; i <= current + 2; i++) {
            pages.push(i)
          }
          pages.push('...')
          pages.push(total)
        }
      }
      
      return pages
    })

    const handleSearch = () => {
      currentPage.value = 1
    }

    const handleSort = () => {
      currentPage.value = 1
    }

    const applyFilters = () => {
      currentPage.value = 1
    }

    const clearFilters = () => {
      Object.keys(filters).forEach(key => {
        if (Array.isArray(filters[key])) {
          filters[key] = []
        } else {
          filters[key] = ''
        }
      })
      currentPage.value = 1
    }

    const formatPrice = (price) => {
      return new Intl.NumberFormat('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      }).format(price)
    }

    const viewSkinDetails = (skin) => {
      router.push(`/marketplace/item/${skin.id}`)
    }

    const addToCart = (skin) => {
      // Add to cart logic
      console.log('Added to cart:', skin)
    }

    onMounted(() => {
      // Load marketplace data
      loading.value = true
      setTimeout(() => {
        loading.value = false
      }, 1000)
    })

    return {
      loading,
      searchQuery,
      viewMode,
      sortBy,
      currentPage,
      filters,
      rarities,
      filteredSkins,
      paginatedSkins,
      totalPages,
      visiblePages,
      handleSearch,
      handleSort,
      applyFilters,
      clearFilters,
      formatPrice,
      viewSkinDetails,
      addToCart
    }
  }
}
</script>
