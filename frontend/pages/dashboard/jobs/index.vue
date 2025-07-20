<template>
  <div class="min-h-screen bg-gray-50">
    <div class="container max-w-7xl mx-auto py-8 px-4">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Offres d'emploi</h1>
        <p class="text-gray-600">
          Trouvez les meilleures opportunités en alternance
        </p>
      </div>

      <!-- Statut de l'abonnement -->
      <SubscriptionStatus />

      <!-- Composant de filtres -->
      <JobFilters 
        :loading="isLoading" 
        @search="handleSearchWithFilters" 
      />

      <!-- Résultats -->
      <div v-if="jobs.length" class="space-y-6">
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
          <div
            v-for="job in jobs"
            :key="job.id"
            class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow"
          >
            <div class="space-y-4">
              <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ job.title }}</h3>
                <p class="text-gray-600 font-medium">{{ job.company }}</p>
                <p class="text-sm text-gray-500">{{ job.location }}</p>
              </div>
              
              <div v-if="job.salary" class="text-sm text-green-600 font-medium">
                {{ job.salary }}
              </div>
              
              <div v-if="job.type" class="text-xs text-gray-500 uppercase tracking-wide">
                {{ job.type }}
              </div>
              
              <p class="text-gray-700 text-sm line-clamp-3">
                {{ job.description?.substring(0, 150) }}...
              </p>
              
              <div class="pt-4 border-t space-y-2">
                <!-- Bouton pour consulter l'offre -->
                <a
                  :href="job.application_url || '#'"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="w-full px-4 py-2 bg-blue-600 text-white rounded-md font-medium hover:bg-blue-700 transition-colors inline-flex items-center justify-center"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                  </svg>
                  Consulter l'offre directement sur le site source
                </a>
                
                <!-- Bouton pour enregistrer dans le suivi -->
                <button
                  @click="handleSaveToApplications(job)"
                  :disabled="appliedJobs.has(job.id.toString()) || job.hasApplied"
                  :class="[
                    'w-full px-4 py-2 rounded-md font-medium transition-colors inline-flex items-center justify-center',
                    appliedJobs.has(job.id.toString()) || job.hasApplied
                      ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                      : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'
                  ]"
                >
                  <svg v-if="appliedJobs.has(job.id.toString()) || job.hasApplied" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                  </svg>
                  {{ appliedJobs.has(job.id.toString()) || job.hasApplied ? 'Déjà enregistrée dans le suivi' : 'Enregistrer dans son suivi pour organiser ses candidatures' }}
                </button>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Pagination -->
        <div v-if="pagination" class="flex items-center justify-between border-t pt-6">
          <div class="text-sm text-gray-500">
            Page {{ pagination.current_page }} 
            ({{ pagination.total_results }} résultats sur cette page)
          </div>
          
          <div class="flex items-center gap-2">
            <!-- Bouton Précédent -->
            <button 
              v-if="pagination.has_previous_page"
              @click="loadJobs(pagination.previous_page)"
              :disabled="isLoading"
              class="px-4 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50"
            >
              ← Précédent
            </button>
            
            <!-- Bouton Suivant -->
            <button 
              v-if="pagination.has_next_page"
              @click="loadJobs(pagination.next_page)"
              :disabled="isLoading"
              class="px-4 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50"
            >
              Suivant →
            </button>
          </div>
        </div>
      </div>

      <!-- Message si pas de résultats -->
      <div v-else-if="!isLoading && !error" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune offre trouvée</h3>
        <p class="mt-1 text-sm text-gray-500">Utilisez les filtres pour trouver des opportunités.</p>
      </div>

      <!-- Loading -->
      <div v-if="isLoading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <p class="mt-2 text-sm text-gray-500">Chargement des offres...</p>
      </div>

      <!-- Message d'erreur -->
      <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
        <div class="flex">
          <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">Erreur</h3>
            <p class="text-sm text-red-700 mt-1">{{ error }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import JobFilters from '@/components/jobs/JobFilters.vue'
import SubscriptionStatus from '@/components/dashboard/SubscriptionStatus.vue'

// Variables réactives
const isLoading = ref(false)
const jobs = ref([])
const pagination = ref(null)
const error = ref('')
const appliedJobs = ref(new Set())

// Fonction pour gérer une recherche avec filtres
const handleSearchWithFilters = async (filters) => {
  await loadJobs(1, filters)
}

// Fonction pour enregistrer dans les candidatures (suivi)
const handleSaveToApplications = async (job) => {
  try {
    // Récupérer l'utilisateur connecté
    const userData = typeof window !== 'undefined' ? JSON.parse(localStorage.getItem('user') || '{}') : {}
    const userId = userData.id
    
    if (!userId) {
      throw new Error('Utilisateur non connecté')
    }

    const candidatureData = {
      titrePoste: job.title,
      entreprise: job.company,
      statut: 'a_faire',
      dateDepot: new Date().toISOString().split('T')[0],
      jobId: job.id,
      notes: `Enregistrée pour suivi via France Travail - ${job.location}\n\nType: ${job.type}\nSalaire: ${job.salary}\n\nDescription: ${job.description.substring(0, 300)}...`,
      user: `/api/users/${userId}`
    }
    
    const token = localStorage.getItem('jwt')
    const response = await fetch('http://localhost:8888/api/candidatures', {
      method: 'POST',
      headers: {
        'Authorization': token ? `Bearer ${token}` : '',
        'Content-Type': 'application/ld+json',
      },
      body: JSON.stringify(candidatureData)
    })
    
    if (!response.ok) {
      const errorText = await response.text()
      throw new Error(`Erreur ${response.status}: ${errorText}`)
    }
    
    const newCandidature = await response.json()
    
    // Marquer le job comme candidaté (s'assurer que les types correspondent)
    const jobIdStr = job.id.toString()
    appliedJobs.value.add(jobIdStr)
    
    // Mettre à jour le job dans la liste
    const jobIndex = jobs.value.findIndex(j => j.id === job.id)
    if (jobIndex !== -1) {
      jobs.value[jobIndex].hasApplied = true
    }
    
    // Message de succès (optionnel, vous pourriez ajouter une notification)
    console.log(`✅ Offre "${job.title}" enregistrée dans le suivi`)
    
  } catch (err) {
    error.value = 'Erreur lors de l\'enregistrement dans le suivi: ' + err.message
    appliedJobs.value.delete(job.id)
  }
}

// Fonction pour vérifier les candidatures existantes
const checkExistingCandidatures = async (jobIds) => {
  try {
    const userData = typeof window !== 'undefined' ? JSON.parse(localStorage.getItem('user') || '{}') : {}
    const userId = userData.id || null
    
    if (!userId || !jobIds.length) {
      return
    }
    
    const token = localStorage.getItem('jwt')
    const response = await fetch(`http://localhost:8888/api/candidatures/user/${userId}`, {
      headers: {
        'Authorization': token ? `Bearer ${token}` : '',
        'Content-Type': 'application/json',
      }
    })
    
    if (!response.ok) {
      return
    }
    
    const data = await response.json()
    const candidatures = data.member || []
    
    // Réinitialiser les candidatures appliquées
    appliedJobs.value.clear()
    
    candidatures.forEach(candidature => {
      if (candidature.jobId) {
        appliedJobs.value.add(candidature.jobId.toString())
      }
    })
    
    // Mettre à jour les jobs dans la liste
    jobs.value.forEach(job => {
      const jobIdStr = job.id.toString()
      if (appliedJobs.value.has(jobIdStr)) {
        job.hasApplied = true
      } else {
        job.hasApplied = false
      }
    })
    
  } catch (err) {
    // Erreur silencieuse pour ne pas perturber l'utilisateur
  }
}

// Fonction principale pour charger les jobs
const loadJobs = async (page = 1, filters = {}) => {
  isLoading.value = true
  error.value = ''
  
  try {
    const userData = typeof window !== 'undefined' ? JSON.parse(localStorage.getItem('user') || '{}') : {}
    const userId = userData.id
    
    if (!userId) {
      throw new Error('Utilisateur non connecté')
    }
    
    const params = new URLSearchParams({
      page: page.toString(),
      ...filters
    })
    
    const token = localStorage.getItem('jwt')
    const response = await fetch(`http://localhost:8888/api/pole-emploi/search?${params}`, {
      headers: {
        'Authorization': token ? `Bearer ${token}` : '',
        'X-User-ID': userId.toString(),
        'Content-Type': 'application/json'
      }
    })
    
    if (!response.ok) {
      const errorData = await response.json().catch(() => ({}))
      
      if (response.status === 403 && errorData.error === 'premium_required') {
        throw new Error('Un abonnement premium est requis pour accéder aux offres d\'emploi.')
      }
      
      throw new Error(`Erreur ${response.status}: ${response.statusText}`)
    }
    
    const data = await response.json()
    
    // Récupérer les données selon la structure du backend
    let jobsArray = []
    let paginationData = null
    
    if (data.success && data.data) {
      const apiData = data.data
      jobsArray = apiData.jobs || apiData.resultats || []
      paginationData = apiData.pagination
    } else {
      jobsArray = data.resultats || data.jobs || data['hydra:member'] || []
      paginationData = data.pagination || data['hydra:view']
    }
    
    jobs.value = jobsArray
    pagination.value = paginationData
    
    // Vérifier les candidatures existantes
    if (jobs.value.length > 0) {
      const jobIds = jobs.value.map(job => job.id)
      await checkExistingCandidatures(jobIds)
    }
    
  } catch (err) {
    error.value = err.message
    jobs.value = []
    pagination.value = null
  } finally {
    isLoading.value = false
  }
}

// Charger les jobs au montage du composant
onMounted(async () => {
  // Vérifier et rafraîchir le statut de l'utilisateur
  await refreshUserStatus()
  await loadJobs(1, { keywords: 'alternance' })
})

// Fonction pour rafraîchir le statut de l'utilisateur
const refreshUserStatus = async () => {
  try {
    const userData = typeof window !== 'undefined' ? JSON.parse(localStorage.getItem('user') || '{}') : {}
    const userId = userData.id
    
    if (!userId) return
    
    // Récupérer le statut d'abonnement à jour
    const token = localStorage.getItem('jwt')
    const response = await fetch(`http://localhost:8888/api/stripe/subscription-status/${userId}`, {
      headers: {
        'Authorization': token ? `Bearer ${token}` : '',
        'Content-Type': 'application/json',
      }
    })
    
    if (response.ok) {
      const subscriptionData = await response.json()
      
      // Mettre à jour les données utilisateur
      userData.isPremium = subscriptionData.isPremium || false
      userData.subscription = {
        plan: subscriptionData.plan,
        status: subscriptionData.status
      }
      
      localStorage.setItem('user', JSON.stringify(userData))
    }
  } catch (err) {
    // Silencieux en cas d'erreur
  }
}

// Meta pour le layout
definePageMeta({
  layout: 'dashboard',
  middleware: ['auth']
})
</script>

<style scoped>
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
