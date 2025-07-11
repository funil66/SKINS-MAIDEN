<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 via-purple-900 to-violet-900 flex items-center justify-center px-4">
    <div class="max-w-md w-full space-y-8">
      <div class="text-center">
        <img src="/logo.svg" alt="Iron Code Skins" class="mx-auto h-16 w-auto">
        <h2 class="mt-6 text-3xl font-extrabold text-white">
          Crie sua conta
        </h2>
        <p class="mt-2 text-sm text-gray-300">
          Já tem uma conta? 
          <router-link to="/login" class="font-medium text-purple-400 hover:text-purple-300">
            Faça login aqui
          </router-link>
        </p>
      </div>
      
      <form @submit.prevent="handleRegister" class="mt-8 space-y-6">
        <div class="space-y-4">
          <div>
            <label for="name" class="sr-only">Nome completo</label>
            <input
              id="name"
              v-model="form.name"
              name="name"
              type="text"
              required
              class="appearance-none rounded-lg relative block w-full px-3 py-3 bg-gray-800 border border-gray-700 placeholder-gray-400 text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
              placeholder="Nome completo"
            >
          </div>
          <div>
            <label for="email" class="sr-only">Email</label>
            <input
              id="email"
              v-model="form.email"
              name="email"
              type="email"
              required
              class="appearance-none rounded-lg relative block w-full px-3 py-3 bg-gray-800 border border-gray-700 placeholder-gray-400 text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
              placeholder="Seu email"
            >
          </div>
          <div>
            <label for="password" class="sr-only">Senha</label>
            <input
              id="password"
              v-model="form.password"
              name="password"
              type="password"
              required
              class="appearance-none rounded-lg relative block w-full px-3 py-3 bg-gray-800 border border-gray-700 placeholder-gray-400 text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
              placeholder="Sua senha (min. 8 caracteres)"
            >
          </div>
          <div>
            <label for="password_confirmation" class="sr-only">Confirmar senha</label>
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              name="password_confirmation"
              type="password"
              required
              class="appearance-none rounded-lg relative block w-full px-3 py-3 bg-gray-800 border border-gray-700 placeholder-gray-400 text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
              placeholder="Confirme sua senha"
            >
          </div>
          <div>
            <label for="phone" class="sr-only">Telefone</label>
            <input
              id="phone"
              v-model="form.phone"
              name="phone"
              type="tel"
              class="appearance-none rounded-lg relative block w-full px-3 py-3 bg-gray-800 border border-gray-700 placeholder-gray-400 text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
              placeholder="Telefone (opcional)"
            >
          </div>
        </div>

        <div class="flex items-start">
          <div class="flex items-center h-5">
            <input
              id="terms"
              v-model="form.acceptTerms"
              name="terms"
              type="checkbox"
              required
              class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded"
            >
          </div>
          <div class="ml-3 text-sm">
            <label for="terms" class="text-gray-300">
              Eu aceito os 
              <router-link to="/terms" class="text-purple-400 hover:text-purple-300">
                Termos de Uso
              </router-link>
              e a 
              <router-link to="/privacy" class="text-purple-400 hover:text-purple-300">
                Política de Privacidade
              </router-link>
            </label>
          </div>
        </div>

        <div class="flex items-start">
          <div class="flex items-center h-5">
            <input
              id="newsletter"
              v-model="form.newsletter"
              name="newsletter"
              type="checkbox"
              class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded"
            >
          </div>
          <div class="ml-3 text-sm">
            <label for="newsletter" class="text-gray-300">
              Quero receber ofertas exclusivas e atualizações por email
            </label>
          </div>
        </div>

        <div>
          <button
            type="submit"
            :disabled="loading || !form.acceptTerms"
            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 disabled:opacity-50 disabled:cursor-not-allowed transition duration-200"
          >
            <span v-if="loading" class="absolute left-0 inset-y-0 flex items-center pl-3">
              <svg class="animate-spin h-5 w-5 text-purple-300" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </span>
            {{ loading ? 'Criando conta...' : 'Criar conta' }}
          </button>
        </div>

        <div class="mt-6">
          <div class="relative">
            <div class="absolute inset-0 flex items-center">
              <div class="w-full border-t border-gray-600"></div>
            </div>
            <div class="relative flex justify-center text-sm">
              <span class="px-2 bg-gray-900 text-gray-400">Ou registre-se com</span>
            </div>
          </div>

          <div class="mt-6">
            <button
              @click="registerWithSteam"
              type="button"
              class="w-full inline-flex justify-center py-3 px-4 border border-gray-600 rounded-lg shadow-sm bg-gray-800 text-sm font-medium text-white hover:bg-gray-700 transition duration-200"
            >
              <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10c5.52 0 10-4.48 10-10S17.52 2 12 2zm-1.53 13.78c-.83 0-1.51-.34-2.05-.95-.54-.61-.81-1.42-.81-2.42s.27-1.81.81-2.42c.54-.61 1.22-.95 2.05-.95.42 0 .8.08 1.14.23.34.15.63.37.87.64l-.87.89c-.32-.35-.71-.52-1.17-.52-.42 0-.77.15-1.05.44-.28.29-.42.7-.42 1.22s.14.93.42 1.22c.28.29.63.44 1.05.44.46 0 .85-.17 1.17-.52l.87.89c-.24.27-.53.49-.87.64-.34.15-.72.23-1.14.23zm3.06-.55v-3.46h1.19v3.46h-1.19z"/>
              </svg>
              Registrar com Steam
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

export default {
  name: 'RegisterForm',
  setup() {
    const router = useRouter()
    const authStore = useAuthStore()
    const loading = ref(false)
    
    const form = reactive({
      name: '',
      email: '',
      password: '',
      password_confirmation: '',
      phone: '',
      acceptTerms: false,
      newsletter: false
    })

    const handleRegister = async () => {
      if (form.password !== form.password_confirmation) {
        alert('As senhas não coincidem!')
        return
      }

      loading.value = true
      try {
        await authStore.register(form)
        router.push('/verify-email')
      } catch (error) {
        console.error('Registration failed:', error)
        // Show error notification
      } finally {
        loading.value = false
      }
    }

    const registerWithSteam = () => {
      window.location.href = '/api/auth/steam/register'
    }

    return {
      form,
      loading,
      handleRegister,
      registerWithSteam
    }
  }
}
</script>
