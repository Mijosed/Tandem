import { ref, computed, readonly } from 'vue'

interface User {
  id: number
  email: string
  firstName: string
  lastName: string
  roles: string[]
  subscription?: {
    plan: string
    status: string
    isPremium: boolean
  }
}

const currentUser = ref<User | null>(null)
const isLoading = ref(false)

export const useAuth = () => {
  
  const loadUserFromStorage = () => {
    if (typeof window !== 'undefined') {
      const storedUser = localStorage.getItem('user')
      if (storedUser) {
        try {
          currentUser.value = JSON.parse(storedUser)
        } catch (error) {
          console.error('Erreur parsing user:', error)
          localStorage.removeItem('user')
        }
      } else {
        currentUser.value = {
          id: 1,
          email: 'admin@tandem.com',
          firstName: 'Admin',
          lastName: 'Tandem',
          roles: ['ROLE_ADMIN', 'ROLE_USER'],
          subscription: {
            plan: 'premium',
            status: 'active',
            isPremium: true
          }
        }
      }
    }
  }

  const saveUser = (user: User) => {
    if (typeof window !== 'undefined') {
      localStorage.setItem('user', JSON.stringify(user))
    }
    currentUser.value = user
  }

  const login = (user: User) => {
    saveUser(user)
  }

  const logout = () => {
    currentUser.value = null
    if (typeof window !== 'undefined') {
      localStorage.removeItem('user')
    }
  }

  if (!currentUser.value && typeof window !== 'undefined') {
    loadUserFromStorage()
  }

  const isAuthenticated = computed(() => !!currentUser.value)
  
  const userFullName = computed(() => {
    if (!currentUser.value) return 'Utilisateur'
    const firstName = currentUser.value.firstName || ''
    const lastName = currentUser.value.lastName || ''
    return `${firstName} ${lastName}`.trim() || currentUser.value.email
  })

  const userInitials = computed(() => {
    if (!currentUser.value) return 'U'
    const first = currentUser.value.firstName?.[0] || ''
    const last = currentUser.value.lastName?.[0] || ''
    return `${first}${last}`.toUpperCase() || currentUser.value.email[0].toUpperCase()
  })

  const isPremium = computed(() => {
    return currentUser.value?.subscription?.isPremium || false
  })

  const isAdmin = computed(() => {
    return currentUser.value?.roles?.includes('ROLE_ADMIN') || false
  })

  const isRecruiter = computed(() => {
    return currentUser.value?.roles?.includes('ROLE_RECRUITER') || false
  })

  return {
    currentUser: readonly(currentUser),
    isLoading: readonly(isLoading),
    isAuthenticated,
    
    login,
    logout,
    loadUserFromStorage,
    
    userFullName,
    userInitials,
    isPremium,
    isAdmin,
    isRecruiter,
  }
} 