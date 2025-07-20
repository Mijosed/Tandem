<template>
  <div class="container mx-auto py-8">
    <h1 class="text-3xl font-bold mb-8">🔧 Test Heure Entretien Debug</h1>
    
    <!-- Debug logs -->
    <div class="bg-gray-100 border rounded-lg p-4 mb-6">
      <h2 class="text-lg font-bold mb-3">📝 Logs Debug</h2>
      <div class="text-sm space-y-1 max-h-32 overflow-y-auto">
        <div v-for="(log, index) in debugLogs" :key="index" class="font-mono text-xs">
          {{ log }}
        </div>
      </div>
      <button @click="debugLogs = []" class="mt-2 text-xs bg-red-100 px-2 py-1 rounded">
        Effacer logs
      </button>
    </div>
    
    <!-- Formulaire de test -->
    <div class="bg-white border rounded-lg p-6 mb-6">
      <h2 class="text-xl font-bold mb-4">🧪 Test Création avec Heure</h2>
      
      <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
          <label class="block text-sm font-medium mb-1">Poste</label>
          <input 
            v-model="testForm.titrePoste" 
            class="border rounded px-3 py-2 w-full"
            placeholder="Test Debug Heure"
          />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Entreprise</label>
          <input 
            v-model="testForm.entreprise" 
            class="border rounded px-3 py-2 w-full"
            placeholder="Debug Corp"
          />
        </div>
      </div>
      
      <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
          <label class="block text-sm font-medium mb-1">Date d'entretien</label>
          <input 
            v-model="testForm.dateEntretien" 
            type="date"
            class="border rounded px-3 py-2 w-full"
          />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Heure d'entretien</label>
          <input 
            v-model="testForm.heureEntretien" 
            type="time"
            class="border rounded px-3 py-2 w-full"
            :disabled="!testForm.dateEntretien"
          />
          <p class="text-xs text-gray-500 mt-1">
            Valeur actuelle: "{{ testForm.heureEntretien }}" (type: {{ typeof testForm.heureEntretien }})
          </p>
        </div>
      </div>
      
      <div class="flex gap-4 mb-4">
        <button 
          @click="testCreate" 
          class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
          :disabled="!testForm.titrePoste || !testForm.entreprise || loading"
        >
          {{ loading ? 'Création...' : 'Créer Test' }}
        </button>
        
        <button 
          @click="loadCandidatures" 
          class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600"
        >
          Recharger
        </button>
      </div>
      
      <div v-if="result" class="p-3 rounded border">
        <h3 class="font-medium mb-2">Résultat :</h3>
        <pre class="text-xs bg-gray-50 p-2 rounded overflow-auto">{{ JSON.stringify(result, null, 2) }}</pre>
      </div>
    </div>

    <!-- Liste des candidatures avec debug -->
    <div class="bg-white border rounded-lg p-6">
      <h2 class="text-xl font-bold mb-4">📋 Candidatures Debug ({{ candidatures.length }})</h2>
      
      <div v-if="candidatures.length === 0" class="text-gray-500">
        Aucune candidature
      </div>
      
      <div v-else class="space-y-4">
        <div 
          v-for="candidature in candidatures.slice(0, 5)" 
          :key="candidature.id"
          class="border p-4 rounded hover:bg-gray-50"
        >
          <div class="flex justify-between items-start">
            <div class="flex-1">
              <h3 class="font-medium text-lg">{{ candidature.titrePoste }}</h3>
              <p class="text-gray-600">{{ candidature.entreprise }}</p>
              
              <div class="mt-2 grid grid-cols-2 gap-4 text-sm">
                <div>
                  <strong>Date entretien:</strong> 
                  <span class="font-mono">{{ candidature.dateEntretien || 'Non définie' }}</span>
                </div>
                <div class="bg-yellow-50 p-2 rounded">
                  <strong>Heure entretien:</strong><br/>
                  <span class="font-mono text-sm">
                    Valeur: "{{ candidature.heureEntretien }}"<br/>
                    Type: {{ typeof candidature.heureEntretien }}<br/>
                    Vide?: {{ !candidature.heureEntretien ? 'OUI' : 'NON' }}<br/>
                    Undefined?: {{ candidature.heureEntretien === undefined ? 'OUI' : 'NON' }}<br/>
                    Null?: {{ candidature.heureEntretien === null ? 'OUI' : 'NON' }}
                  </span>
                </div>
              </div>
              
              <div class="mt-2 text-xs text-gray-500">
                ID: {{ candidature.id }} | Statut: {{ candidature.statut }}
              </div>
            </div>
            
            <button 
              @click="debugCandidature(candidature)" 
              class="ml-4 text-xs bg-yellow-100 hover:bg-yellow-200 px-2 py-1 rounded"
            >
              🔍 Debug
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useCandidatures } from '~/composables/useCandidatures'

