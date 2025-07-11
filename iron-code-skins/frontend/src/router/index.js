import { createRouter, createWebHistory } from 'vue-router'

// Lazy loading dos componentes para otimizar performance
const Dashboard = () => import('../views/Dashboard.vue')
const AuditSystem = () => import('../views/AuditSystem.vue')
const EvidenceList = () => import('../views/EvidenceList.vue')
const BlockchainExplorer = () => import('../views/BlockchainExplorer.vue')
const TransactionDetails = () => import('../views/TransactionDetails.vue')
const Login = () => import('../views/auth/Login.vue')
const NotFound = () => import('../views/NotFound.vue')

const routes = [
  {
    path: '/',
    redirect: '/dashboard'
  },
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: {
      requiresAuth: false,
      title: 'Login - Iron Code Skins'
    }
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: Dashboard,
    meta: {
      requiresAuth: true,
      title: 'Dashboard - Iron Code Skins'
    }
  },
  {
    path: '/audit',
    name: 'AuditSystem',
    component: AuditSystem,
    meta: {
      requiresAuth: true,
      title: 'Sistema de Auditoria - Iron Code Skins'
    }
  },
  {
    path: '/evidence',
    name: 'EvidenceList',
    component: EvidenceList,
    meta: {
      requiresAuth: true,
      title: 'Evidências - Iron Code Skins'
    }
  },
  {
    path: '/blockchain',
    name: 'BlockchainExplorer',
    component: BlockchainExplorer,
    meta: {
      requiresAuth: true,
      title: 'Blockchain Explorer - Iron Code Skins'
    }
  },
  {
    path: '/transaction/:id',
    name: 'TransactionDetails',
    component: TransactionDetails,
    props: true,
    meta: {
      requiresAuth: true,
      title: 'Detalhes da Transação - Iron Code Skins'
    }
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: NotFound,
    meta: {
      requiresAuth: false,
      title: 'Página não encontrada - Iron Code Skins'
    }
  }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    } else {
      return { top: 0 }
    }
  }
})

// Navigation guard para autenticação
router.beforeEach((to, from, next) => {
  const isAuthenticated = localStorage.getItem('auth_token')
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth)

  // Atualizar título da página
  if (to.meta.title) {
    document.title = to.meta.title
  }

  if (requiresAuth && !isAuthenticated) {
    next('/login')
  } else if (to.path === '/login' && isAuthenticated) {
    next('/dashboard')
  } else {
    next()
  }
})

// Navigation guard para loading
router.beforeResolve((to, from, next) => {
  // Mostrar loading se navegando entre páginas principais
  const showLoading = to.name !== from.name && 
                     to.meta.requiresAuth && 
                     from.meta.requiresAuth

  if (showLoading) {
    // Aqui você pode disparar um evento de loading global
    // ou usar uma store para controlar o estado de loading
  }

  next()
})

export default router