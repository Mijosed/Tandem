import { ref, computed, readonly } from 'vue'

export interface User {
  id: number
  firstName: string
  lastName: string
  email: string
  roles: string[]
  isActive: boolean
  createdAt: string
  updatedAt?: string
}

export interface UserFormData {
  firstName: string
  lastName: string
  email: string
  roles: string[]
  isActive: boolean
  password?: string
}

export const useUsers = () => {
  const users = ref<User[]>([])
  const loading = ref(false)
  const error = ref('')

  const apiBase = 'http://localhost:8888/api'

  const fetchUsers = async () => {
    loading.value = true
    error.value = ''
    
    try {
      const response = await fetch(`${apiBase}/users`)
      
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`)
      }
      
      const data = await response.json()
      
      let allUsers: any[] = []
      if (data['hydra:member'] && Array.isArray(data['hydra:member'])) {
        allUsers = data['hydra:member']
      } else if (data.member && Array.isArray(data.member)) {
        allUsers = data.member
      } else if (Array.isArray(data)) {
        allUsers = data
      } else {
        throw new Error('Format de réponse API non supporté')
      }
      
      users.value = allUsers.map(transformUserFromAPI)
      
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Erreur lors du chargement des utilisateurs'
      console.error('Erreur lors du chargement des utilisateurs:', err)
    } finally {
      loading.value = false
    }
  }

  const createUser = async (userData: UserFormData): Promise<User | null> => {
    loading.value = true
    error.value = ''
    
    try {
      const response = await fetch(`${apiBase}/users`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          firstName: userData.firstName,
          lastName: userData.lastName,
          email: userData.email,
          roles: userData.roles,
          isActive: userData.isActive,
          password: userData.password,
        }),
      })
      
      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}))
        throw new Error(errorData.message || `HTTP ${response.status}: ${response.statusText}`)
      }
      
      const newUser = await response.json()
      const transformedUser = transformUserFromAPI(newUser)
      users.value.push(transformedUser)
      
      return transformedUser
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Erreur lors de la création de l\'utilisateur'
      console.error('Erreur lors de la création de l\'utilisateur:', err)
      return null
    } finally {
      loading.value = false
    }
  }

  const updateUser = async (id: number, userData: Partial<UserFormData>): Promise<User | null> => {
    loading.value = true
    error.value = ''
    
    try {
      const response = await fetch(`${apiBase}/users/${id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(userData),
      })
      
      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}))
        throw new Error(errorData.message || `HTTP ${response.status}: ${response.statusText}`)
      }
      
      const updatedUser = await response.json()
      const transformedUser = transformUserFromAPI(updatedUser)
      
      const index = users.value.findIndex(u => u.id === id)
      if (index !== -1) {
        users.value[index] = transformedUser
      }
      
      return transformedUser
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Erreur lors de la mise à jour de l\'utilisateur'
      console.error('Erreur lors de la mise à jour de l\'utilisateur:', err)
      return null
    } finally {
      loading.value = false
    }
  }

  const deleteUser = async (id: number): Promise<boolean> => {
    loading.value = true
    error.value = ''
    
    try {
      const response = await fetch(`${apiBase}/users/${id}`, {
        method: 'DELETE',
      })
      
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`)
      }
      
      users.value = users.value.filter(u => u.id !== id)
      return true
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Erreur lors de la suppression de l\'utilisateur'
      console.error('Erreur lors de la suppression de l\'utilisateur:', err)
      return false
    } finally {
      loading.value = false
    }
  }

  const toggleUserStatus = async (id: number): Promise<User | null> => {
    const user = users.value.find(u => u.id === id)
    if (!user) return null
    
    return updateUser(id, { isActive: !user.isActive })
  }

  const transformUserFromAPI = (apiUser: any): User => {
    return {
      id: apiUser.id,
      firstName: apiUser.firstName || '',
      lastName: apiUser.lastName || '',
      email: apiUser.email || '',
      roles: apiUser.roles || ['ROLE_USER'],
      isActive: apiUser.isActive ?? true,
      createdAt: apiUser.createdAt || new Date().toISOString(),
      updatedAt: apiUser.updatedAt,
    }
  }

  const userStats = computed(() => ({
    total: users.value.length,
    active: users.value.filter(u => u.isActive).length,
    inactive: users.value.filter(u => !u.isActive).length,
    admins: users.value.filter(u => u.roles.includes('ROLE_ADMIN')).length,
    premium: users.value.filter(u => u.roles.includes('ROLE_PREMIUM')).length,
  }))

  return {
    users: readonly(users),
    loading: readonly(loading),
    error: readonly(error),

    fetchUsers,
    createUser,
    updateUser,
    deleteUser,
    toggleUserStatus,

    userStats,
  }
} 