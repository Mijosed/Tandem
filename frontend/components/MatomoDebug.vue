<!-- Composant de test pour vérifier le tracking Matomo avec @openmost/nuxt-matomo -->
<template>
  <div v-if="isDev" class="fixed bottom-4 right-4 z-50">
    <div v-if="showDebug" class="bg-black text-white p-4 rounded-lg shadow-lg max-w-sm">
      <h3 class="font-bold mb-2">Matomo Debug</h3>
      <div class="space-y-2 text-xs">
        <button 
          @click="testEvent" 
          class="bg-blue-500 hover:bg-blue-600 px-2 py-1 rounded text-white block w-full"
        >
          Test Event
        </button>
        <button 
          @click="showMatomoInfo" 
          class="bg-green-500 hover:bg-green-600 px-2 py-1 rounded text-white block w-full"
        >
          Show Matomo Info
        </button>
        <button 
          @click="toggleDebug" 
          class="bg-red-500 hover:bg-red-600 px-2 py-1 rounded text-white block w-full"
        >
          Hide Debug
        </button>
        <div v-if="lastEvent" class="mt-2 p-2 bg-gray-800 rounded">
          <strong>Dernier événement:</strong><br>
          {{ lastEvent }}
        </div>
      </div>
    </div>
    <button 
      v-else
      @click="toggleDebug"
      class="bg-blue-500 text-white p-2 rounded-full shadow-lg"
      title="Debug Matomo"
    >
      🐛
    </button>
  </div>
</template>

<script setup>
import { useMatomo } from '@/composables/useMatomo'

const isDev = process.env.NODE_ENV === 'development'
const { trackEvent } = useMatomo()
const { $matomo } = useNuxtApp()

const showDebug = ref(false)
const lastEvent = ref('')

const toggleDebug = () => {
  showDebug.value = !showDebug.value
}

const testEvent = () => {
  const testData = {
    category: 'Debug',
    action: 'Test',
    name: 'Debug Button Test',
    value: Date.now()
  }
  
  trackEvent(testData.category, testData.action, testData.name, testData.value)
  lastEvent.value = `${testData.category} | ${testData.action} | ${testData.name}`
  
  console.log('✅ Test event sent:', testData)
}

const showMatomoInfo = () => {
  console.log('📊 Matomo instance:', $matomo)
  if ($matomo) {
    console.log('📊 Matomo methods available:', Object.keys($matomo))
  } else {
    console.log('❌ Matomo not loaded yet')
  }
  lastEvent.value = 'Check console for Matomo info'
}
</script>
