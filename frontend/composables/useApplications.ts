import { ref, computed } from 'vue'
import type { Application, ApplicationStatus, ApplicationStats } from '~/types/application'

const transformApplicationFromAPI = (apiApplication: any): Application => {
  return {
    id: apiApplication.id,
    job: null,
    appliedAt: apiApplication.dateDepot,
    coverLetter: apiApplication.notes,
    resumePath: null,
    status: apiApplication.statut,
    user: apiApplication.user,
    updatedAt: apiApplication.dateCreation,
    position: apiApplication.titrePoste || '',
    company: apiApplication.entreprise || '',
    applicationDate: apiApplication.dateDepot?.split('T')[0] || '',
    notes: apiApplication.notes || ''
  }
}

export const useApplications = () => {
  const config = useRuntimeConfig()
  const apiBase = config.public.apiBase
  
  const getJwtHeaders = () => {
    const token = typeof window !== 'undefined' ? localStorage.getItem('jwt') : null
    return token ? { Authorization: `Bearer ${token}` } : {}
  }
  const applications = ref<Application[]>([])
  const loading = ref(false)
  const error = ref('')

  const fetchApplications = async () => {
    loading.value = true
    error.value = ''
    
    try {
      const userData = typeof window !== 'undefined' ? JSON.parse(localStorage.getItem('user') || '{}') : {}
      
      if (!userData.id) {
        throw new Error('Utilisateur non connecté')
      }

      const response = await fetch(`${apiBase}/api/candidatures`, {
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
      
      const userApplications = allApplications.filter((app: any) => {
        return app.user === `/api/users/${userData.id}` || 
               (app.user && app.user.id === userData.id)
      })
      
      applications.value = userApplications.map(transformApplicationFromAPI)
      
    } catch (err: any) {
      error.value = err.message || 'Erreur lors de la récupération des candidatures'
      console.error('Erreur lors de la récupération des candidatures:', err)
    } finally {
      loading.value = false
    }
  }

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
      const jobsResponse = await fetch(`${apiBase}/api/jobs`, {
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
      
      let jobToUse = existingJobs.find((job: any) => 
        job.title === applicationData.position && job.company === applicationData.company
      )
      
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
        
        const jobResponse = await fetch(`${apiBase}/api/jobs`, {
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

      const userData = typeof window !== 'undefined' ? JSON.parse(localStorage.getItem('user') || '{}') : {}
      
      const applicationPayload: any = {
        job: `/api/jobs/${jobToUse.id}`,
        appliedAt: applicationData.applicationDate + 'T00:00:00Z',
        status: applicationData.status,
        coverLetter: applicationData.notes || '',
        resumePath: null
      }
      
      if (userData.id) {
        applicationPayload.user = `/api/users/${userData.id}`
      }
      
      const response = await fetch(`${apiBase}/api/candidatures`, {
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

  const updateApplication = async (id: number, applicationData: Partial<Application>) => {
    loading.value = true
    error.value = ''
    
    try {
      const response = await fetch(`${apiBase}/api/candidatures/${id}`, {
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

  const deleteApplication = async (id: number) => {
    loading.value = true
    error.value = ''
    
    try {
      const response = await fetch(`${apiBase}/api/candidatures/${id}`, {
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
      
      applications.value = applications.value.filter(app => app.id !== id)
      
    } catch (err: any) {
      error.value = err.message || 'Erreur lors de la suppression de la candidature'
      console.error('Erreur lors de la suppression de la candidature:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateApplicationStatus = async (id: number, status: Application['status']) => {
    return updateApplication(id, { status })
  }

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

  const recentApplications = computed(() => {
    return [...applications.value]
      .sort((a, b) => new Date(b.appliedAt || b.applicationDate || '').getTime() - new Date(a.appliedAt || a.applicationDate || '').getTime())
      .slice(0, 5)
  })

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
    applications,
    loading,
    error,
    
    stats,
    recentApplications,
    applicationsByStatus,
    
    fetchApplications,
    createApplication,
    updateApplication,
    deleteApplication,
    updateApplicationStatus,
    
    searchApplications
  }
}
