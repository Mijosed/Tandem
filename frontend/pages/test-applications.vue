<template>
  <div class="container py-8">
    <h1 class="text-3xl font-bold mb-6">Test API Applications</h1>
    
    <div class="space-y-6">
      <!-- Section de test pour créer une candidature -->
      <div class="border rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">Test: Créer une candidature</h2>
        
        <form @submit.prevent="testCreateApplication" class="space-y-4">
          <div>
            <label class="block text-sm font-medium mb-1">Poste</label>
            <input v-model="testForm.position" type="text" class="w-full p-2 border rounded" required>
          </div>
          
          <div>
            <label class="block text-sm font-medium mb-1">Entreprise</label>
            <input v-model="testForm.company" type="text" class="w-full p-2 border rounded" required>
          </div>
          
          <div>
            <label class="block text-sm font-medium mb-1">Date de candidature</label>
            <input v-model="testForm.applicationDate" type="date" class="w-full p-2 border rounded" required>
          </div>
          
          <div>
            <label class="block text-sm font-medium mb-1">Statut</label>
            <select v-model="testForm.status" class="w-full p-2 border rounded">
              <option value="pending">En attente</option>
              <option value="followed_up">Relancé</option>
              <option value="interview">Entretien</option>
              <option value="accepted">Accepté</option>
              <option value="rejected">Refusé</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium mb-1">Notes</label>
            <textarea v-model="testForm.notes" class="w-full p-2 border rounded" rows="3"></textarea>
          </div>
          
          <button type="submit" :disabled="loading" class="bg-blue-500 text-white px-4 py-2 rounded disabled:opacity-50">
            {{ loading ? 'Création...' : 'Créer la candidature' }}
          </button>
        </form>
        
        <!-- Affichage des erreurs -->
        <div v-if="error" class="mt-4 p-4 bg-red-50 text-red-700 rounded">
          <strong>Erreur:</strong> {{ error }}
        </div>
        
        <!-- Affichage du succès -->
        <div v-if="successMessage" class="mt-4 p-4 bg-green-50 text-green-700 rounded">
          <strong>Succès:</strong> {{ successMessage }}
        </div>
      </div>
      
      <!-- Section pour lister les candidatures -->
      <div class="border rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">Candidatures existantes</h2>
        
        <button @click="fetchApplications" :disabled="loading" class="mb-4 bg-gray-500 text-white px-4 py-2 rounded disabled:opacity-50">
          {{ loading ? 'Chargement...' : 'Rafraîchir' }}
        </button>
        
        <div v-if="applications.length === 0" class="text-gray-500">
          Aucune candidature trouvée
        </div>
        
        <div v-else class="space-y-4">
          <div v-for="app in applications" :key="app.id" class="p-4 border rounded bg-gray-50">
            <h3 class="font-semibold">{{ app.position }} - {{ app.company }}</h3>
            <p class="text-sm text-gray-600">Date: {{ app.applicationDate }}</p>
            <p class="text-sm text-gray-600">Statut: {{ app.status }}</p>
            <p v-if="app.notes" class="text-sm text-gray-600">Notes: {{ app.notes }}</p>
          </div>
        </div>
      </div>
      
      <!-- Section pour les jobs -->
      <div class="border rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">Jobs disponibles</h2>
        
        <button @click="fetchJobs" :disabled="loadingJobs" class="mb-4 bg-purple-500 text-white px-4 py-2 rounded disabled:opacity-50">
          {{ loadingJobs ? 'Chargement...' : 'Charger les jobs' }}
        </button>
        
        <div v-if="jobs.length === 0" class="text-gray-500">
          Aucun job trouvé
        </div>
        
        <div v-else class="space-y-2">
          <div v-for="job in jobs" :key="job.id" class="p-2 border rounded bg-blue-50">
            <p><strong>{{ job.title }}</strong> chez {{ job.company }}</p>
            <p class="text-sm text-gray-600">ID: {{ job.id }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import type { ApplicationStatus } from '~/types/application'

const { applications, loading, error, fetchApplications, createApplication } = useApplications()

const successMessage = ref('')
const loadingJobs = ref(false)
const jobs = ref<any[]>([])

const testForm = ref({
  position: 'Développeur Frontend',
  company: 'Tech Startup',
  applicationDate: new Date().toISOString().split('T')[0],
  status: 'pending' as ApplicationStatus,
  notes: 'Candidature envoyée via le site web'
})

const testCreateApplication = async () => {
  try {
    successMessage.value = ''
    error.value = ''
    
    await createApplication(testForm.value)
    successMessage.value = 'Candidature créée avec succès !'
    
    // Réinitialiser le formulaire
    testForm.value = {
      position: '',
      company: '',
      applicationDate: new Date().toISOString().split('T')[0],
      status: 'pending',
      notes: ''
    }
    
    // Rafraîchir la liste
    await fetchApplications()
    
  } catch (err) {
    console.error('Erreur lors de la création:', err)
  }
}

const fetchJobs = async () => {
  loadingJobs.value = true
  try {
    const config = useRuntimeConfig()
    const response = await fetch(`${config.public.apiBase}/jobs`)
    const data = await response.json()
    jobs.value = data.member || []
  } catch (err) {
    console.error('Erreur lors de la récupération des jobs:', err)
  } finally {
    loadingJobs.value = false
  }
}

// Charger les candidatures au démarrage
onMounted(() => {
  fetchApplications()
})
</script>