const { candidatures, loading, createCandidature, fetchCandidatures } = useCandidatures()

const debugLogs = ref<string[]>([])
const result = ref<any>(null)

const testForm = ref({
  titrePoste: 'Test Debug Heure',
  entreprise: 'Debug Corp',
  dateEntretien: '',
  heureEntretien: '15:30'
})

const addLog = (message: string) => {
  const timestamp = new Date().toLocaleTimeString()
  debugLogs.value.push(`[${timestamp}] ${message}`)
}

const testCreate = async () => {
  try {
    result.value = null
    addLog(`🚀 Début création candidature`)
    addLog(`📝 Données form: ${JSON.stringify(testForm.value)}`)
    
    const formData = {
      titrePoste: testForm.value.titrePoste,
      entreprise: testForm.value.entreprise,
      statut: 'entretien' as const,
      dateDepot: new Date().toISOString().split('T')[0],
      dateEntretien: testForm.value.dateEntretien,
      heureEntretien: testForm.value.heureEntretien,
      notes: 'Test debug heure entretien'
    }
    
    addLog(`📤 Payload envoyé: ${JSON.stringify(formData)}`)
    
    const newCandidature = await createCandidature(formData)
    
    addLog(`✅ Candidature créée: ID ${newCandidature.id}`)
    addLog(`🕐 Heure reçue: "${newCandidature.heureEntretien}" (type: ${typeof newCandidature.heureEntretien})`)
    
    result.value = newCandidature
    
    // Recharger pour voir les changements
    setTimeout(loadCandidatures, 1000)
    
  } catch (error: any) {
    addLog(`❌ Erreur: ${error.message}`)
    result.value = { error: error.message }
  }
}

const debugCandidature = (candidature: any) => {
  addLog(`🔍 Debug candidature ${candidature.id}:`)
  addLog(`   - titrePoste: "${candidature.titrePoste}"`)
  addLog(`   - entreprise: "${candidature.entreprise}"`)
  addLog(`   - dateEntretien: "${candidature.dateEntretien}"`)
  addLog(`   - heureEntretien: "${candidature.heureEntretien}" (${typeof candidature.heureEntretien})`)
  addLog(`   - heureEntretien === undefined: ${candidature.heureEntretien === undefined}`)
  addLog(`   - heureEntretien === null: ${candidature.heureEntretien === null}`)
  addLog(`   - heureEntretien === '': ${candidature.heureEntretien === ''}`)
}

const loadCandidatures = async () => {
  try {
    addLog(`🔄 Rechargement candidatures...`)
    await fetchCandidatures()
    addLog(`✅ ${candidatures.value.length} candidatures chargées`)
  } catch (error: any) {
    addLog(`❌ Erreur rechargement: ${error.message}`)
  }
}

onMounted(() => {
  // Date pour demain par défaut
  const tomorrow = new Date()
  tomorrow.setDate(tomorrow.getDate() + 1)
  testForm.value.dateEntretien = tomorrow.toISOString().split('T')[0]
  
  addLog(`🚀 Page debug chargée`)
  loadCandidatures()
})
</script>
