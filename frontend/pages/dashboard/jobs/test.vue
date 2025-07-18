<template>
  <div class="p-8">
    <h1 class="text-2xl font-bold mb-4">Test Page</h1>
    
    <div class="space-y-4">
      <p>Page de test pour vérifier si Vue fonctionne</p>
      
      <button @click="testFunction" class="bg-blue-500 text-white px-4 py-2 rounded">
        Cliquer ici - Count: {{ count }}
      </button>
      
      <div v-if="showData" class="p-4 bg-gray-100 rounded">
        <h3>Données de test:</h3>
        <pre>{{ JSON.stringify(testData, null, 2) }}</pre>
      </div>
      
      <button @click="testApi" class="bg-green-500 text-white px-4 py-2 rounded">
        Tester API
      </button>
      
      <div v-if="apiResult" class="p-4 bg-green-100 rounded">
        <h3>Résultat API:</h3>
        <pre>{{ JSON.stringify(apiResult, null, 2) }}</pre>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

definePageMeta({
  layout: 'dashboard',
})

const count = ref(0)
const showData = ref(false)
const apiResult = ref(null)

const testData = {
  message: "Test réussi!",
  timestamp: new Date().toISOString()
}

const testFunction = () => {
  count.value++
  showData.value = !showData.value
  console.log('Fonction test appelée, count:', count.value)
}

const testApi = async () => {
  try {
    console.log('Test API démarré...')
    const response = await fetch('http://localhost:8888/api/pole-emploi/search?page=1')
    console.log('Réponse reçue:', response.status)
    
    if (!response.ok) {
      throw new Error(`Erreur: ${response.status}`)
    }
    
    const data = await response.json()
    console.log('Données API:', data)
    apiResult.value = {
      success: data.success,
      jobs_count: data.data?.jobs?.length || 0,
      pagination: data.data?.pagination
    }
  } catch (error) {
    console.error('Erreur API:', error)
    apiResult.value = { error: error.message }
  }
}
</script>
