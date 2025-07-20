import { ref, computed, readonly } from 'vue'
import type { Candidature, CandidatureStatus, CandidatureStats, CandidatureFormData } from '~/types/candidature'

// Fonction utilitaire pour convertir une date ISO vers le format yyyy-MM-dd
const formatDateFromAPI = (isoDate: string | null): string => {
  if (!isoDate) return ''
  return isoDate.split('T')[0] // Prend seulement la partie date avant le 'T'
}

// Helper pour gérer les notifications sans problème de dépendance circulaire
const handleNotifications = async () => {
  try {
    const { useNotifications } = await import('~/composables/useNotifications')
    return useNotifications()
  } catch (error) {
    console.error('Erreur lors de l\'import des notifications:', error)
    return null
  }
}

// Fonction utilitaire pour transformer les données de l'API vers le format de l'interface
const transformCandidatureFromAPI = (apiCandidature: any): Candidature => {
  const transformed = {
    id: apiCandidature.id,
    titrePoste: apiCandidature.titrePoste,
    entreprise: apiCandidature.entreprise,
    statut: apiCandidature.statut,
    dateDepot: formatDateFromAPI(apiCandidature.dateDepot),
    dateEntretien: formatDateFromAPI(apiCandidature.dateEntretien),
    heureEntretien: apiCandidature.heureEntretien || '',
    notes: apiCandidature.notes,
    utilisateur: apiCandidature.user,
    dateCreation: apiCandidature.dateCreation
  }
  
  return transformed
}

