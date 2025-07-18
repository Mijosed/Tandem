<template>
  <div style="padding: 20px; font-family: Arial, sans-serif;">
    <h1 style="color: red; font-size: 32px;">🚨 TEST DEBUG - PAGE RACINE</h1>
    
    <div style="background: lightblue; padding: 20px; margin: 10px 0; border-radius: 10px;">
      <h2>Test 1: Vue.js Basic</h2>
      <p>Compteur: <strong>{{ counter }}</strong></p>
      <button @click="increment" style="background: blue; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer;">
        Cliquer pour incrémenter
      </button>
    </div>
    
    <div style="background: lightgreen; padding: 20px; margin: 10px 0; border-radius: 10px;">
      <h2>Test 2: Affichage conditionnel</h2>
      <p v-if="showMessage">✅ Vue.js reactive fonctionne!</p>
      <p v-else>❌ Vue.js reactive ne fonctionne pas</p>
      <button @click="toggleMessage" style="background: green; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer;">
        Toggle Message
      </button>
    </div>
    
    <div style="background: lightyellow; padding: 20px; margin: 10px 0; border-radius: 10px;">
      <h2>Test 3: API Test</h2>
      <button @click="testAPI" :disabled="loading" style="background: orange; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer;">
        {{ loading ? 'Chargement...' : 'Tester API Backend' }}
      </button>
      <pre v-if="apiResult" style="background: white; padding: 10px; margin: 10px 0; border: 1px solid black;">{{ apiResult }}</pre>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const counter = ref(0)
const showMessage = ref(false)
const loading = ref(false)
const apiResult = ref('')

const increment = () => {
  counter.value++
  console.log('Compteur incrémenté:', counter.value)
}

const toggleMessage = () => {
  showMessage.value = !showMessage.value
  console.log('Message toggled:', showMessage.value)
}

const testAPI = async () => {
  console.log('Test API démarré...')
  loading.value = true
  apiResult.value = ''
  
  try {
    const response = await fetch('http://localhost:8888/api/pole-emploi/search?page=1')
    console.log('Réponse API:', response.status)
    
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`)
    }
    
    const data = await response.json()
    console.log('Données API:', data)
    
    apiResult.value = JSON.stringify({
      success: data.success,
      jobs_count: data.data?.jobs?.length || 0,
      has_pagination: !!data.data?.pagination,
      has_next_page: data.data?.pagination?.has_next_page,
      current_page: data.data?.pagination?.current_page
    }, null, 2)
    
  } catch (error) {
    console.error('Erreur API:', error)
    apiResult.value = `ERREUR: ${error.message}`
  } finally {
    loading.value = false
  }
}

// Test automatique au montage
console.log('Page de debug montée')
</script>
