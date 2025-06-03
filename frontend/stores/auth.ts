import { defineStore } from 'pinia'
import { navigateTo } from '#app'
import { ref } from 'vue'

interface User {
  id: number
  email: string
  firstname: string
  lastname: string
  roles: string[]
}

interface LoginData {
  email: string
  password: string
}

export const useAuthStore = defineStore('auth', () => {
  // État initial
  const token = ref<string | null>(null)
  const user = ref<User | null>(null)
  const isAuthenticated = ref(false)
  
  // Initialisation côté client uniquement
  if (process.client) {
    const storedToken = localStorage.getItem('auth-token')
    if (storedToken) {
      token.value = storedToken
      isAuthenticated.value = true
    }

    const storedUser = localStorage.getItem('auth-user')
    if (storedUser) {
      try {
        user.value = JSON.parse(storedUser)
      } catch (e) {
        console.error('Failed to parse stored user:', e)
        localStorage.removeItem('auth-user')
      }
    }
  }

  async function login(formData: LoginData) {
    try {
      const response = await fetch('/api/login_check', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify({
          username: formData.email,
          password: formData.password,
        }),
      })

      if (!response.ok) {
        const errorData = await response.json()
        throw new Error(errorData.message || 'Login failed')
      }

      const data = await response.json()
      if (!data.token) {
        throw new Error('No token received')
      }

      token.value = data.token
      isAuthenticated.value = true

      if (process.client) {
        localStorage.setItem('auth-token', data.token)
      }

      await fetchUser()
      return true
    } catch (error) {
      console.error('Login error:', error)
      logout()
      throw error
    }
  }

  async function fetchUser() {
    if (!token.value) {
      throw new Error('No token available')
    }

    try {
      const response = await fetch('/api/test', {
        headers: {
          'Authorization': `Bearer ${token.value}`,
          'Accept': 'application/json',
        },
      })

      if (!response.ok) {
        throw new Error('Failed to fetch user')
      }

      const data = await response.json()
      if (!data.user) {
        throw new Error('Invalid user data received')
      }

      user.value = data.user
      if (process.client) {
        localStorage.setItem('auth-user', JSON.stringify(data.user))
      }
    } catch (error) {
      console.error('Fetch user error:', error)
      throw error
    }
  }

  async function register(userData: { email: string; password: string; firstname: string; lastname: string }) {
    try {
      const response = await fetch('/api/register', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify(userData),
      })

      if (!response.ok) {
        const errorData = await response.json()
        throw new Error(errorData.message || 'Registration failed')
      }

      return true
    } catch (error) {
      console.error('Registration error:', error)
      throw error
    }
  }

  // Fonction pour vérifier si le token est valide
  async function validateToken() {
    if (!token.value) return false
    
    try {
      const response = await fetch('/api/test', {
        headers: {
          'Authorization': `Bearer ${token.value}`,
          'Accept': 'application/json',
        },
      })
      
      if (!response.ok) {
        throw new Error('Token validation failed')
      }

      const data = await response.json()
      return !!data.user
    } catch (error) {
      console.error('Token validation error:', error)
      return false
    }
  }

  function logout() {
    token.value = null
    user.value = null
    isAuthenticated.value = false
    if (process.client) {
      localStorage.removeItem('auth-token')
      localStorage.removeItem('auth-user')
    }
  }

  async function restoreSession() {
    if (!process.client || !token.value) return false

    try {
      const isTokenValid = await validateToken()
      if (!isTokenValid) {
        throw new Error('Invalid token')
      }

      await fetchUser()
      return true
    } catch (error) {
      console.error('Failed to restore session:', error)
      logout()
      return false
    }
  }

  // Vérifier l'authentification au chargement du store
  if (token.value && !user.value) {
    console.log('Token found but no user, fetching user data...')
    fetchUser().catch((error) => {
      console.error('Failed to fetch initial user data:', error)
      logout()
    })
  }

  return {
    token,
    user,
    isAuthenticated,
    login,
    logout,
    fetchUser,
    validateToken,
    restoreSession
  }
})
