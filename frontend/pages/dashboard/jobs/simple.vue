<template>
<div class="p-8">
  <h1 class="text-2xl font-bold mb-4">Jobs - Version Simple (SANS LAYOUT)</h1>
  
  <div class="space-y-4">
    <!-- Test basique -->
    <div class="p-4 bg-blue-100 rounded">
      <p>Vue.js fonctionne: ✅ OUI</p>
      <button @click="increment" class="bg-blue-500 text-white px-4 py-2 rounded mt-2">
        Count: {{ counter }}
      </button>
    </div>
    
    <!-- Test API -->
    <div class="p-4 bg-green-100 rounded">
      <button @click="loadJobs" :disabled="isLoading" class="bg-green-500 text-white px-4 py-2 rounded">
        {{ isLoading ? 'Chargement...' : 'Charger les emplois' }}
      </button>
      
      <div v-if="jobs.length > 0" class="mt-4">
        <p class="font-bold">{{ jobs.length }} emplois chargés:</p>
        <div class="grid gap-2 mt-2">
          <div v-for="job in jobs.slice(0, 3)" :key="job.id" class="p-2 bg-white rounded border">
            <h3 class="font-semibold">{{ job.title }}</h3>
            <p class="text-sm text-gray-600">{{ job.company }} - {{ job.location }}</p>
          </div>
        </div>
      </div>
      
      <div v-if="error" class="mt-4 p-2 bg-red-100 text-red-700 rounded">
        Erreur: {{ error }}
      </div>
    </div>
    
    <!-- Test pagination -->
    <div v-if="pagination" class="p-4 bg-yellow-100 rounded">
      <h3 class="font-bold">Pagination détectée:</h3>
      <pre class="text-xs mt-2">{{ JSON.stringify(pagination, null, 2) }}</pre>
      
      <div class="flex gap-2 mt-4">
        <button 
          v-if="pagination.has_previous_page"
          @click="goToPage(pagination.previous_page)"
          class="bg-gray-500 text-white px-4 py-2 rounded"
        >
          ← Page {{ pagination.previous_page }}
        </button>
        
        <button class="bg-blue-500 text-white px-4 py-2 rounded" disabled>
          Page {{ pagination.current_page }}
        </button>
        
        <button 
          v-if="pagination.has_next_page"
          @click="goToPage(pagination.next_page)"
          class="bg-gray-500 text-white px-4 py-2 rounded"
        >
          Page {{ pagination.next_page }} →
        </button>
      </div>
    </div>
  </div>
</div>
</template>

<script setup>
// PAS DE LAYOUT POUR ÉVITER LES PROBLÈMES

import { ref } from 'vue'

// Variables de test
const testMessage = ref('✅ Oui!')
const counter = ref(0)
const isLoading = ref(false)
const jobs = ref([])
const pagination = ref(null)
const error = ref('')

// Fonctions de test
const increment = () => {
  counter.value++
  console.log('Counter:', counter.value)
}

const goToPage = (page) => {
  console.log('Navigation vers page:', page)
  loadJobs(page)
}

const loadJobs = async (page = 1) => {
  console.log('Chargement des emplois, page:', page)
  isLoading.value = true
  error.value = ''
  
  try {
    const url = `http://localhost:8888/api/pole-emploi/search?page=${page}`
    console.log('URL API:', url)
    
    const response = await fetch(url)
    console.log('Réponse status:', response.status)
    
    if (!response.ok) {
      throw new Error(`Erreur HTTP: ${response.status}`)
    }
    
    const data = await response.json()
    console.log('Données reçues:', data)
    
    if (data.success && data.data) {
      jobs.value = data.data.jobs || []
      pagination.value = data.data.pagination || null
      console.log('Jobs chargés:', jobs.value.length)
      console.log('Pagination:', pagination.value)
    } else {
      throw new Error('Format de réponse invalide')
    }
    
  } catch (err) {
    console.error('Erreur lors du chargement:', err)
    error.value = err.message
  } finally {
    isLoading.value = false
  }
}
</script>
