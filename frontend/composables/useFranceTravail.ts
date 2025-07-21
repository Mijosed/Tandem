
export const useFranceTravail = () => {
  const config = useRuntimeConfig()
  const baseUrl = '${config.public.apiBase}/api/pole-emploi'

  const searchJobs = async (filters: Record<string, any> = {}) => {
    try {
      console.log('🔍 Recherche avec filtres:', filters)
      
      const params = new URLSearchParams()
      Object.keys(filters).forEach(key => {
        if (filters[key] && filters[key] !== '') {
          params.append(key, filters[key].toString())
        }
      })
      
      const url = `${baseUrl}/search?${params}`
      console.log('📡 URL de recherche:', url)
      
      const token = typeof window !== 'undefined' ? localStorage.getItem('jwt') : null
      const response = await fetch(url, {
        headers: {
          'Authorization': token ? `Bearer ${token}` : '',
          'Content-Type': 'application/json',
        }
      })
      
      if (!response.ok) {
        throw new Error(`Erreur HTTP: ${response.status}`)
      }
      
      const data = await response.json()
      
      if (!data.success) {
        throw new Error(data.message || 'Erreur lors de la recherche')
      }
      
      return data.data
      
    } catch (error) {
      console.error('❌ Erreur lors de la recherche:', error)
      throw error
    }
  }

  const getSectors = async () => {
    try {
      console.log('📊 Chargement des secteurs...')
      
      const token = typeof window !== 'undefined' ? localStorage.getItem('jwt') : null
      const response = await fetch(`${baseUrl}/sectors`, {
        headers: {
          'Authorization': token ? `Bearer ${token}` : '',
          'Content-Type': 'application/json',
        }
      })
      
      if (!response.ok) {
        throw new Error(`Erreur HTTP: ${response.status}`)
      }
      
      const data = await response.json()
      
      if (!data.success) {
        throw new Error(data.message || 'Erreur lors du chargement des secteurs')
      }
      
      const sectors = data.data.sort((a: any, b: any) => 
        a.libelle.localeCompare(b.libelle)
      )
      
      console.log(`✅ ${sectors.length} secteurs chargés`)
      return sectors
      
    } catch (error) {
      console.error('❌ Erreur lors du chargement des secteurs:', error)
      throw error
    }
  }

  const getJobDetails = async (jobId: string) => {
    try {
      console.log('📝 Chargement des détails pour:', jobId)
      
      const token = typeof window !== 'undefined' ? localStorage.getItem('jwt') : null
      const response = await fetch(`${baseUrl}/${jobId}`, {
        headers: {
          'Authorization': token ? `Bearer ${token}` : '',
          'Content-Type': 'application/json',
        }
      })
      
      if (!response.ok) {
        throw new Error(`Erreur HTTP: ${response.status}`)
      }
      
      const data = await response.json()
      
      if (!data.success) {
        throw new Error(data.message || 'Erreur lors du chargement des détails')
      }
      
      return data.data
      
    } catch (error) {
      console.error('❌ Erreur lors du chargement des détails:', error)
      throw error
    }
  }

  const testConnection = async () => {
    try {
      console.log('🔧 Test de connexion...')
      
      const token = typeof window !== 'undefined' ? localStorage.getItem('jwt') : null
      const response = await fetch(`${baseUrl}/test`, {
        headers: {
          'Authorization': token ? `Bearer ${token}` : '',
          'Content-Type': 'application/json',
        }
      })
      
      if (!response.ok) {
        throw new Error(`Erreur HTTP: ${response.status}`)
      }
      
      const data = await response.json()
      
      if (!data.success) {
        throw new Error(data.message || 'Erreur de connexion')
      }
      
      console.log('✅ Connexion réussie')
      return true
      
    } catch (error) {
      console.error('❌ Erreur de connexion:', error)
      throw error
    }
  }

  const getSuggestions = async (query: string) => {
    try {
      if (query.length < 2) {
        return []
      }
      
      const token = typeof window !== 'undefined' ? localStorage.getItem('jwt') : null
      const response = await fetch(`${baseUrl}/suggestions?q=${encodeURIComponent(query)}`, {
        headers: {
          'Authorization': token ? `Bearer ${token}` : '',
          'Content-Type': 'application/json',
        }
      })
      
      if (!response.ok) {
        throw new Error(`Erreur HTTP: ${response.status}`)
      }
      
      const data = await response.json()
      
      if (!data.success) {
        return []
      }
      
      return data.data
      
    } catch (error) {
      console.error('❌ Erreur lors de la génération de suggestions:', error)
      return []
    }
  }

  return {
    searchJobs,
    getSectors,
    getJobDetails,
    testConnection,
    getSuggestions
  }
}
