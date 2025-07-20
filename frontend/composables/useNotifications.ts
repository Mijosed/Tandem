import { ref, computed, readonly } from 'vue'
import type { Notification, NotificationFormData, NotificationStats } from '~/types/notification'
import { useAuth } from '~/composables/useAuth'

// Instance globale partagée pour éviter les problèmes de synchronisation
let globalNotificationsInstance: any = null

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
  // Retourner l'instance globale si elle existe déjà
  if (globalNotificationsInstance) {
    return globalNotificationsInstance
  }
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
        method: 'PATCH',
        headers: {
          ...getJwtHeaders(),
          'Content-Type': 'application/merge-patch+json',
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
            method: 'PATCH',
            headers: {
              ...getJwtHeaders(),
              'Content-Type': 'application/merge-patch+json',
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
      console.log('currentUser.value:', currentUser.value)
      
      if (!currentUser.value?.id) {
        throw new Error('Utilisateur non connecté')
      }

      const payload = {
        title: data.title,
        message: data.message,
        type: data.type,
        scheduledFor: data.scheduledFor,
        interviewTime: data.interviewTime,
        user: currentUser.value.id, // Envoyer directement l'ID
        candidature: data.candidatureId || null,
        isRead: false
      }

      console.log('Création de notification avec payload:', payload)
      console.log('User ID utilisé:', currentUser.value.id)

      const response = await fetch(`${apiBase}/notifications`, {
        method: 'POST',
        headers: {
          ...getJwtHeaders(),
          'Content-Type': 'application/ld+json',
        },
        body: JSON.stringify(payload)
      })

      if (!response.ok) {
        const errorText = await response.text()
        console.error('Erreur API lors de la création de notification:', errorText)
        throw new Error(`HTTP ${response.status}: ${response.statusText}`)
      }

      const newNotification = await response.json()
      console.log('Notification créée par l\'API:', newNotification)
      
      const transformedNotification = transformNotificationFromAPI(newNotification)
      notifications.value.unshift(transformedNotification)
      
      console.log('Notification ajoutée à la liste locale:', transformedNotification)
      console.log('Nombre total de notifications:', notifications.value.length)

      // Émettre un événement personnalisé pour notifier les autres composants
      if (typeof window !== 'undefined') {
        window.dispatchEvent(new CustomEvent('notification-created', {
          detail: transformedNotification
        }))
      }

      return newNotification

    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Erreur lors de la création'
      console.error('Erreur lors de la création de la notification:', err)
      throw err
    }
  }

  // Fonction spécialisée pour créer une notification d'entretien
  const createInterviewNotification = async (
    titrePoste: string, 
    entreprise: string, 
    dateEntretien: string,
    candidatureId?: number,
    heureEntretien?: string
  ) => {
    try {
      const interviewDate = new Date(dateEntretien + 'T00:00:00')
      const formattedDate = new Intl.DateTimeFormat('fr-FR', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric'
      }).format(interviewDate)

      console.log('Création de notification d\'entretien:', {
        titrePoste,
        entreprise,
        dateEntretien,
        heureEntretien,
        scheduledFor: interviewDate.toISOString()
      })

      // Message avec ou sans heure
      const timeMessage = heureEntretien ? ` à ${heureEntretien}` : ''
      const fullMessage = `Vous avez un entretien programmé le ${formattedDate}${timeMessage} pour le poste "${titrePoste}" chez ${entreprise}. Bonne chance !`

      // Créer la notification principale d'entretien
      const mainNotification = await createNotification({
        title: 'Entretien programmé',
        message: fullMessage,
        type: 'interview',
        scheduledFor: interviewDate.toISOString(),
        candidatureId: candidatureId,
        interviewTime: heureEntretien
      })

      console.log('Notification principale créée:', mainNotification)

      // Créer un rappel 1 jour avant l'entretien si l'entretien est dans plus d'1 jour
      const now = new Date()
      const oneDayBefore = new Date(interviewDate)
      oneDayBefore.setDate(oneDayBefore.getDate() - 1)
      
      if (oneDayBefore > now) {
        try {
          const reminderNotification = await createNotification({
            title: 'Rappel : Entretien demain',
            message: `N'oubliez pas votre entretien demain pour le poste "${titrePoste}" chez ${entreprise}. Préparez vos documents et questions !`,
            type: 'reminder',
            scheduledFor: oneDayBefore.toISOString(),
            candidatureId: candidatureId
          })
          console.log('Notification de rappel créée:', reminderNotification)
        } catch (reminderError) {
          console.warn('Erreur lors de la création du rappel d\'entretien:', reminderError)
        }
      }

      return mainNotification
    } catch (err) {
      console.error('Erreur lors de la création de la notification d\'entretien:', err)
      throw err
    }
  }

  // Mettre à jour l'heure d'entretien d'une notification
  const updateNotificationTime = async (notificationId: number, time: string) => {
    try {
      const notification = notifications.value.find(n => n.id === notificationId)
      if (!notification) {
        throw new Error('Notification non trouvée')
      }

      const response = await fetch(`${apiBase}/notifications/${notificationId}`, {
        method: 'PATCH',
        headers: {
          ...getJwtHeaders(),
          'Content-Type': 'application/merge-patch+json',
        },
        body: JSON.stringify({
          interviewTime: time
        })
      })

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`)
      }

      // Mettre à jour localement
      const index = notifications.value.findIndex(n => n.id === notificationId)
      if (index !== -1) {
        notifications.value[index].interviewTime = time
      }

      console.log('Heure de notification mise à jour:', time)

    } catch (err) {
      console.error('Erreur lors de la mise à jour de l\'heure:', err)
      throw err
    }
  }

  // Supprimer toutes les notifications d'entretien liées à une candidature
  const deleteInterviewNotificationsForCandidature = async (candidatureId: number) => {
    try {
      const interviewNotifications = notifications.value.filter(n => 
        n.candidature?.id === candidatureId && 
        (n.type === 'interview' || n.type === 'reminder')
      )
      
      console.log(`Suppression de ${interviewNotifications.length} notifications pour candidature ${candidatureId}`)
      
      await Promise.all(
        interviewNotifications.map(notification => 
          fetch(`${apiBase}/notifications/${notification.id}`, {
            method: 'DELETE',
            headers: {
              ...getJwtHeaders(),
              'Content-Type': 'application/json',
            }
          })
        )
      )
      
      // Retirer de la liste locale
      notifications.value = notifications.value.filter(n => 
        !(n.candidature?.id === candidatureId && (n.type === 'interview' || n.type === 'reminder'))
      )
      
      console.log('Notifications d\'entretien supprimées avec succès')
      
    } catch (err) {
      console.error('Erreur lors de la suppression des notifications d\'entretien:', err)
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

  // Notifications avec date d'entretien prévue
  const notificationsWithInterview = computed(() => 
    notifications.value.filter(notification => 
      notification.scheduledFor && notification.scheduledFor !== null
    )
  )

  // Notifications sans date d'entretien
  const notificationsWithoutInterview = computed(() => 
    notifications.value.filter(notification => 
      !notification.scheduledFor || notification.scheduledFor === null
    )
  )

  // Notifications d'entretien à venir (dans les 7 prochains jours)
  const upcomingInterviews = computed(() => {
    const now = new Date()
    const nextWeek = new Date()
    nextWeek.setDate(nextWeek.getDate() + 7)
    
    return notificationsWithInterview.value.filter(notification => {
      if (!notification.scheduledFor) return false
      const interviewDate = new Date(notification.scheduledFor)
      return interviewDate >= now && interviewDate <= nextWeek
    })
  })

  const instance = {
    notifications: readonly(notifications),
    loading: readonly(loading),
    error: readonly(error),
    
    fetchNotifications,
    markAsRead,
    markAllAsRead,
    deleteNotification,
    createNotification,
    createInterviewNotification,
    updateNotificationTime,
    deleteInterviewNotificationsForCandidature,
    
    unreadNotifications,
    unreadCount,
    hasUnreadNotifications,
    stats,
    recentNotifications,
    notificationsWithInterview,
    notificationsWithoutInterview,
    upcomingInterviews,
  }

  // Sauvegarder l'instance globale
  globalNotificationsInstance = instance
  return instance
} 