import { ref, computed } from 'vue'

interface DashboardStats {
  applications: number
  upcomingInterviews: number
  pendingResponses: number
  responseRate: number
  activeApplications: number
  rejectedApplications: number
  messages: number
}

interface UserProfile {
  id: number
  email: string
  firstName: string
  lastName: string
  fullName: string
  roles: string[]
  subscription: {
    plan: string
    status: string
    isPremium: boolean
  }
}

export const useDashboard = () => {
  const loading = ref(false)
  const error = ref('')
  const user = ref<UserProfile | null>(null)
  const stats = ref<DashboardStats>({
    applications: 0,
    upcomingInterviews: 0,
    pendingResponses: 0,
    responseRate: 0,
    activeApplications: 0,
    rejectedApplications: 0,
    messages: 0
  })
  const applications = ref([])
  const notifications = ref([])
  const jobs = ref([])

  const config = useRuntimeConfig()

  // Récupérer les données utilisateur depuis le localStorage ou l'API
  const fetchUserProfile = async () => {
    try {
      loading.value = true
      
      // Pour l'instant, on utilise des données de test
      // Dans une vraie app, on récupérerait les données depuis l'API avec un token
      const storedUser = localStorage.getItem('user')
      if (storedUser) {
        user.value = JSON.parse(storedUser)
      } else {
        // Données de test par défaut
        user.value = {
          id: 1,
          email: 'admin@tandem.com',
          firstName: 'Admin',
          lastName: 'Tandem',
          fullName: 'Admin Tandem',
          roles: ['ROLE_ADMIN', 'ROLE_USER'],
          subscription: {
            plan: 'premium',
            status: 'active',
            isPremium: true
          }
        }
      }
    } catch (err: any) {
      error.value = err.message || 'Erreur lors de la récupération du profil utilisateur'
    } finally {
      loading.value = false
    }
  }

  // Récupérer les candidatures
  const fetchApplications = async () => {
    try {
      // Récupérer l'utilisateur connecté
      const userData = typeof window !== 'undefined' ? JSON.parse(localStorage.getItem('user') || '{}') : {}
      const userId = userData.id || 4 // Fallback pour les tests
      
      const response = await fetch(`http://localhost:8888/api/candidatures/user/${userId}`)
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`)
      }
      const data = await response.json()
      applications.value = data.member || []
      
      // Calculer les statistiques des candidatures
      const totalApplications = applications.value.length
      const pending = applications.value.filter((app: any) => app.statut === 'en_attente').length
      const active = applications.value.filter((app: any) => app.statut === 'entretien').length
      const rejected = applications.value.filter((app: any) => app.statut === 'refuse').length
      const accepted = applications.value.filter((app: any) => app.statut === 'accepte').length
      
      stats.value.applications = totalApplications
      stats.value.pendingResponses = pending
      stats.value.activeApplications = active
      stats.value.rejectedApplications = rejected
      stats.value.responseRate = totalApplications > 0 ? Math.round(((active + accepted) / totalApplications) * 100) : 0

    } catch (err: any) {
      console.error('Erreur lors de la récupération des candidatures:', err)
      error.value = err.message || 'Erreur lors de la récupération des candidatures'
    }
  }

  // Récupérer les notifications
  const fetchNotifications = async () => {
    try {
      const response = await fetch(`http://localhost:8888/api/notifications`)
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`)
      }
      const data = await response.json()
      notifications.value = data.member || []
      
      // Compter les messages non lus
      const unreadMessages = notifications.value.filter((notif: any) => !notif.isRead).length
      stats.value.messages = unreadMessages

    } catch (err: any) {
      console.error('Erreur lors de la récupération des notifications:', err)
      error.value = err.message || 'Erreur lors de la récupération des notifications'
    }
  }

  // Récupérer les offres d'emploi
  const fetchJobs = async () => {
    try {
      const response = await fetch(`http://localhost:8888/api/jobs`)
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`)
      }
      const data = await response.json()
      jobs.value = data.member || []

    } catch (err: any) {
      console.error('Erreur lors de la récupération des emplois:', err)
      error.value = err.message || 'Erreur lors de la récupération des emplois'
    }
  }

  // Récupérer toutes les données du dashboard
  const fetchDashboardData = async () => {
    loading.value = true
    error.value = ''
    
    try {
      await Promise.all([
        fetchUserProfile(),
        fetchApplications(),
        fetchNotifications(),
        fetchJobs()
      ])
      
      // Calculer les entretiens à venir (simulation basée sur les candidatures actives)
      stats.value.upcomingInterviews = Math.floor(stats.value.activeApplications * 0.4)
      
    } catch (err: any) {
      error.value = err.message || 'Erreur lors du chargement des données'
    } finally {
      loading.value = false
    }
  }

  // Données calculées
  const recentApplications = computed(() => {
    return applications.value
      .slice(0, 5)
      .sort((a: any, b: any) => new Date(b.dateCreation || b.dateDepot).getTime() - new Date(a.dateCreation || a.dateDepot).getTime())
  })

  const recentNotifications = computed(() => {
    return notifications.value
      .filter((notif: any) => !notif.isRead)
      .slice(0, 5)
      .sort((a: any, b: any) => new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime())
  })

  const featuredJobs = computed(() => {
    return jobs.value.slice(0, 3)
  })

  // Sauvegarder les données utilisateur
  const saveUserProfile = (userData: UserProfile) => {
    user.value = userData
    localStorage.setItem('user', JSON.stringify(userData))
  }

  return {
    // État
    loading,
    error,
    user,
    stats,
    applications,
    notifications,
    jobs,
    
    // Données calculées
    recentApplications,
    recentNotifications,
    featuredJobs,
    
    // Actions
    fetchDashboardData,
    fetchUserProfile,
    fetchApplications,
    fetchNotifications,
    fetchJobs,
    saveUserProfile
  }
}
