<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-600"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-300 px-4 py-3 rounded">
        {{ error }}
      </div>

      <!-- Skin Details -->
      <div v-else-if="skin" class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">
        <!-- Navigation -->
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
          <button
            @click="$router.go(-1)"
            class="flex items-center text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Voltar ao Marketplace
          </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 p-6">
          <!-- Image Gallery -->
          <div class="space-y-4">
            <div class="aspect-w-1 aspect-h-1 bg-gray-200 dark:bg-gray-700 rounded-lg overflow-hidden">
              <img
                :src="skin.image"
                :alt="skin.name"
                class="w-full h-96 object-cover"
              >
            </div>
            
            <!-- Additional Images -->
            <div v-if="skin.gallery && skin.gallery.length > 0" class="grid grid-cols-4 gap-2">
              <div
                v-for="(image, index) in skin.gallery"
                :key="index"
                class="aspect-w-1 aspect-h-1 bg-gray-200 dark:bg-gray-700 rounded overflow-hidden cursor-pointer hover:opacity-80"
                @click="skin.image = image"
              >
                <img
                  :src="image"
                  :alt="`${skin.name} view ${index + 1}`"
                  class="w-full h-20 object-cover"
                >
              </div>
            </div>
          </div>

          <!-- Details -->
          <div class="space-y-6">
            <!-- Title and Price -->
            <div>
              <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ skin.name }}</h1>
              <div class="mt-2 flex items-center space-x-4">
                <span class="text-3xl font-bold text-purple-600 dark:text-purple-400">
                  R$ {{ formatPrice(skin.price) }}
                </span>
                <span
                  class="px-3 py-1 rounded-full text-sm font-medium"
                  :style="{ backgroundColor: getRarityColor(skin.rarity), color: 'white' }"
                >
                  {{ getRarityLabel(skin.rarity) }}
                </span>
              </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 gap-4">
              <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                <div class="text-sm text-gray-600 dark:text-gray-400">Condição</div>
                <div class="text-lg font-semibold text-gray-900 dark:text-white">{{ skin.condition }}</div>
              </div>
              <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                <div class="text-sm text-gray-600 dark:text-gray-400">Float</div>
                <div class="text-lg font-semibold text-gray-900 dark:text-white">{{ skin.float }}</div>
              </div>
              <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                <div class="text-sm text-gray-600 dark:text-gray-400">Tipo</div>
                <div class="text-lg font-semibold text-gray-900 dark:text-white">{{ getWeaponTypeLabel(skin.weaponType) }}</div>
              </div>
              <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                <div class="text-sm text-gray-600 dark:text-gray-400">Padrão</div>
                <div class="text-lg font-semibold text-gray-900 dark:text-white">#{{ skin.pattern || 'N/A' }}</div>
              </div>
            </div>

            <!-- Description -->
            <div v-if="skin.description">
              <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Descrição</h3>
              <p class="text-gray-600 dark:text-gray-400">{{ skin.description }}</p>
            </div>

            <!-- Seller Info -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
              <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Vendedor</h3>
              <div class="flex items-center space-x-4">
                <img
                  :src="skin.seller.avatar"
                  :alt="skin.seller.name"
                  class="w-12 h-12 rounded-full"
                >
                <div>
                  <div class="font-medium text-gray-900 dark:text-white">{{ skin.seller.name }}</div>
                  <div class="text-sm text-gray-600 dark:text-gray-400">
                    ⭐ {{ skin.seller.rating }} ({{ skin.seller.reviews }} avaliações)
                  </div>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-3">
              <button
                @click="buyNow"
                :disabled="purchasing"
                class="w-full bg-purple-600 text-white py-3 px-6 rounded-lg font-medium hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 transition duration-200 disabled:opacity-50"
              >
                <span v-if="purchasing">Processando...</span>
                <span v-else>Comprar Agora</span>
              </button>
              
              <button
                @click="addToCart"
                class="w-full bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 py-3 px-6 rounded-lg font-medium hover:bg-gray-400 dark:hover:bg-gray-500 focus:outline-none transition duration-200"
              >
                Adicionar ao Carrinho
              </button>
              
              <button
                @click="addToWishlist"
                class="w-full border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 py-3 px-6 rounded-lg font-medium hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none transition duration-200"
              >
                ♡ Adicionar aos Favoritos
              </button>
            </div>

            <!-- Safety Notice -->
            <div class="bg-blue-50 dark:bg-blue-900 p-4 rounded-lg">
              <div class="flex">
                <svg class="flex-shrink-0 h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div class="ml-3">
                  <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                    Transação Segura
                  </h3>
                  <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                    <p>Sua compra é protegida pelo nosso sistema de escrow. O item só será transferido após a confirmação do pagamento.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Similar Items -->
        <div v-if="similarItems.length > 0" class="border-t border-gray-200 dark:border-gray-700 p-6">
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">Itens Similares</h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div
              v-for="item in similarItems"
              :key="item.id"
              class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer transition duration-200"
              @click="$router.push(`/marketplace/item/${item.id}`)"
            >
              <img
                :src="item.image"
                :alt="item.name"
                class="w-full h-32 object-cover rounded mb-3"
              >
              <h4 class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ item.name }}</h4>
              <p class="text-lg font-bold text-purple-600 dark:text-purple-400 mt-1">
                R$ {{ formatPrice(item.price) }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

export default {
  name: 'SkinDetails',
  setup() {
    const route = useRoute()
    const router = useRouter()
    const loading = ref(true)
    const error = ref(null)
    const purchasing = ref(false)
    const skin = ref(null)
    const similarItems = ref([])

    const rarities = {
      consumer: { label: 'Consumer Grade', color: '#b0c3d9' },
      industrial: { label: 'Industrial Grade', color: '#5e98d9' },
      milspec: { label: 'Mil-Spec', color: '#4b69ff' },
      restricted: { label: 'Restricted', color: '#8847ff' },
      classified: { label: 'Classified', color: '#d32ce6' },
      covert: { label: 'Covert', color: '#eb4b4b' },
      contraband: { label: 'Contraband', color: '#e4ae39' }
    }

    const weaponTypes = {
      rifle: 'Rifle',
      pistol: 'Pistola',
      sniper: 'Sniper',
      shotgun: 'Shotgun',
      smg: 'SMG',
      knife: 'Faca',
      gloves: 'Luvas'
    }

    const loadSkinDetails = async () => {
      try {
        loading.value = true
        const skinId = route.params.id

        // Mock API call - replace with actual API
        await new Promise(resolve => setTimeout(resolve, 1000))

        // Mock data
        skin.value = {
          id: skinId,
          name: 'AK-47 | Redline',
          condition: 'Field-Tested',
          float: 0.25,
          price: 150.00,
          image: '/images/ak47-redline.jpg',
          weaponType: 'rifle',
          rarity: 'classified',
          pattern: '592',
          description: 'Esta AK-47 possui um design limpo e minimalista com detalhes em vermelho que destacam suas linhas agressivas.',
          gallery: [
            '/images/ak47-redline-1.jpg',
            '/images/ak47-redline-2.jpg',
            '/images/ak47-redline-3.jpg'
          ],
          seller: {
            name: 'SkinTrader123',
            avatar: '/images/default-avatar.jpg',
            rating: 4.8,
            reviews: 234
          }
        }

        // Load similar items
        similarItems.value = [
          {
            id: 2,
            name: 'AK-47 | Bloodsport',
            price: 180.00,
            image: '/images/ak47-bloodsport.jpg'
          },
          {
            id: 3,
            name: 'AK-47 | Neon Rider',
            price: 95.00,
            image: '/images/ak47-neonrider.jpg'
          },
          {
            id: 4,
            name: 'AK-47 | Vulcan',
            price: 320.00,
            image: '/images/ak47-vulcan.jpg'
          },
          {
            id: 5,
            name: 'AK-47 | Fuel Injector',
            price: 85.00,
            image: '/images/ak47-fuelinjector.jpg'
          }
        ]

      } catch (err) {
        error.value = 'Erro ao carregar detalhes do item'
        console.error('Error loading skin details:', err)
      } finally {
        loading.value = false
      }
    }

    const formatPrice = (price) => {
      return new Intl.NumberFormat('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      }).format(price)
    }

    const getRarityColor = (rarity) => {
      return rarities[rarity]?.color || '#gray'
    }

    const getRarityLabel = (rarity) => {
      return rarities[rarity]?.label || rarity
    }

    const getWeaponTypeLabel = (weaponType) => {
      return weaponTypes[weaponType] || weaponType
    }

    const buyNow = async () => {
      try {
        purchasing.value = true
        
        // Mock purchase process
        await new Promise(resolve => setTimeout(resolve, 2000))
        
        // Redirect to purchase confirmation
        router.push(`/purchase/confirm/${skin.value.id}`)
        
      } catch (err) {
        console.error('Purchase error:', err)
        alert('Erro ao processar compra. Tente novamente.')
      } finally {
        purchasing.value = false
      }
    }

    const addToCart = () => {
      // Add to cart logic
      console.log('Added to cart:', skin.value)
      // Show success message
      alert('Item adicionado ao carrinho!')
    }

    const addToWishlist = () => {
      // Add to wishlist logic
      console.log('Added to wishlist:', skin.value)
      alert('Item adicionado aos favoritos!')
    }

    onMounted(() => {
      loadSkinDetails()
    })

    return {
      loading,
      error,
      purchasing,
      skin,
      similarItems,
      formatPrice,
      getRarityColor,
      getRarityLabel,
      getWeaponTypeLabel,
      buyNow,
      addToCart,
      addToWishlist
    }
  }
}
</script>
