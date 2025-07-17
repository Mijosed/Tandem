<template>
  <div>
    <header class="flex h-16 shrink-0 items-center gap-2 border-b transition-[width,height] ease-linear">
      <div class="flex items-center gap-2 px-4">
        <SidebarTrigger v-if="isMobile" class="-ml-1" />
        <h1 class="text-2xl font-bold">Test API Applications</h1>
      </div>
    </header>

    <div class="container py-6 px-4">
      <!-- Section de test pour créer une candidature -->
      <div class="border rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">Test: Créer une candidature</h2>
        
        <form @submit.prevent="testCreateApplication" class="space-y-4">
          <div>
            <label class="block text-sm font-medium mb-1">Poste</label>
            <Input v-model="testForm.position" type="text" required />
          </div>
          
          <div>
            <label class="block text-sm font-medium mb-1">Entreprise</label>
            <Input v-model="testForm.company" type="text" required />
          </div>
          
          <div>
            <label class="block text-sm font-medium mb-1">Date de candidature</label>
            <Input v-model="testForm.applicationDate" type="date" required />
          </div>
          
          <div>
            <label class="block text-sm font-medium mb-1">Statut</label>
            <Select v-model="testForm.status">
              <SelectTrigger>
                <SelectValue placeholder="Sélectionner un statut" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="pending">En attente</SelectItem>
                <SelectItem value="followed_up">Relancé</SelectItem>
                <SelectItem value="interview">Entretien</SelectItem>
                <SelectItem value="accepted">Accepté</SelectItem>
                <SelectItem value="rejected">Refusé</SelectItem>
              </SelectContent>
            </Select>
          </div>
          
          <div>
            <label class="block text-sm font-medium mb-1">Notes</label>
            <Textarea v-model="testForm.notes" />
          </div>
          
          <Button type="submit" :disabled="loading">
            {{ loading ? 'Création...' : 'Créer la candidature' }}
          </Button>
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
      <div class="border rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">Candidatures existantes</h2>
        
        <Button @click="fetchApplications" :disabled="loading" variant="secondary" class="mb-4">
          {{ loading ? 'Chargement...' : 'Rafraîchir' }}
        </Button>
        
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
        
        <Button @click="fetchJobs" :disabled="loadingJobs" variant="outline" class="mb-4">
          {{ loadingJobs ? 'Chargement...' : 'Charger les jobs' }}
        </Button>
        
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
import { Button } from '~/components/ui/button'
import { Input } from '~/components/ui/input'
import { Textarea } from '~/components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '~/components/ui/select'
import { SidebarTrigger } from '~/components/ui/sidebar'
import { useMediaQuery } from '@vueuse/core'
import type { ApplicationStatus } from '~/types/application'

definePageMeta({
  layout: 'dashboard'
})

// Détection mobile pour afficher conditionnellement le SidebarTrigger
const isMobile = useMediaQuery('(max-width: 768px)')

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
    const response = await fetch('http://localhost:8888/api/jobs')
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
