import { ref, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '~/stores/auth'

interface Notification {
  id: number
  title: string
  message: string
  type: 'reminder' | 'interview' | 'info'
  read: boolean
  createdAt: string
  data?: any
}

export const useMercure = () => {
  const { $axios } = useNuxtApp()
  const authStore = useAuthStore()
  
  const notifications = ref<Notification[]>([])
  const unreadCount = ref(0)
  const isConnected = ref(false)
  const eventSource = ref<EventSource | null>(null)

  const connectToMercure = () => {
    if (!authStore.user?.id) {
      console.warn('User not authenticated, cannot connect to Mercure')
      return
    }

    try {
      // URL du Hub Mercure
      const mercureUrl = 'http://localhost:3000/.well-known/mercure'
      const topic = `notifications/user/${authStore.user.id}`
      
      // Créer la connexion EventSource
      const url = `${mercureUrl}?topic=${encodeURIComponent(topic)}`
      eventSource.value = new EventSource(url)

      eventSource.value.onopen = () => {
        console.log('Connected to Mercure Hub')
        isConnected.value = true
      }

      eventSource.value.onmessage = (event) => {
        try {
          const data = JSON.parse(event.data)
          handleNotificationUpdate(data)
        } catch (error) {
          console.error('Error parsing Mercure message:', error)
        }
      }

      eventSource.value.onerror = (error) => {
        console.error('Mercure connection error:', error)
        isConnected.value = false
        // Tentative de reconnexion après 5 secondes
        setTimeout(() => {
          if (authStore.isAuthenticated) {
            connectToMercure()
          }
        }, 5000)
      }

    } catch (error) {
      console.error('Error connecting to Mercure:', error)
    }
  }

  const disconnectFromMercure = () => {
    if (eventSource.value) {
      eventSource.value.close()
      eventSource.value = null
      isConnected.value = false
    }
  }

  const handleNotificationUpdate = (data: any) => {
    if (data.id) {
      // Mise à jour d'une notification existante
      const index = notifications.value.findIndex(n => n.id === data.id)
      if (index !== -1) {
        notifications.value[index] = data
      } else {
        // Nouvelle notification
        notifications.value.unshift(data)
      }
    } else if (data.count !== undefined) {
      // Mise à jour du compteur de notifications non lues
      unreadCount.value = data.count
    }
    
    // Mettre à jour le compteur de notifications non lues
    updateUnreadCount()
  }

  const loadNotifications = async () => {
    try {
      const response = await $axios.get('/notifications')
      notifications.value = response.data.notifications
      updateUnreadCount()
    } catch (error) {
      console.error('Error loading notifications:', error)
    }
  }

  const updateUnreadCount = async () => {
    try {
      const response = await $axios.get('/notifications/unread-count')
      unreadCount.value = response.data.count
    } catch (error) {
      console.error('Error updating unread count:', error)
    }
  }

  const markAsRead = async (id: number) => {
    try {
      await $axios.post(`/notifications/${id}/read`)
      const notification = notifications.value.find(n => n.id === id)
      if (notification) {
        notification.read = true
        updateUnreadCount()
      }
    } catch (error) {
      console.error('Error marking notification as read:', error)
    }
  }

  const markAllAsRead = async () => {
    try {
      await $axios.post('/notifications/mark-all-read')
      notifications.value.forEach(n => n.read = true)
      unreadCount.value = 0
    } catch (error) {
      console.error('Error marking all notifications as read:', error)
    }
  }

  const createTestNotification = async (data: {
    title: string
    message: string
    type: 'reminder' | 'interview' | 'info'
  }) => {
    try {
      await $axios.post('/notifications/test', data)
    } catch (error) {
      console.error('Error creating test notification:', error)
    }
  }

  // Connexion automatique quand l'utilisateur est authentifié
  watch(() => authStore.isAuthenticated, (isAuthenticated) => {
    if (isAuthenticated && authStore.user?.id) {
      loadNotifications()
      connectToMercure()
    } else {
      disconnectFromMercure()
      notifications.value = []
      unreadCount.value = 0
    }
  })

  onMounted(() => {
    if (authStore.isAuthenticated && authStore.user?.id) {
      loadNotifications()
      connectToMercure()
    }
  })

  onUnmounted(() => {
    disconnectFromMercure()
  })

  return {
    notifications: readonly(notifications),
    unreadCount: readonly(unreadCount),
    isConnected: readonly(isConnected),
    loadNotifications,
    markAsRead,
    markAllAsRead,
    createTestNotification,
    connectToMercure,
    disconnectFromMercure
  }
} 