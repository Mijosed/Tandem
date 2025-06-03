import { useAuthStore } from '@/stores/auth'

export default defineNuxtRouteMiddleware(async (to) => {
  // Ne pas exécuter le middleware côté serveur
  if (!process.client) {
    return
  }

  const authStore = useAuthStore()

  // Pages qui ne nécessitent pas d'authentification
  const publicPages = ['/login', '/register', '/']
  const isDashboardRoute = to.path.startsWith('/dashboard')
  const authRequired = !publicPages.includes(to.path) || isDashboardRoute

  // Si un token existe mais qu'on n'a pas les infos utilisateur, on les récupère
  if (authStore.token && !authStore.user) {
    try {
      await authStore.fetchUser()
    } catch (error) {
      console.error('Failed to fetch user:', error)
      // Si on ne peut pas récupérer les infos utilisateur, on déconnecte
      authStore.logout()
      return navigateTo('/login')
    }
  }

  // Si la page nécessite une authentification et qu'on n'est pas authentifié
  if (authRequired && !authStore.isAuthenticated) {
    console.log('Not authenticated, redirecting to login')
    return navigateTo('/login')
  }

  // Rediriger vers la page d'accueil si l'utilisateur est déjà connecté et essaie d'accéder à login/register
  if (!isDashboardRoute && !authRequired && authStore.isAuthenticated && to.path !== '/') {
    return navigateTo('/')
  }
})
