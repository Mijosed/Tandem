import type { ScheduleEvent, ScheduleEventInput } from '~/types/schedule'

interface ApiScheduleEvent {
  id: number
  title: string
  description?: string
  startDate: string
  endDate: string
  type: 'interview' | 'meeting' | 'reminder' | 'deadline' | 'personal'
  location?: string
  allDay: boolean
  color?: string
  user: any
  candidature?: any
  createdAt: string
  updatedAt: string
}

export const useSchedule = () => {
  const { get, post, put, delete: del } = useApi()
  const events = ref<ScheduleEvent[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  // Convertir les événements de l'API au format FullCalendar
  const formatEventFromAPI = (apiEvent: ApiScheduleEvent): ScheduleEvent => {
    return {
      id: apiEvent.id.toString(),
      title: apiEvent.title,
      description: apiEvent.description || '',
      start: apiEvent.startDate,
      end: apiEvent.endDate,
      allDay: apiEvent.allDay,
      type: apiEvent.type,
      location: apiEvent.location || '',
      color: getEventColor(apiEvent.type),
      extendedProps: {
        type: apiEvent.type,
        location: apiEvent.location,
        description: apiEvent.description,
        candidature: apiEvent.candidature
      }
    }
  }

  // Convertir les événements du format FullCalendar vers l'API
  const formatEventForAPI = (event: ScheduleEventInput): any => {
    return {
      title: event.title,
      description: event.description || null,
      startDate: event.startDate,
      endDate: event.endDate,
      type: event.type || 'personal',
      location: event.location || null,
      allDay: event.allDay || false,
      color: event.color || null
    }
  }

  // Obtenir la couleur selon le type d'événement
  const getEventColor = (type: string): string => {
    const colors = {
      interview: '#10b981', // vert
      meeting: '#3b82f6',   // bleu
      reminder: '#f59e0b',  // orange
      deadline: '#ef4444',  // rouge
      personal: '#8b5cf6'   // violet
    }
    return colors[type as keyof typeof colors] || colors.personal
  }

  // Récupérer tous les événements de l'utilisateur
  const fetchEvents = async () => {
    loading.value = true
    error.value = null
    
    try {
      const userData = JSON.parse(localStorage.getItem('user') || '{}')
      const userId = userData.id || 12 // Fallback pour les tests
      
      const response: any = await get(`/api/schedule/events/user/${userId}`)
      
      if (response['hydra:member']) {
        events.value = response['hydra:member'].map(formatEventFromAPI)
      }
    } catch (err: any) {
      error.value = err.message || 'Erreur lors du chargement des événements'
      console.error('Erreur fetchEvents:', err)
    } finally {
      loading.value = false
    }
  }

  // Créer un nouvel événement
  const createEvent = async (eventData: ScheduleEventInput): Promise<ScheduleEvent | null> => {
    loading.value = true
    error.value = null

    try {
      const userData = JSON.parse(localStorage.getItem('user') || '{}')
      const userId = userData.id || 12

      const payload = {
        ...formatEventForAPI(eventData),
        userId: userId
      }

      const response = await post('/api/schedule/events', payload) as ApiScheduleEvent

      const newEvent = formatEventFromAPI(response)
      events.value.push(newEvent)
      
      return newEvent
    } catch (err: any) {
      error.value = err.message || 'Erreur lors de la création de l\'événement'
      console.error('Erreur createEvent:', err)
      return null
    } finally {
      loading.value = false
    }
  }

  // Mettre à jour un événement
  const updateEvent = async (eventId: string, eventData: Partial<ScheduleEventInput>): Promise<ScheduleEvent | null> => {
    loading.value = true
    error.value = null

    try {
      const payload = formatEventForAPI(eventData as ScheduleEventInput)
      
      const response = await put(`/api/schedule_events/${eventId}`, payload) as ApiScheduleEvent

      const updatedEvent = formatEventFromAPI(response)
      const index = events.value.findIndex(e => e.id === eventId)
      if (index !== -1) {
        events.value[index] = updatedEvent
      }
      
      return updatedEvent
    } catch (err: any) {
      error.value = err.message || 'Erreur lors de la mise à jour de l\'événement'
      console.error('Erreur updateEvent:', err)
      return null
    } finally {
      loading.value = false
    }
  }

  // Supprimer un événement
  const deleteEvent = async (eventId: string): Promise<boolean> => {
    loading.value = true
    error.value = null

    try {
      await del(`/api/schedule_events/${eventId}`)

      events.value = events.value.filter(e => e.id !== eventId)
      return true
    } catch (err: any) {
      error.value = err.message || 'Erreur lors de la suppression de l\'événement'
      console.error('Erreur deleteEvent:', err)
      return false
    } finally {
      loading.value = false
    }
  }

  // Obtenir les événements d'aujourd'hui
  const todayEvents = computed(() => {
    const today = new Date().toISOString().split('T')[0]
    return events.value.filter(event => {
      const eventDate = new Date(event.start).toISOString().split('T')[0]
      return eventDate === today
    })
  })

  // Obtenir les événements à venir
  const upcomingEvents = computed(() => {
    const now = new Date()
    return events.value
      .filter(event => new Date(event.start) > now)
      .sort((a, b) => new Date(a.start).getTime() - new Date(b.start).getTime())
      .slice(0, 5)
  })

  // Statistiques des événements
  const eventStats = computed(() => {
    const stats = {
      total: events.value.length,
      today: todayEvents.value.length,
      upcoming: upcomingEvents.value.length,
      byType: {} as Record<string, number>
    }

    events.value.forEach(event => {
      const type = event.extendedProps?.type || 'personal'
      stats.byType[type] = (stats.byType[type] || 0) + 1
    })

    return stats
  })

  // Récupérer les statistiques d'événements
  const fetchEventStats = async () => {
    try {
      const userData = JSON.parse(localStorage.getItem('user') || '{}')
      const userId = userData.id || 12
      
      const response: any = await get(`/api/schedule/stats/${userId}`)
      return response
    } catch (err: any) {
      console.error('Erreur fetchEventStats:', err)
      return {
        total: 0,
        today: 0,
        upcoming: 0,
        byType: {}
      }
    }
  }

  return {
    events: readonly(events),
    loading: readonly(loading),
    error: readonly(error),
    todayEvents,
    upcomingEvents,
    eventStats,
    fetchEvents,
    createEvent,
    updateEvent,
    deleteEvent,
    fetchEventStats,
    getEventColor
  }
}
