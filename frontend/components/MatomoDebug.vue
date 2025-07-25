<!-- Composant de test pour vérifier le tracking Matomo -->
<template>
  <div class="fixed bottom-4 right-4 bg-white p-4 border rounded-lg shadow-lg z-50" v-if="showDebug">
    <h4 class="font-bold mb-2">Matomo Debug</h4>
    <div class="space-y-2 text-sm">
      <button 
        @click="testEvent" 
        class="block w-full bg-blue-500 text-white px-3 py-1 rounded"
      >
        Test Event
      </button>
      <button 
        @click="showQueue" 
        class="block w-full bg-green-500 text-white px-3 py-1 rounded"
      >
        Show Queue
      </button>
      <button 
        @click="toggleDebug" 
        class="block w-full bg-red-500 text-white px-3 py-1 rounded"
      >
        Hide Debug
      </button>
    </div>
    <div v-if="lastEvent" class="mt-2 p-2 bg-gray-100 rounded text-xs">
      Last: {{ lastEvent }}
    </div>
  </div>
  <button 
    v-else
    @click="toggleDebug"
    class="fixed bottom-4 right-4 bg-blue-500 text-white p-2 rounded-full shadow-lg z-50"
  >
    🐛
  </button>
</template>

<script setup>
import { ref } from 'vue'
import { useMatomo } from '@/composables/useMatomo'

const { trackEvent } = useMatomo()
const showDebug = ref(false)
const lastEvent = ref('')

const toggleDebug = () => {
  showDebug.value = !showDebug.value
}

const testEvent = () => {
  const timestamp = new Date().toLocaleTimeString()
  trackEvent('Debug', 'Test', `Test at ${timestamp}`)
  lastEvent.value = `Debug > Test > Test at ${timestamp}`
}

const showQueue = () => {
  if (typeof window !== 'undefined' && window._paq) {
    console.log('Matomo Queue:', window._paq)
    lastEvent.value = 'Queue logged to console'
  } else {
    lastEvent.value = 'Matomo not found'
  }
}

// N'afficher qu'en développement
const isDev = process.dev
</script>
