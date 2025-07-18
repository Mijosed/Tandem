<template>
  <div class="p-8">
    <h1 class="text-3xl font-bold mb-6">Offres d'emploi - Version Réparée</h1>
    
    <!-- Section de recherche -->
    <div class="bg-white p-6 rounded-lg shadow mb-6">
      <div class="flex gap-4">
        <input 
          v-model="searchQuery" 
          placeholder="Rechercher des emplois..." 
          class="flex-1 p-3 border border-gray-300 rounded"
          @keyup.enter="loadJobs(1)"
        />
        <button 
          @click="loadJobs(1)" 
          :disabled="isLoading"
          class="bg-blue-500 text-white px-6 py-3 rounded hover:bg-blue-600 disabled:opacity-50"
        >
          {{ isLoading ? 'Chargement...' : 'Rechercher' }}
        </button>
      </div>
    </div>
    
    <!-- Section des résultats -->
    <div v-if="jobs.length > 0">
      <p class="text-gray-600 mb-4">{{ jobs.length }} emplois trouvés</p>
      
      <!-- Liste des emplois -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 mb-6">
        <div 
          v-for="job in jobs" 
          :key="job.id"
          class="bg-white p-4 rounded-lg shadow border"
        >
          <h3 class="font-bold text-lg mb-2">{{ job.title }}</h3>
          <p class="text-gray-700 mb-1"><strong>{{ job.company }}</strong></p>
          <p class="text-gray-600 mb-2">📍 {{ job.location }}</p>
          <p class="text-sm text-gray-500 mb-2">{{ job.type }}</p>
          <p v-if="job.salary" class="text-green-600 font-semibold mb-2">💰 {{ job.salary }}</p>
          <p class="text-sm text-gray-700 line-clamp-3 mb-3">{{ job.description }}</p>
          <a 
            :href="job.sourceUrl" 
            target="_blank"
            class="inline-block bg-green-500 text-white px-4 py-2 rounded text-sm hover:bg-green-600"
          >
            Postuler →
          </a>
        </div>
      </div>
      
      <!-- PAGINATION SIMPLIFIÉE -->
      <div v-if="pagination" class="flex justify-center items-center gap-4 py-6 bg-gray-50 rounded-lg">
        <button 
          v-if="pagination.has_previous_page"
          @click="loadJobs(pagination.previous_page)"
          class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600"
          :disabled="isLoading"
        >
          ← Page {{ pagination.previous_page }}
        </button>
        
        <span class="bg-blue-500 text-white px-4 py-2 rounded">
          Page {{ pagination.current_page }}
        </span>
        
        <button 
          v-if="pagination.has_next_page"
          @click="loadJobs(pagination.next_page)"
          class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600"
          :disabled="isLoading"
        >
          Page {{ pagination.next_page }} →
        </button>
      </div>
      
      <!-- Debug pagination -->
      <details class="mt-4">
        <summary class="cursor-pointer text-sm text-gray-500">🔍 Debug Pagination</summary>
        <pre class="text-xs bg-gray-100 p-2 mt-2 rounded">{{ JSON.stringify(pagination, null, 2) }}</pre>
      </details>
    </div>
    
    <!-- État vide -->
    <div v-else-if="!isLoading" class="text-center py-12">
      <p class="text-gray-500">Aucun emploi trouvé. Cliquez sur "Rechercher" pour charger les offres.</p>
    </div>
    
    <!-- État de chargement -->
    <div v-if="isLoading" class="text-center py-12">
      <p class="text-blue-500">🔄 Chargement des emplois...</p>
    </div>
    
    <!-- Erreurs -->
    <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mt-4">
      ❌ Erreur: {{ error }}
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

definePageMeta({
  layout: 'dashboard',
})

// Variables réactives
const searchQuery = ref('')
const isLoading = ref(false)
const jobs = ref([])
const pagination = ref(null)
const error = ref('')

// Fonction pour charger les emplois
const loadJobs = async (page = 1) => {
  console.log('🔍 Chargement des emplois, page:', page)
  isLoading.value = true
  error.value = ''
  
  try {
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
      // Mapping des emplois
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
      
      // Pagination
      pagination.value = data.data.pagination
      
      console.log('✅ Jobs chargés:', jobs.value.length)
      console.log('📄 Pagination:', pagination.value)
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

// Chargement initial
onMounted(() => {
  console.log('🚀 Page montée, chargement initial...')
  loadJobs(1)
})
</script>
