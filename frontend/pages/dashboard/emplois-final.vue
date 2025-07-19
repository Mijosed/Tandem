<template>
  <div class="p-6">
    <h1 class="text-3xl font-bold mb-6">🚀 Offres d'emploi</h1>
    
    <!-- Barre de recherche -->
    <div class="bg-white p-6 rounded-lg shadow mb-6">
      <div class="flex gap-4">
        <input 
          v-model="searchQuery" 
          placeholder="Rechercher des emplois (mots-clés)..." 
          class="flex-1 p-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
          @keyup.enter="loadJobs(1)"
        />
        <button 
          @click="loadJobs(1)" 
          :disabled="isLoading"
          class="bg-blue-500 text-white px-8 py-3 rounded hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ isLoading ? 'Chargement...' : 'Rechercher' }}
        </button>
      </div>
    </div>
    
    <!-- Zone de chargement -->
    <div v-if="isLoading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
      <p class="mt-2 text-blue-600">Chargement des emplois...</p>
    </div>
    
    <!-- Résultats -->
    <div v-else-if="jobs.length > 0">
      <p class="text-gray-600 mb-6">{{ jobs.length }} emplois trouvés (Page {{ pagination?.current_page || 1 }})</p>
      
      <!-- Liste des emplois -->
      <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 mb-8">
        <div 
          v-for="job in jobs" 
          :key="job.id"
          class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow border-l-4 border-blue-500"
        >
          <h3 class="font-bold text-xl mb-3 text-gray-800">{{ job.title }}</h3>
          <p class="text-lg font-semibold text-blue-600 mb-2">{{ job.company }}</p>
          <p class="text-gray-600 mb-2 flex items-center">
            <span class="mr-2">📍</span>{{ job.location }}
          </p>
          <p class="text-sm bg-gray-100 px-3 py-1 rounded inline-block mb-3">{{ job.type }}</p>
          <p v-if="job.salary" class="text-green-600 font-semibold mb-3 flex items-center">
            <span class="mr-2">💰</span>{{ job.salary }}
          </p>
          <p class="text-gray-700 text-sm mb-4 line-clamp-3">{{ job.description }}</p>
          <a 
            :href="job.sourceUrl" 
            target="_blank"
            class="inline-block bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition-colors"
          >
            Postuler →
          </a>
        </div>
      </div>
      
      <!-- 🎯 PAGINATION -->
      <div v-if="pagination" class="bg-white p-6 rounded-lg shadow">
        <h3 class="font-bold text-lg mb-4 text-center">Navigation des pages</h3>
        
        <!-- Boutons de pagination -->
        <div class="flex justify-center items-center gap-4 mb-4">
          <button 
            v-if="pagination.has_previous_page"
            @click="loadJobs(pagination.previous_page)"
            class="bg-gray-500 text-white px-6 py-3 rounded hover:bg-gray-600 transition-colors"
            :disabled="isLoading"
          >
            ← Page {{ pagination.previous_page }}
          </button>
          
          <span class="bg-blue-500 text-white px-6 py-3 rounded font-bold">
            Page {{ pagination.current_page }}
          </span>
          
          <button 
            v-if="pagination.has_next_page"
            @click="loadJobs(pagination.next_page)"
            class="bg-gray-500 text-white px-6 py-3 rounded hover:bg-gray-600 transition-colors"
            :disabled="isLoading"
          >
            Page {{ pagination.next_page }} →
          </button>
        </div>
        
        <!-- Informations de pagination -->
        <div class="text-center text-gray-600">
          <p>{{ pagination.total_results }} résultats sur cette page</p>
          <p v-if="pagination.estimated_total">Estimation: {{ pagination.estimated_total }} résultats au total</p>
        </div>
      </div>
    </div>
    
    <!-- État vide -->
    <div v-else-if="!isLoading" class="text-center py-12 bg-white rounded-lg shadow">
      <p class="text-gray-500 text-lg mb-4">Aucun emploi trouvé</p>
      <p class="text-gray-400">Cliquez sur "Rechercher" pour charger les offres disponibles</p>
    </div>
    
    <!-- Erreurs -->
    <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded mt-6">
      <h3 class="font-bold">❌ Erreur</h3>
      <p>{{ error }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

console.log('🚀 Page emplois chargée !')

// Variables réactives
const searchQuery = ref('')
const isLoading = ref(false)
const jobs = ref([])
const pagination = ref(null)
const error = ref('')

// Fonction pour charger les emplois depuis l'API Pôle Emploi
const loadJobs = async (page = 1) => {
  console.log('🔍 Chargement des emplois, page:', page)
  isLoading.value = true
  error.value = ''
  
  try {
    // Construction de l'URL avec paramètres
    const params = new URLSearchParams({ page: page.toString() })
    if (searchQuery.value.trim()) {
      params.append('motsCles', searchQuery.value.trim())
    }
    
    const url = `http://localhost:8888/api/pole-emploi/search?${params}`
    console.log('📡 URL API:', url)
    
    const response = await fetch(url)
    console.log('📈 Réponse status:', response.status)
    
    if (!response.ok) {
      throw new Error(`Erreur HTTP: ${response.status}`)
    }
    
    const data = await response.json()
    console.log('📦 Données reçues:', data)
    
    if (data.success && data.data) {
      // Transformation des données pour l'affichage
      jobs.value = data.data.jobs.map(job => ({
        id: job.id,
        title: job.title,
        company: job.company,
        location: job.location,
        type: job.contract_type || 'Non spécifié',
        salary: job.salary || null,
        description: job.description || 'Description non disponible',
        sourceUrl: job.application_url || '#'
      }))
      
      // Récupération des données de pagination
      pagination.value = data.data.pagination
      
      console.log('✅ Jobs chargés:', jobs.value.length)
      console.log('📄 Pagination:', pagination.value)
      
      // Scroll vers le haut après changement de page
      if (page > 1) {
        window.scrollTo({ top: 0, behavior: 'smooth' })
      }
    } else {
      throw new Error('Format de réponse invalide')
    }
    
  } catch (err) {
    console.error('❌ Erreur lors du chargement:', err)
    error.value = err.message
    jobs.value = []
    pagination.value = null
  } finally {
    isLoading.value = false
  }
}

// Chargement initial au montage de la page
onMounted(() => {
  console.log('🚀 Page montée, chargement initial...')
  loadJobs(1)
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
