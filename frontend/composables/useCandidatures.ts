import { ref, computed } from 'vue'
import type { Candidature, CandidatureStatus, CandidatureStats, CandidatureFormData } from '~/types/candidature'

// Fonction utilitaire pour convertir une date ISO vers le format yyyy-MM-dd
const formatDateFromAPI = (isoDate: string | null): string => {
  if (!isoDate) return ''
  return isoDate.split('T')[0] // Prend seulement la partie date avant le 'T'
}

// Fonction utilitaire pour transformer les données de l'API vers le format de l'interface
const transformCandidatureFromAPI = (apiCandidature: any): Candidature => {
  return {
    id: apiCandidature.id,
    titrePoste: apiCandidature.titrePoste,
    entreprise: apiCandidature.entreprise,
    statut: apiCandidature.statut,
    dateDepot: formatDateFromAPI(apiCandidature.dateDepot),
    dateEntretien: formatDateFromAPI(apiCandidature.dateEntretien),
    notes: apiCandidature.notes,
    utilisateur: apiCandidature.user,
    dateCreation: apiCandidature.dateCreation
  }
}

export const useCandidatures = () => {
  const candidatures = ref<Candidature[]>([])
  const loading = ref(false)
  const error = ref('')

  const apiBase = 'http://localhost:8888/api'

  // Récupérer toutes les candidatures de l'utilisateur connecté
  const fetchCandidatures = async () => {
    loading.value = true
    error.value = ''
    
    try {
      // Récupérer l'utilisateur connecté
      const userData = typeof window !== 'undefined' ? JSON.parse(localStorage.getItem('user') || '{}') : {}
      
      // Utiliser l'utilisateur 12 par défaut si pas d'utilisateur en localStorage (pour les tests)
      const userId = userData.id || 12
      
      if (!userId) {
        throw new Error('Utilisateur non connecté')
      }

      // Récupérer toutes les candidatures et filtrer côté client
      const response = await fetch(`${apiBase}/candidatures`)
      
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`)
      }
      
      const data = await response.json()
      const allCandidatures = data.member || []
      
      // Filtrer côté client par utilisateur
      const userCandidatures = allCandidatures.filter((candidature: any) => {
        return candidature.user === `/api/users/${userId}` || 
               (candidature.user && candidature.user.id === userId)
      })
      
      // Transformer les données filtrées
      candidatures.value = userCandidatures.map(transformCandidatureFromAPI)
      
    } catch (err: any) {
      error.value = err.message || 'Erreur lors de la récupération des candidatures'
      console.error('Erreur lors de la récupération des candidatures:', err)
    } finally {
      loading.value = false
    }
  }

  // Créer une nouvelle candidature
  const createCandidature = async (candidatureData: CandidatureFormData) => {
    loading.value = true
    error.value = ''
    
    try {
      const userData = typeof window !== 'undefined' ? JSON.parse(localStorage.getItem('user') || '{}') : {}
      
      // Utiliser l'utilisateur 12 par défaut si pas d'utilisateur en localStorage (pour les tests)
      const userId = userData.id || 12
      
      // Fonction pour convertir date YYYY-MM-DD en format ISO avec timezone
      const formatDateForAPI = (dateString: string) => {
        if (!dateString) return null
        return new Date(dateString + 'T00:00:00Z').toISOString()
      }
      
      const candidaturePayload: any = {
        titrePoste: candidatureData.titrePoste,
        entreprise: candidatureData.entreprise,
        statut: candidatureData.statut,
        dateDepot: formatDateForAPI(candidatureData.dateDepot),
        dateEntretien: candidatureData.dateEntretien ? formatDateForAPI(candidatureData.dateEntretien) : null,
        notes: candidatureData.notes || '',
        user: `/api/users/${userId}`
      }
      
      const response = await fetch(`${apiBase}/candidatures`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/ld+json',
        },
        body: JSON.stringify(candidaturePayload)
      })
      
      if (!response.ok) {
        const errorText = await response.text()
        throw new Error(`HTTP ${response.status}: ${errorText}`)
      }
      
      const newCandidature = await response.json()
      
      // Transformer la réponse pour l'interface
      const transformedCandidature = transformCandidatureFromAPI(newCandidature)
      candidatures.value.push(transformedCandidature)
      
      return transformedCandidature
      
    } catch (err: any) {
      error.value = err.message || 'Erreur lors de la création de la candidature'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Mettre à jour une candidature existante
  const updateCandidature = async (id: number, candidatureData: Partial<Candidature>) => {
    loading.value = true
    error.value = ''
    
    try {
      // Récupérer la candidature existante d'abord
      const currentCandidature = candidatures.value.find(c => c.id === id)
      if (!currentCandidature) {
        throw new Error('Candidature non trouvée')
      }
      
      // Fonction pour convertir date YYYY-MM-DD en format ISO avec timezone
      const formatDateForAPI = (dateString: string) => {
        if (!dateString) return null
        return new Date(dateString + 'T00:00:00Z').toISOString()
      }
      
      // Fusionner avec les données existantes et convertir les dates
      const updatePayload: any = {
        titrePoste: candidatureData.titrePoste || currentCandidature.titrePoste,
        entreprise: candidatureData.entreprise || currentCandidature.entreprise,
        statut: candidatureData.statut || currentCandidature.statut,
        dateDepot: candidatureData.dateDepot ? formatDateForAPI(candidatureData.dateDepot) : formatDateForAPI(currentCandidature.dateDepot),
        dateEntretien: candidatureData.dateEntretien ? formatDateForAPI(candidatureData.dateEntretien) : (currentCandidature.dateEntretien ? formatDateForAPI(currentCandidature.dateEntretien) : null),
        notes: candidatureData.notes !== undefined ? candidatureData.notes : currentCandidature.notes,
        user: currentCandidature.utilisateur
      }
      
      const response = await fetch(`${apiBase}/candidatures/${id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/ld+json',
        },
        body: JSON.stringify(updatePayload)
      })
      
      if (!response.ok) {
        const errorData = await response.json().catch(() => ({ error: 'Erreur lors de la mise à jour' }))
        throw new Error(errorData.error || `HTTP ${response.status}: ${response.statusText}`)
      }
      
      const updatedCandidature = await response.json()
      
      // Mettre à jour la liste locale
      const index = candidatures.value.findIndex(c => c.id === id)
      if (index !== -1) {
        candidatures.value[index] = transformCandidatureFromAPI(updatedCandidature)
      }
      
      return updatedCandidature
      
    } catch (err: any) {
      error.value = err.message || 'Erreur lors de la mise à jour de la candidature'
      console.error('Erreur lors de la mise à jour de la candidature:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  // Supprimer une candidature
  const deleteCandidature = async (id: number) => {
    loading.value = true
    error.value = ''
    
    try {
      const response = await fetch(`${apiBase}/candidatures/${id}`, {
        method: 'DELETE'
      })
      
      if (!response.ok) {
        const errorData = await response.json().catch(() => ({ error: 'Erreur lors de la suppression' }))
        throw new Error(errorData.error || `HTTP ${response.status}: ${response.statusText}`)
      }
      
      // Retirer de la liste locale
      candidatures.value = candidatures.value.filter(c => c.id !== id)
      
    } catch (err: any) {
      error.value = err.message || 'Erreur lors de la suppression de la candidature'
      console.error('Erreur lors de la suppression de la candidature:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  // Mettre à jour uniquement le statut d'une candidature
  const updateCandidatureStatus = async (id: number, statut: CandidatureStatus) => {
    return updateCandidature(id, { statut })
  }

  // Statistiques calculées selon votre modèle
  const stats = computed((): CandidatureStats => {
    const total = candidatures.value.length
    const aFaire = candidatures.value.filter(c => c.statut === 'a_faire').length
    const enAttente = candidatures.value.filter(c => c.statut === 'en_attente').length
    const relance = candidatures.value.filter(c => c.statut === 'relance').length
    const entretien = candidatures.value.filter(c => c.statut === 'entretien').length
    const accepte = candidatures.value.filter(c => c.statut === 'accepte').length
    const refuse = candidatures.value.filter(c => c.statut === 'refuse').length
    
    return {
      total,
      aFaire,
      enAttente,
      relance,
      entretien,
      accepte,
      refuse,
      tauxReussite: total > 0 ? Math.round((accepte / total) * 100) : 0
    }
  })

  // Candidatures récentes (dernières 5)
  const candidaturesRecentes = computed(() => {
    return [...candidatures.value]
      .sort((a, b) => new Date(b.dateDepot).getTime() - new Date(a.dateDepot).getTime())
      .slice(0, 5)
  })

  // Candidatures par statut
  const candidaturesParStatut = computed(() => {
    const parStatut: Record<string, Candidature[]> = {}
    candidatures.value.forEach(candidature => {
      if (!parStatut[candidature.statut]) {
        parStatut[candidature.statut] = []
      }
      parStatut[candidature.statut].push(candidature)
    })
    return parStatut
  })

  // Recherche et filtrage
  const searchCandidatures = (query: string, statusFilter?: string[]) => {
    return candidatures.value.filter(candidature => {
      const matchesQuery = !query || 
        candidature.titrePoste.toLowerCase().includes(query.toLowerCase()) ||
        candidature.entreprise.toLowerCase().includes(query.toLowerCase()) ||
        (candidature.notes && candidature.notes.toLowerCase().includes(query.toLowerCase()))
      
      const matchesStatus = !statusFilter || statusFilter.length === 0 || statusFilter.includes(candidature.statut)
      
      return matchesQuery && matchesStatus
    })
  }

  return {
    // État
    candidatures,
    loading,
    error,
    
    // Données calculées
    stats,
    candidaturesRecentes,
    candidaturesParStatut,
    
    // Actions CRUD
    fetchCandidatures,
    createCandidature,
    updateCandidature,
    deleteCandidature,
    updateCandidatureStatus,
    
    // Utilitaires
    searchCandidatures
  }
}
