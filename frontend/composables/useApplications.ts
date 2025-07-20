import { ref, computed } from 'vue'
import type { Application, ApplicationStatus, ApplicationStats } from '~/types/application'

// Fonction utilitaire pour transformer les données de l'API vers le format de l'interface
const transformApplicationFromAPI = (apiApplication: any): Application => {
  return {
    id: apiApplication.id,
    job: null, // Plus de relation job
    appliedAt: apiApplication.dateDepot,
    coverLetter: apiApplication.notes,
    resumePath: null, // Plus de CV
    status: apiApplication.statut,
    user: apiApplication.user,
    updatedAt: apiApplication.dateCreation,
    // Propriétés calculées pour l'interface
    position: apiApplication.titrePoste || '',
    company: apiApplication.entreprise || '',
    applicationDate: apiApplication.dateDepot?.split('T')[0] || '',
    notes: apiApplication.notes || ''
  }
}

export const useApplications = () => {
  const getJwtHeaders = () => {
    const token = typeof window !== 'undefined' ? localStorage.getItem('jwt') : null
    return token ? { Authorization: `Bearer ${token}` } : {}
  }
  const applications = ref<Application[]>([])
  const loading = ref(false)
  const error = ref('')

  const apiBase = 'http://localhost:8888/api'

  // Récupérer toutes les candidatures
  const fetchApplications = async () => {
    loading.value = true
    error.value = ''
    
    try {
      // Récupérer l'utilisateur connecté
      const userData = typeof window !== 'undefined' ? JSON.parse(localStorage.getItem('user') || '{}') : {}
      
      if (!userData.id) {
        throw new Error('Utilisateur non connecté')
      }

      // Récupérer toutes les candidatures et filtrer côté client
      const response = await fetch(`${apiBase}/candidatures`, {
        headers: {
          ...getJwtHeaders(),
          'Content-Type': 'application/json',
        }
      })
      
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`)
      }
      
      const data = await response.json()
      const allApplications = data.member || []
      
      // Filtrer côté client par utilisateur
      const userApplications = allApplications.filter((app: any) => {
        return app.user === `/api/users/${userData.id}` || 
               (app.user && app.user.id === userData.id)
      })
      
      // Transformer les données filtrées
      applications.value = userApplications.map(transformApplicationFromAPI)
      
    } catch (err: any) {
      error.value = err.message || 'Erreur lors de la récupération des candidatures'
      console.error('Erreur lors de la récupération des candidatures:', err)
    } finally {
      loading.value = false
    }
  }

  // Créer une nouvelle candidature
  const createApplication = async (applicationData: {
    position: string
    company: string
    applicationDate: string
    status: ApplicationStatus
    notes?: string
    interviewDate?: string
  }) => {
    loading.value = true
    error.value = ''
    
    try {
      // D'abord, créer ou récupérer le job correspondant
      const jobsResponse = await fetch(`${apiBase}/jobs`, {
        headers: {
          ...getJwtHeaders(),
          'Content-Type': 'application/json',
        }
      })
      if (!jobsResponse.ok) {
        throw new Error(`Erreur lors de la récupération des jobs: ${jobsResponse.status}`)
      }
      
      const jobsData = await jobsResponse.json()
      const existingJobs = jobsData.member || []
      
      // Chercher un job existant qui correspond
      let jobToUse = existingJobs.find((job: any) => 
        job.title === applicationData.position && job.company === applicationData.company
      )
      
      // Si aucun job correspondant n'existe, créer un nouveau job
      if (!jobToUse) {
        const jobPayload = {
          title: applicationData.position,
          company: applicationData.company,
          description: `Poste de ${applicationData.position} chez ${applicationData.company}`,
          location: 'Non spécifié',
          salaryMin: 0,
          salaryMax: 0,
          contractType: 'CDI',
          isActive: true
        }
        
        const jobResponse = await fetch(`${apiBase}/jobs`, {
          method: 'POST',
          headers: {
            ...getJwtHeaders(),
            'Content-Type': 'application/ld+json',
          },
          body: JSON.stringify(jobPayload)
        })
        
        if (!jobResponse.ok) {
          throw new Error(`Erreur lors de la création du job: ${jobResponse.status}`)
        }
        
        jobToUse = await jobResponse.json()
      }

      // Ensuite, créer la candidature
      const userData = typeof window !== 'undefined' ? JSON.parse(localStorage.getItem('user') || '{}') : {}
      
      const applicationPayload: any = {
        job: `/api/jobs/${jobToUse.id}`,
        appliedAt: applicationData.applicationDate + 'T00:00:00Z',
        status: applicationData.status,
        coverLetter: applicationData.notes || '',
        resumePath: null
      }
      
      // Ajouter l'utilisateur si disponible
      if (userData.id) {
        applicationPayload.user = `/api/users/${userData.id}`
      }
      
      const response = await fetch(`${apiBase}/candidatures`, {
        method: 'POST',
        headers: {
          ...getJwtHeaders(),
          'Content-Type': 'application/ld+json',
        },
        body: JSON.stringify(applicationPayload)
      })
      
      if (!response.ok) {
        const errorText = await response.text()
        throw new Error(`HTTP ${response.status}: ${errorText}`)
      }
      
      const newApplication = await response.json()
      
      // Transformer la réponse pour l'interface
      const transformedApplication = transformApplicationFromAPI(newApplication)
      applications.value.push(transformedApplication)
      
      return transformedApplication
      
    } catch (err: any) {
      error.value = err.message || 'Erreur lors de la création de la candidature'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Mettre à jour une candidature existante
  const updateApplication = async (id: number, applicationData: Partial<Application>) => {
    loading.value = true
    error.value = ''
    
    try {
      const response = await fetch(`${apiBase}/candidatures/${id}`, {
        method: 'PUT',
        headers: {
          ...getJwtHeaders(),
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(applicationData)
      })
      
      if (!response.ok) {
        const errorData = await response.json().catch(() => ({ error: 'Erreur lors de la mise à jour' }))
        throw new Error(errorData.error || `HTTP ${response.status}: ${response.statusText}`)
      }
      
      const updatedApplication = await response.json()
      
      // Mettre à jour la liste locale
      const index = applications.value.findIndex(app => app.id === id)
      if (index !== -1) {
        applications.value[index] = updatedApplication
      }
      
      return updatedApplication
      
    } catch (err: any) {
      error.value = err.message || 'Erreur lors de la mise à jour de la candidature'
      console.error('Erreur lors de la mise à jour de la candidature:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  // Supprimer une candidature
  const deleteApplication = async (id: number) => {
    loading.value = true
    error.value = ''
    
    try {
      const response = await fetch(`${apiBase}/candidatures/${id}`, {
        method: 'DELETE',
        headers: {
          ...getJwtHeaders(),
          'Content-Type': 'application/json',
        }
      })
      
      if (!response.ok) {
        const errorData = await response.json().catch(() => ({ error: 'Erreur lors de la suppression' }))
        throw new Error(errorData.error || `HTTP ${response.status}: ${response.statusText}`)
      }
      
      // Retirer de la liste locale
      applications.value = applications.value.filter(app => app.id !== id)
      
    } catch (err: any) {
      error.value = err.message || 'Erreur lors de la suppression de la candidature'
      console.error('Erreur lors de la suppression de la candidature:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  // Mettre à jour uniquement le statut d'une candidature
  const updateApplicationStatus = async (id: number, status: Application['status']) => {
    return updateApplication(id, { status })
  }

  // Statistiques calculées
  const stats = computed(() => {
    const total = applications.value.length
    const pending = applications.value.filter(app => app.status === 'pending').length
    const interview = applications.value.filter(app => app.status === 'interview').length
    const accepted = applications.value.filter(app => app.status === 'accepted').length
    const rejected = applications.value.filter(app => app.status === 'rejected').length
    const followedUp = applications.value.filter(app => app.status === 'followed_up').length
    
    return {
      total,
      pending,
      interview,
      accepted,
      rejected,
      followedUp,
      responseRate: total > 0 ? Math.round((accepted / total) * 100) : 0
    }
  })

  // Candidatures récentes (dernières 5)
  const recentApplications = computed(() => {
    return [...applications.value]
      .sort((a, b) => new Date(b.appliedAt || b.applicationDate || '').getTime() - new Date(a.appliedAt || a.applicationDate || '').getTime())
      .slice(0, 5)
  })

  // Candidatures par statut
  const applicationsByStatus = computed(() => {
    const byStatus: Record<string, Application[]> = {}
    applications.value.forEach(app => {
      if (!byStatus[app.status]) {
        byStatus[app.status] = []
      }
      byStatus[app.status].push(app)
    })
    return byStatus
  })

  // Recherche et filtrage
  const searchApplications = (query: string, statusFilter?: string[]) => {
    return applications.value.filter(app => {
      const matchesQuery = !query || 
        app.position.toLowerCase().includes(query.toLowerCase()) ||
        app.company.toLowerCase().includes(query.toLowerCase()) ||
        (app.notes && app.notes.toLowerCase().includes(query.toLowerCase()))
      
      const matchesStatus = !statusFilter || statusFilter.length === 0 || statusFilter.includes(app.status)
      
      return matchesQuery && matchesStatus
    })
  }

  return {
    // État
    applications,
    loading,
    error,
    
    // Données calculées
    stats,
    recentApplications,
    applicationsByStatus,
    
    // Actions CRUD
    fetchApplications,
    createApplication,
    updateApplication,
    deleteApplication,
    updateApplicationStatus,
    
    // Utilitaires
    searchApplications
  }
}