export const useCandidatures = () => {
  const candidatures = ref<Candidature[]>([])
  const loading = ref(false)
  const error = ref('')

  const apiBase = 'http://localhost:8888/api'


  // Fonction utilitaire pour les headers JWT
  const getJwtHeaders = () => {
    const token = typeof window !== 'undefined' ? localStorage.getItem('jwt') : null
    return token ? { Authorization: `Bearer ${token}` } : {}
  }


  const fetchCandidatures = async () => {
    loading.value = true
    error.value = ''
    
    try {
      const userData = typeof window !== 'undefined' ? JSON.parse(localStorage.getItem('user') || '{}') : {}
      
      const userId = userData.id || 12
      
      const response = await fetch(`${apiBase}/candidatures?user.id=${userId}`, {
        headers: getJwtHeaders()
      })
      
      if (!response.ok) {
        const errorText = await response.text()
        throw new Error(`HTTP ${response.status}: ${errorText}`)
      }
      
      const data = await response.json()
      

      // Vérifier que data contient des candidatures (soit 'hydra:member' soit 'member')
      const candidaturesData = data['hydra:member'] || data.member || []
      
      if (!candidaturesData || !Array.isArray(candidaturesData)) {
        console.error('Pas de données candidatures dans la réponse:', data)
        candidatures.value = []
        return
      }
      
      candidatures.value = candidaturesData.map(transformCandidatureFromAPI)

      
    } catch (err: any) {
      error.value = err.message || 'Erreur lors du chargement des candidatures'
      console.error('Erreur fetchCandidatures:', err)
    } finally {
      loading.value = false
    }
  }

  const createCandidature = async (candidatureData: CandidatureFormData) => {
    loading.value = true
    error.value = ''
    
    try {
      const userData = typeof window !== 'undefined' ? JSON.parse(localStorage.getItem('user') || '{}') : {}
      
      const userId = userData.id || 12
      
      const formatDateForAPI = (dateString: string) => {
        if (!dateString) return null
        return new Date(dateString + 'T00:00:00Z').toISOString()
      }
      
      // Préparer le payload pour l'API avec conversion des dates
      const candidaturePayload = {
        titrePoste: candidatureData.titrePoste,
        entreprise: candidatureData.entreprise,
        statut: candidatureData.statut,
        dateDepot: formatDateForAPI(candidatureData.dateDepot),
        dateEntretien: candidatureData.dateEntretien ? formatDateForAPI(candidatureData.dateEntretien) : null,
        heureEntretien: candidatureData.heureEntretien || null,
        notes: candidatureData.notes || '',
        user: `/api/users/${userId}`
      }
      
      console.log('Payload candidature avec heure:', candidaturePayload)
      
      const response = await fetch(`${apiBase}/candidatures`, {
        method: 'POST',
        headers: {
          ...getJwtHeaders(),
          'Content-Type': 'application/ld+json',
        },
        body: JSON.stringify(candidaturePayload)
      })
      
      if (!response.ok) {
        const errorText = await response.text()
        throw new Error(`HTTP ${response.status}: ${errorText}`)
      }
      
      const newCandidature = await response.json()
      console.log('Réponse API candidature:', newCandidature)
      
      const transformedCandidature = transformCandidatureFromAPI(newCandidature)
      candidatures.value.push(transformedCandidature)

      // Créer une notification si une date d'entretien est définie lors de la création
      if (candidatureData.dateEntretien) {
        try {
          console.log('Tentative de création de notification pour:', {
            titre: candidatureData.titrePoste,
            entreprise: candidatureData.entreprise,
            date: candidatureData.dateEntretien,
            heure: candidatureData.heureEntretien
          })
          
          const notificationsComposable = await handleNotifications()
          if (notificationsComposable) {
            const { createInterviewNotification, fetchNotifications } = notificationsComposable
            
            await createInterviewNotification(
              candidatureData.titrePoste,
              candidatureData.entreprise,
              candidatureData.dateEntretien,
              newCandidature.id,
              candidatureData.heureEntretien
            )
            
            console.log('Notification créée avec succès')
            
            // Petit délai pour s'assurer que la notification est bien enregistrée
            await new Promise(resolve => setTimeout(resolve, 500))
            
            // Rafraîchir les notifications pour les afficher immédiatement
            await fetchNotifications()
            
            console.log('Notifications rafraîchies')
          }
        } catch (notifError) {
          console.error('Erreur lors de la création de la notification d\'entretien:', notifError)
          // Ne pas faire échouer la création de la candidature si la notification échoue
        }
      }
      
      return transformedCandidature
      
    } catch (err: any) {
      error.value = err.message || 'Erreur lors de la création de la candidature'
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateCandidature = async (id: number, candidatureData: Partial<Candidature>) => {
    loading.value = true
    error.value = ''
    
    try {
      const currentCandidature = candidatures.value.find(c => c.id === id)
      if (!currentCandidature) {
        throw new Error('Candidature non trouvée')
      }
      
      const formatDateForAPI = (dateString: string) => {
        if (!dateString) return null
        return new Date(dateString + 'T00:00:00Z').toISOString()
      }
      
      const updatePayload: any = {
        titrePoste: candidatureData.titrePoste || currentCandidature.titrePoste,
        entreprise: candidatureData.entreprise || currentCandidature.entreprise,
        statut: candidatureData.statut || currentCandidature.statut,
        dateDepot: candidatureData.dateDepot ? formatDateForAPI(candidatureData.dateDepot) : formatDateForAPI(currentCandidature.dateDepot),
        dateEntretien: candidatureData.dateEntretien ? formatDateForAPI(candidatureData.dateEntretien) : (currentCandidature.dateEntretien ? formatDateForAPI(currentCandidature.dateEntretien) : null),
        heureEntretien: candidatureData.heureEntretien !== undefined ? candidatureData.heureEntretien : currentCandidature.heureEntretien,
        notes: candidatureData.notes !== undefined ? candidatureData.notes : currentCandidature.notes,
        user: currentCandidature.utilisateur
      }
      
      console.log('Payload update candidature avec heure:', updatePayload)
      
      const response = await fetch(`${apiBase}/candidatures/${id}`, {
        method: 'PUT',
        headers: {
          ...getJwtHeaders(),
          'Content-Type': 'application/ld+json',
        },
        body: JSON.stringify(updatePayload)
      })
      
      if (!response.ok) {
        const errorData = await response.json().catch(() => ({ error: 'Erreur lors de la mise à jour' }))
        throw new Error(errorData.error || `HTTP ${response.status}: ${response.statusText}`)
      }
      
      const updatedCandidature = await response.json()
      console.log('Réponse API update candidature:', updatedCandidature)
      
      const index = candidatures.value.findIndex(c => c.id === id)
      if (index !== -1) {
        candidatures.value[index] = transformCandidatureFromAPI(updatedCandidature)
      }

      // Créer une notification si une date d'entretien a été ajoutée
      const hadInterviewDate = currentCandidature.dateEntretien && currentCandidature.dateEntretien !== ''
      const hasNewInterviewDate = candidatureData.dateEntretien && candidatureData.dateEntretien !== ''
      
      if (!hadInterviewDate && hasNewInterviewDate) {
        try {
          console.log('Tentative de création de notification pour mise à jour:', {
            titre: candidatureData.titrePoste || currentCandidature.titrePoste,
            entreprise: candidatureData.entreprise || currentCandidature.entreprise,
            date: candidatureData.dateEntretien,
            heure: candidatureData.heureEntretien || currentCandidature.heureEntretien
          })
          
          const { createInterviewNotification, fetchNotifications } = useNotifications()
          
          await createInterviewNotification(
            candidatureData.titrePoste || currentCandidature.titrePoste,
            candidatureData.entreprise || currentCandidature.entreprise,
            candidatureData.dateEntretien,
            id,
            candidatureData.heureEntretien || currentCandidature.heureEntretien
          )
          
          console.log('Notification de mise à jour créée avec succès')
          
          // Petit délai pour s'assurer que la notification est bien enregistrée
          await new Promise(resolve => setTimeout(resolve, 500))
          
          // Rafraîchir les notifications pour les afficher immédiatement
          await fetchNotifications()
          
          console.log('Notifications rafraîchies après mise à jour')
        } catch (notifError) {
          console.error('Erreur lors de la création de la notification d\'entretien:', notifError)
          // Ne pas faire échouer la mise à jour de la candidature si la notification échoue
        }
      }
      
      // Si la date d'entretien a été supprimée, supprimer les notifications associées
      if (hadInterviewDate && !hasNewInterviewDate) {
        try {
          const { deleteInterviewNotificationsForCandidature, fetchNotifications } = useNotifications()
          
          await deleteInterviewNotificationsForCandidature(id)
          console.log('Notifications d\'entretien supprimées')
          await fetchNotifications()
        } catch (notifError) {
          console.error('Erreur lors de la suppression des notifications d\'entretien:', notifError)
        }
      }
      
      return transformCandidatureFromAPI(updatedCandidature)
      
    } catch (err: any) {
      error.value = err.message || 'Erreur lors de la mise à jour de la candidature'
      throw err
    } finally {
      loading.value = false
    }
  }

  const deleteCandidature = async (id: number) => {
    loading.value = true
    error.value = ''
    
    try {
      // Supprimer d'abord les notifications d'entretien associées
      try {
        const { deleteInterviewNotificationsForCandidature } = useNotifications()
        
        await deleteInterviewNotificationsForCandidature(id)
        console.log('Notifications d\'entretien supprimées avant suppression candidature')
      } catch (notifError) {
        console.error('Erreur lors de la suppression des notifications:', notifError)
        // Continuer même si la suppression des notifications échoue
      }
      
      const response = await fetch(`${apiBase}/candidatures/${id}`, {
        method: 'DELETE',
        headers: getJwtHeaders()
      })
      
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`)
      }
      
      candidatures.value = candidatures.value.filter(c => c.id !== id)
      
    } catch (err: any) {
      error.value = err.message || 'Erreur lors de la suppression de la candidature'
      throw err
    } finally {
      loading.value = false
    }
  }


  const stats = computed<CandidatureStats>(() => {

  const updateCandidatureStatus = async (id: number, statut: CandidatureStatus) => {
    return updateCandidature(id, { statut })
  }

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
      tauxReussite: total > 0 ? Math.round(((accepte) / total) * 100) : 0
    }
  })


  // Candidatures avec entretien programmé
  const candidaturesAvecEntretien = computed(() => 
    candidatures.value.filter(c => c.dateEntretien && c.dateEntretien !== '')
  )

  // Prochains entretiens (dans les 7 prochains jours)
  const prochainsEntretiens = computed(() => {
    const maintenant = new Date()
    const dansSeptJours = new Date()
    dansSeptJours.setDate(maintenant.getDate() + 7)
    
    return candidatures.value
      .filter(c => {
        if (!c.dateEntretien) return false
        const dateEntretien = new Date(c.dateEntretien)
        return dateEntretien >= maintenant && dateEntretien <= dansSeptJours
      })
      .sort((a, b) => new Date(a.dateEntretien!).getTime() - new Date(b.dateEntretien!).getTime())
  })

  return {
    candidatures: readonly(candidatures),
    loading: readonly(loading),
    error: readonly(error),
    stats,
    candidaturesAvecEntretien,
    prochainsEntretiens,
    fetchCandidatures,
    createCandidature,
    updateCandidature,
    deleteCandidature

  }
}
