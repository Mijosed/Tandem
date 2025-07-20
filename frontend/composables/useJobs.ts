import { ref, computed } from 'vue'

export interface JobOffer {
  id: string
  title: string
  company: string
  location: string
  contract_type: string
  experience: string
  qualification: string
  description: string
  salary?: string
  publication_date: string
  application_url: string
  duration?: string
  sector?: string
}

export interface JobSearchCriteria {
  keywords?: string
  location?: string
  distance?: number
  contractType?: string
  experience?: string
  qualification?: string
  fullTime?: boolean
  sort?: number
}

export interface JobSearchResult {
  jobs: JobOffer[]
  total: number
  filters?: any[]
}

export const useJobs = () => {
  const { apiCall } = useApi()
  const getJwtHeaders = () => {
    const token = typeof window !== 'undefined' ? localStorage.getItem('jwt') : null
    return token ? { Authorization: `Bearer ${token}` } : {}
  }
  
  const jobs = ref<JobOffer[]>([])
  const currentJob = ref<JobOffer | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)
  const total = ref(0)
  const sectors = ref<any[]>([])

  const searchCriteria = ref<JobSearchCriteria>({
    keywords: '',
    location: '',
    distance: 10,
    contractType: 'ALL',
    experience: 'ALL',
    qualification: '',
    fullTime: true,
    sort: 0
  })

  const searchJobs = async (criteria?: JobSearchCriteria) => {
    loading.value = true
    error.value = null

    try {
      const searchParams = { ...searchCriteria.value, ...criteria }
      
      if (searchParams.contractType === 'ALL') {
        searchParams.contractType = ''
      }
      if (searchParams.experience === 'ALL') {
        searchParams.experience = ''
      }
      
      const response = await apiCall<{
        success: boolean
        data: JobSearchResult
        message: string
      }>('/api/pole-emploi/search', {
        params: searchParams,
        headers: {
          ...getJwtHeaders()
        }
      })

      if (response.success) {
        jobs.value = response.data.jobs
        total.value = response.data.total
        
        Object.assign(searchCriteria.value, searchParams)
      } else {
        throw new Error(response.message || 'Erreur lors de la recherche')
      }
    } catch (err: any) {
      error.value = err.message || 'Erreur lors de la recherche d\'offres'
      console.error('Erreur recherche offres:', err)
    } finally {
      loading.value = false
    }
  }

  const getJobDetails = async (jobId: string) => {
    loading.value = true
    error.value = null

    try {
      const response = await apiCall<{
        success: boolean
        data: JobOffer
        message: string
      }>(`/api/pole-emploi/${jobId}`, {
        headers: {
          ...getJwtHeaders()
        }
      })

      if (response.success) {
        currentJob.value = response.data
        return response.data
      } else {
        throw new Error(response.message || 'Erreur lors de la récupération')
      }
    } catch (err: any) {
      error.value = err.message || 'Erreur lors de la récupération des détails'
      console.error('Erreur détails offre:', err)
      return null
    } finally {
      loading.value = false
    }
  }

  const getSuggestions = async (userId: number, criteria?: Partial<JobSearchCriteria>) => {
    loading.value = true
    error.value = null

    try {
      const response = await apiCall<{
        success: boolean
        data: JobSearchResult
        message: string
      }>(`/api/pole-emploi/suggestions/${userId}`, {
        params: criteria || {},
        headers: {
          ...getJwtHeaders()
        }
      })

      if (response.success) {
        return response.data.jobs
      } else {
        throw new Error(response.message || 'Erreur lors de la génération de suggestions')
      }
    } catch (err: any) {
      error.value = err.message || 'Erreur lors de la génération de suggestions'
      console.error('Erreur suggestions:', err)
      return []
    } finally {
      loading.value = false
    }
  }

  const getSectors = async () => {
    try {
      const response = await apiCall<{
        success: boolean
        data: any[]
        message: string
      }>('/api/pole-emploi/sectors', {
        headers: {
          ...getJwtHeaders()
        }
      })

      if (response.success) {
        sectors.value = response.data
        return response.data
      }
    } catch (err: any) {
      console.error('Erreur secteurs:', err)
      return []
    }
  }

  const resetSearch = () => {
    searchCriteria.value = {
      keywords: '',
      location: '',
      distance: 10,
      contractType: 'ALL',
      experience: 'ALL',
      qualification: '',
      fullTime: true,
      sort: 0
    }
    jobs.value = []
    total.value = 0
    error.value = null
  }

  const createApplicationFromJob = async (job: JobOffer, userId: number) => {
    try {
      const applicationData = {
        titrePoste: job.title,
        entreprise: job.company,
        statut: 'a_faire',
        dateDepot: new Date().toISOString().split('T')[0],
        notes: `Offre trouvée via Pôle Emploi\nDescription: ${job.description.substring(0, 200)}...\nLien: ${job.application_url}`,
        user: `/api/users/${userId}`
      }

      const response = await apiCall<any>('/candidatures', {
        method: 'POST',
        body: applicationData,
        headers: {
          ...getJwtHeaders()
        }
      })

      return response
    } catch (err: any) {
      error.value = 'Erreur lors de la création de la candidature'
      throw err
    }
  }

  const hasJobs = computed(() => jobs.value.length > 0)
  const hasError = computed(() => !!error.value)
  const isSearching = computed(() => loading.value)

  const contractTypes = [
    { value: 'ALL', label: 'Tous les contrats' },
    { value: 'CDI', label: 'CDI' },
    { value: 'CDD', label: 'CDD' },
    { value: 'INTERIM', label: 'Intérim' },
    { value: 'FREELANCE', label: 'Freelance' }
  ]

  const experienceLevels = [
    { value: 'ALL', label: 'Toute expérience' },
    { value: 'D', label: 'Débutant accepté' },
    { value: 'S', label: 'Souhaité' },
    { value: 'E', label: 'Exigé' }
  ]

  const sortOptions = [
    { value: 0, label: 'Pertinence' },
    { value: 1, label: 'Date de publication' },
    { value: 2, label: 'Distance' }
  ]

  return {
    jobs: readonly(jobs),
    currentJob: readonly(currentJob),
    loading: readonly(loading),
    error: readonly(error),
    total: readonly(total),
    sectors: readonly(sectors),
    searchCriteria,

    searchJobs,
    getJobDetails,
    getSuggestions,
    getSectors,
    resetSearch,
    createApplicationFromJob,

    hasJobs,
    hasError,
    isSearching,

    contractTypes,
    experienceLevels,
    sortOptions
  }
}
