import { useAuthStore } from '~/stores/auth'

export default defineNuxtPlugin(async (nuxtApp) => {
  // Initialisation de l'authentification côté client
  if (process.client) {
    const authStore = useAuthStore()
    const token = localStorage.getItem('auth-token')
    
    if (token) {
      console.log('Auth plugin: Found token, trying to restore session')
      await authStore.restoreSession()
    }
  }

  // Configuration du middleware global
  addRouteMiddleware('global-auth', async (to) => {
    if (!process.client) return

    const authStore = useAuthStore()
    const isAuthRoute = ['/login', '/register'].includes(to.path)
    const isDashboardRoute = to.path.startsWith('/dashboard')
    
    // Si l'utilisateur a un token mais pas de données utilisateur, essayer de les récupérer
    if (authStore.token && !authStore.user) {
      try {
        await authStore.fetchUser()
      } catch (error) {
        console.error('Failed to fetch user:', error)
        authStore.logout()
        if (!isAuthRoute) {
          return navigateTo('/login')
        }
      }
    }

    // Redirection basée sur l'état d'authentification
    const isFullyAuthenticated = authStore.isAuthenticated && authStore.user

    // Pour les routes d'authentification (login/register)
    if (isAuthRoute) {
      if (isFullyAuthenticated) {
        console.log('Redirecting authenticated user from auth route to dashboard')
        return navigateTo('/dashboard')
      }
      return
    }

    // Pour les routes du dashboard
    if (isDashboardRoute) {
      if (!isFullyAuthenticated) {
        console.log('Unauthenticated user trying to access dashboard, redirecting to login')
        return navigateTo('/login')
      }
      return
    }

    // Pour les routes publiques, pas de redirection
    return
  }, { global: true })
})
