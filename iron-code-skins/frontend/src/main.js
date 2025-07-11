import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'
import App from './App.vue'
import globalsPlugin from './plugins/globals'

// Estilos CSS
import './assets/css/main.css'

// Criar instância do Pinia para gerenciamento de estado
const pinia = createPinia()

// Criar aplicação Vue
const app = createApp(App)

// Configurar plugins
app.use(pinia)
app.use(router)
app.use(globalsPlugin)

// Configurações globais da aplicação
app.config.globalProperties.$appName = 'Iron Code Skins'
app.config.globalProperties.$version = '1.0.0'

// Diretivas globais personalizadas
app.directive('focus', {
  mounted(el) {
    el.focus()
  }
})

app.directive('click-outside', {
  beforeMount(el, binding) {
    el.clickOutsideEvent = (event) => {
      if (!(el === event.target || el.contains(event.target))) {
        binding.value(event)
      }
    }
    document.addEventListener('click', el.clickOutsideEvent)
  },
  unmounted(el) {
    document.removeEventListener('click', el.clickOutsideEvent)
  }
})

// Handler de erros global
app.config.errorHandler = (err, vm, info) => {
  console.error('Erro global capturado:', err, info)
  
  // Em produção, enviar erro para serviço de monitoramento
  if (import.meta.env.PROD) {
    // sendErrorToMonitoring(err, vm, info)
  }
}

// Montar aplicação
app.mount('#app')