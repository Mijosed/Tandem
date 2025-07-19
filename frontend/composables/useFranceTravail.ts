/**
 * Composable pour gérer les interactions avec l'API France Travail
 */
export const useFranceTravail = () => {
  const baseUrl = 'http://localhost:8888/api/pole-emploi'

  /**
   * Rechercher des offres d'emploi
   */
  const searchJobs = async (filters: Record<string, any> = {}) => {
    try {
      console.log('🔍 Recherche avec filtres:', filters)
      
      // Construction des paramètres
      const params = new URLSearchParams()
      Object.keys(filters).forEach(key => {
        if (filters[key] && filters[key] !== '') {
          params.append(key, filters[key].toString())
        }
      })
      
      const url = `${baseUrl}/search?${params}`
      console.log('📡 URL de recherche:', url)
      
      const response = await fetch(url)
      
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

  /**
   * Récupérer les secteurs d'activité
   */
  const getSectors = async () => {
    try {
      console.log('📊 Chargement des secteurs...')
      
      const response = await fetch(`${baseUrl}/sectors`)
      
      if (!response.ok) {
        throw new Error(`Erreur HTTP: ${response.status}`)
      }
      
      const data = await response.json()
      
      if (!data.success) {
        throw new Error(data.message || 'Erreur lors du chargement des secteurs')
      }
      
      // Trier les secteurs par libellé
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

  /**
   * Récupérer les détails d'une offre
   */
  const getJobDetails = async (jobId: string) => {
    try {
      console.log('📝 Chargement des détails pour:', jobId)
      
      const response = await fetch(`${baseUrl}/${jobId}`)
      
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

  /**
   * Tester la connexion à l'API
   */
  const testConnection = async () => {
    try {
      console.log('🔧 Test de connexion...')
      
      const response = await fetch(`${baseUrl}/test`)
      
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

  /**
   * Obtenir des suggestions de recherche
   */
  const getSuggestions = async (query: string) => {
    try {
      if (query.length < 2) {
        return []
      }
      
      const response = await fetch(`${baseUrl}/suggestions?q=${encodeURIComponent(query)}`)
      
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
