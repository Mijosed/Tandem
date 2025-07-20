import { ref, computed } from 'vue'
import type { Notification, NotificationFormData, NotificationStats } from '~/types/notification'
import { useAuth } from '~/composables/useAuth'

// Fonction utilitaire pour transformer les données de l'API vers le format de l'interface
const transformNotificationFromAPI = (apiNotification: any): Notification => {
  return {
    id: apiNotification.id,
    title: apiNotification.title,
    message: apiNotification.message,
    type: apiNotification.type,
    isRead: apiNotification.isRead ?? false, // Par défaut false si la propriété n'est pas présente
    user: typeof apiNotification.user === 'string' 
      ? (() => {
          const matches = apiNotification.user.match(/\/(\d+)$/)
          return { id: matches ? parseInt(matches[1]) : 0, email: '', firstName: '', lastName: '' }
        })()
      : apiNotification.user,
    scheduledFor: apiNotification.scheduledFor,
    createdAt: apiNotification.createdAt,
    updatedAt: apiNotification.updatedAt
  }
}

export const useNotifications = () => {
  const getJwtHeaders = () => {
    const token = typeof window !== 'undefined' ? localStorage.getItem('jwt') : null
    return token ? { Authorization: `Bearer ${token}` } : {}
  }
  const notifications = ref<Notification[]>([])
  const loading = ref(false)
  const error = ref('')

  const { currentUser } = useAuth()
  const apiBase = 'http://localhost:8888/api'

  const fetchNotifications = async () => {
    loading.value = true
    error.value = ''
    
    try {
      if (!currentUser.value?.id) {
        throw new Error('Utilisateur non connecté')
      }

      const response = await fetch(`${apiBase}/notifications`, {
        headers: {
          ...getJwtHeaders(),
          'Content-Type': 'application/json',
        }
      })
      
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`)
      }
      
      const data = await response.json()
      
      let allNotifications: any[] = []
      
      if (data['hydra:member'] && Array.isArray(data['hydra:member'])) {
        allNotifications = data['hydra:member']
      } else if (data.member && Array.isArray(data.member)) {
        allNotifications = data.member
      } else if (Array.isArray(data)) {
        allNotifications = data
      } else if (data.notifications && Array.isArray(data.notifications)) {
        allNotifications = data.notifications
      } else {
        throw new Error('Format de réponse API non supporté')
      }
      
      const userNotifications = allNotifications.filter((notif: any) => {
        let userId: number = 0
        
        if (typeof notif.user === 'string') {
          const matches = notif.user.match(/\/(\d+)$/)
          userId = matches ? parseInt(matches[1]) : 0
        } else if (notif.user?.id) {
          userId = notif.user.id
        }
        
        return userId === currentUser.value?.id
      })
      
      notifications.value = userNotifications.map(transformNotificationFromAPI)
      
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Erreur lors du chargement des notifications'
      console.error('Erreur lors du chargement des notifications:', err)
    } finally {
      loading.value = false
    }
  }

  const markAsRead = async (id: number) => {
    try {
      const notification = notifications.value.find(n => n.id === id)
      if (!notification || notification.isRead) return

      const response = await fetch(`${apiBase}/notifications/${id}`, {
        method: 'PUT',
        headers: {
          ...getJwtHeaders(),
          'Content-Type': 'application/ld+json',
        },
        body: JSON.stringify({
          isRead: true
        })
      })

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`)
      }

      const index = notifications.value.findIndex(n => n.id === id)
      if (index !== -1) {
        notifications.value[index].isRead = true
      }

    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Erreur lors de la mise à jour'
      console.error('Erreur lors de la mise à jour de la notification:', err)
    }
  }

  const markAllAsRead = async () => {
    try {
      const unreadNotifications = notifications.value.filter(n => !n.isRead)
      
      await Promise.all(
        unreadNotifications.map(notification => 
          fetch(`${apiBase}/notifications/${notification.id}`, {
            method: 'PUT',
            headers: {
              ...getJwtHeaders(),
              'Content-Type': 'application/ld+json',
            },
            body: JSON.stringify({
              isRead: true
            })
          })
        )
      )

      notifications.value = notifications.value.map(notification => ({
        ...notification,
        isRead: true
      }))

    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Erreur lors de la mise à jour'
      console.error('Erreur lors de la mise à jour des notifications:', err)
    }
  }

  const deleteNotification = async (id: number) => {
    try {
      const response = await fetch(`${apiBase}/notifications/${id}`, {
        method: 'DELETE',
        headers: {
          ...getJwtHeaders(),
          'Content-Type': 'application/json',
        }
      })

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`)
      }

      notifications.value = notifications.value.filter(n => n.id !== id)

    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Erreur lors de la suppression'
      console.error('Erreur lors de la suppression de la notification:', err)
    }
  }

  const createNotification = async (data: NotificationFormData) => {
    try {
      if (!currentUser.value?.id) {
        throw new Error('Utilisateur non connecté')
      }

      const response = await fetch(`${apiBase}/notifications`, {
        method: 'POST',
        headers: {
          ...getJwtHeaders(),
          'Content-Type': 'application/ld+json',
        },
        body: JSON.stringify({
          ...data,
          user: `/api/users/${currentUser.value.id}`,
          isRead: false
        })
      })

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`)
      }

      const newNotification = await response.json()
      notifications.value.unshift(transformNotificationFromAPI(newNotification))

      return newNotification

    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Erreur lors de la création'
      console.error('Erreur lors de la création de la notification:', err)
      throw err
    }
  }

  const unreadNotifications = computed(() => 
    notifications.value.filter(n => !n.isRead)
  )

  const unreadCount = computed(() => unreadNotifications.value.length)

  const hasUnreadNotifications = computed(() => unreadCount.value > 0)

  const notificationsByType = computed(() => {
    const byType = {
      reminder: 0,
      interview: 0,
      info: 0,
      warning: 0,
      success: 0
    }

    notifications.value.forEach(notification => {
      byType[notification.type]++
    })

    return byType
  })

  const stats = computed((): NotificationStats => ({
    total: notifications.value.length,
    unread: unreadCount.value,
    byType: notificationsByType.value
  }))

  const recentNotifications = computed(() => {
    const sevenDaysAgo = new Date()
    sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7)
    
    return notifications.value.filter(notification => 
      new Date(notification.createdAt) >= sevenDaysAgo
    )
  })

  return {
    notifications: readonly(notifications),
    loading: readonly(loading),
    error: readonly(error),
    
    fetchNotifications,
    markAsRead,
    markAllAsRead,
    deleteNotification,
    createNotification,
    
    unreadNotifications,
    unreadCount,
    hasUnreadNotifications,
    stats,
    recentNotifications,
  }
} 