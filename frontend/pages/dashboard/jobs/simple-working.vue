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
      <div v-if="!isPremium" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
        <div class="flex items-center">
          <svg class="h-5 w-5 text-yellow-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
          </svg>
          <div>
            <h3 class="text-sm font-medium text-yellow-800">
              Accès Premium requis
            </h3>
            <p class="text-sm text-yellow-700 mt-1">
              Passez au Premium pour accéder aux offres d'emploi.
              <NuxtLink to="/premium" class="font-medium underline hover:text-yellow-600">
                Voir les options
              </NuxtLink>
            </p>
          </div>
        </div>
      </div>

      <!-- Recherche -->
      <div class="bg-white rounded-lg shadow mb-6 p-6">
        <div class="flex gap-4">
          <div class="flex-1">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Rechercher par mots-clés..."
              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              @keyup.enter="searchJobs"
            />
          </div>
          <button
            @click="searchJobs"
            :disabled="isLoading"
            class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
          >
            {{ isLoading ? 'Recherche...' : 'Rechercher' }}
          </button>
        </div>
      </div>

      <!-- Résultats -->
      <div v-if="jobs.length" class="space-y-4">
        <div v-for="job in jobs" :key="job.id" class="bg-white rounded-lg shadow p-6">
          <div class="flex justify-between items-start">
            <div class="flex-1">
              <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ job.title }}</h3>
              <p class="text-gray-600 mb-2">{{ job.company }}</p>
              <p class="text-sm text-gray-500 mb-4">{{ job.location }}</p>
              <p class="text-gray-700 text-sm">{{ job.description?.substring(0, 200) }}...</p>
            </div>
            <div class="ml-4">
              <button
                @click="applyToJob(job)"
                :disabled="appliedJobs.has(job.id)"
                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:bg-gray-400"
              >
                {{ appliedJobs.has(job.id) ? 'Candidature envoyée' : 'Postuler' }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Message si pas de résultats -->
      <div v-else-if="!isLoading" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune offre trouvée</h3>
        <p class="mt-1 text-sm text-gray-500">Essayez avec d'autres mots-clés.</p>
      </div>

      <!-- Loading -->
      <div v-if="isLoading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <p class="mt-2 text-sm text-gray-500">Recherche en cours...</p>
      </div>

      <!-- Erreur -->
      <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
        <div class="flex">
          <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
          <div class="ml-3">
            <p class="text-sm text-red-800">{{ error }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

// Variables réactives
const searchQuery = ref('')
const isLoading = ref(false)
const jobs = ref([])
const error = ref('')
const appliedJobs = ref(new Set())
const isPremium = ref(false)

// Vérifier le statut premium au montage
onMounted(async () => {
  // Récupérer l'utilisateur depuis le localStorage
  const userData = typeof window !== 'undefined' ? JSON.parse(localStorage.getItem('user') || '{}') : {}
  const userId = userData.id || null

  if (userId) {
    try {
      const response = await fetch(`http://localhost:8888/api/stripe/subscription-status/${userId}`)
      const data = await response.json()
      isPremium.value = data.isPremium || false
    } catch (err) {
      console.error('Erreur lors de la vérification du statut premium:', err)
    }
  }

  // Si premium, charger quelques offres par défaut
  if (isPremium.value) {
    await searchJobs('alternance')
  }
})

// Fonction de recherche
const searchJobs = async (defaultQuery = null) => {
  if (!isPremium.value) {
    error.value = 'Accès Premium requis pour consulter les offres d\'emploi'
    return
  }

  const query = defaultQuery || searchQuery.value.trim()
  if (!query) {
    error.value = 'Veuillez saisir des mots-clés pour la recherche'
    return
  }

  isLoading.value = true
  error.value = ''

  try {
    const response = await fetch(`http://localhost:8888/api/pole-emploi/search?keywords=${encodeURIComponent(query)}&page=1`)
    
    if (!response.ok) {
      throw new Error(`Erreur ${response.status}: ${response.statusText}`)
    }

    const data = await response.json()
    jobs.value = data.resultats || []
    
    if (jobs.value.length === 0) {
      error.value = 'Aucune offre trouvée pour ces critères'
    }

  } catch (err) {
    console.error('Erreur lors de la recherche:', err)
    error.value = 'Erreur lors de la recherche: ' + err.message
    jobs.value = []
  } finally {
    isLoading.value = false
  }
}

// Fonction pour postuler
const applyToJob = async (job) => {
  try {
    // Récupérer l'utilisateur depuis le localStorage
    const userData = typeof window !== 'undefined' ? JSON.parse(localStorage.getItem('user') || '{}') : {}
    const userId = userData.id || null

    if (!userId) {
      error.value = 'Vous devez être connecté pour postuler'
      return
    }

    // Créer une candidature
    const candidatureData = {
      titrePoste: job.title,
      entreprise: job.company,
      statut: 'a_faire',
      dateDepot: new Date().toISOString().split('T')[0],
      jobId: job.id,
      notes: `Candidature via France Travail - ${job.location}`,
      user: `/api/users/${userId}`
    }

    const response = await fetch('http://localhost:8888/api/candidatures', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/ld+json',
      },
      body: JSON.stringify(candidatureData)
    })

    if (!response.ok) {
      throw new Error(`Erreur ${response.status}`)
    }

    const newCandidature = await response.json()
    appliedJobs.value.add(job.id)
    
    console.log('✅ Candidature créée:', newCandidature)

  } catch (err) {
    console.error('❌ Erreur lors de la candidature:', err)
    error.value = 'Erreur lors de l\'envoi de la candidature: ' + err.message
  }
}

// Meta
definePageMeta({
  layout: 'dashboard'
})
</script>
