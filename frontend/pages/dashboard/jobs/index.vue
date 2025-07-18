<template>
  <div class="container max-w-7xl mx-auto p-4 space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold">Offres d'emploi</h1>
        <p class="text-sm text-muted-foreground mt-1">
          Trouvez les meilleures opportunités en alternance
        </p>
      </div>
    </div>

    <!-- Barre de recherche -->
    <div class="flex flex-col sm:flex-row gap-4">
      <div class="flex-1">
        <div class="relative">
          <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
          <Input
            v-model="searchQuery"
            placeholder="Rechercher par poste, entreprise ou lieu..."
            class="pl-9"
            @keyup.enter="loadJobs(1)"
          />
        </div>
      </div>
      <div class="flex gap-2">
        <Button variant="outline" class="w-full sm:w-auto">
          <Filter class="h-4 w-4 mr-2" />
          Filtres
        </Button>
        <Button 
          @click="loadJobs(1)" 
          :disabled="isLoading"
          class="w-full sm:w-auto"
        >
          <Loader2 
            v-if="isLoading" 
            class="h-4 w-4 mr-2 animate-spin" 
          />
          <Search v-else class="h-4 w-4 mr-2" />
          Rechercher
        </Button>
      </div>
    </div>

    <!-- Résultats -->
    <div v-if="jobs.length" class="space-y-6">
      <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        <JobCard
          v-for="job in jobs"
          :key="job.id"
          :job="job"
          @apply="handleApply"
        />
      </div>
      
      <!-- Pagination -->
      <div v-if="pagination" class="flex items-center justify-between border-t pt-6">
        <div class="text-sm text-muted-foreground">
          Page {{ pagination.current_page }} 
          ({{ pagination.total_results }} résultats sur cette page)
        </div>
        
        <div class="flex items-center gap-2">
          <!-- Bouton Précédent -->
          <Button 
            v-if="pagination.has_previous_page"
            @click="loadJobs(pagination.previous_page)"
            variant="outline"
            size="sm"
            :disabled="isLoading"
          >
            ← Précédent
          </Button>
          
          <!-- Numéro de page actuelle -->
          <Button 
            variant="default" 
            size="sm"
            disabled
          >
            {{ pagination.current_page }}
          </Button>
          
          <!-- Bouton Suivant -->
          <Button 
            v-if="pagination.has_next_page"
            @click="loadJobs(pagination.next_page)"
            variant="outline"
            size="sm"
            :disabled="isLoading"
          >
            Suivant →
          </Button>
        </div>
      </div>
    </div>

    <!-- État vide -->
    <Card v-else-if="!isLoading" class="text-center py-12">
      <CardContent>
        <p class="text-muted-foreground">
          Aucune offre d'emploi ne correspond à votre recherche.
          <br>
          Essayez de modifier vos critères de recherche.
        </p>
      </CardContent>
    </Card>

    <!-- Erreurs -->
    <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4">
      <div class="flex items-center">
        <AlertCircle class="h-5 w-5 text-red-600 mr-2" />
        <p class="text-red-800">{{ error }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Search, Filter, Loader2, AlertCircle } from 'lucide-vue-next'
import {
  Card,
  CardContent,
} from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import JobCard from '@/components/jobs/JobCard.vue'

// Variables réactives
const searchQuery = ref('')
const isLoading = ref(false)
const jobs = ref([])
const pagination = ref(null)
const error = ref('')
const appliedJobs = ref(new Set()) // Tracker les candidatures

// Fonction pour gérer une candidature
const handleApply = async (job) => {
  try {
    // Créer une candidature via l'API backend avec jobId
    const candidatureData = {
      titrePoste: job.title,
      entreprise: job.company,
      statut: 'a_faire',
      dateDepot: new Date().toISOString().split('T')[0], // Format YYYY-MM-DD
      jobId: job.id, // Ajouter l'ID du job
      notes: `Candidature via Pôle Emploi - ${job.location}\n\nType: ${job.type}\nSalaire: ${job.salary}\n\nDescription: ${job.description.substring(0, 300)}...`,
      user: '/api/users/12' // TODO: Remplacer par l'utilisateur connecté
    }
    
    console.log('📤 Création candidature:', candidatureData)
    
    const response = await fetch('http://localhost:8888/api/candidatures', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/ld+json', // Format API Platform
        'Accept': 'application/ld+json',
      },
      body: JSON.stringify(candidatureData)
    })
    
    console.log('📈 Statut réponse:', response.status)
    
    if (!response.ok) {
      const errorData = await response.text()
      console.error('❌ Erreur API:', response.status, errorData)
      
      if (response.status === 422) {
        throw new Error('Données invalides. Vérifiez les informations.')
      } else if (response.status === 404) {
        throw new Error('Utilisateur non trouvé.')
      } else {
        throw new Error(`Erreur serveur: ${response.status}`)
      }
    }
    
    const newCandidature = await response.json()
    console.log('✅ Candidature créée:', newCandidature)
    
    // Marquer le job comme candidaté
    appliedJobs.value.add(job.id)
    
    // Mettre à jour le job dans la liste
    const jobIndex = jobs.value.findIndex(j => j.id === job.id)
    if (jobIndex !== -1) {
      jobs.value[jobIndex].hasApplied = true
    }
    
    console.log(`✅ Candidature #${newCandidature.id} créée pour: ${job.title}`)
    
    // Optionnel: Afficher une notification de succès
    // toast({ title: "Candidature envoyée", description: `Candidature pour "${job.title}" créée avec succès!` })
    
  } catch (err) {
    console.error('❌ Erreur lors de la candidature:', err)
    error.value = 'Erreur lors de l\'envoi de la candidature: ' + err.message
    
    // Retirer le job des candidatures appliquées en cas d'erreur
    appliedJobs.value.delete(job.id)
  }
}

// Fonction pour vérifier les candidatures existantes
const checkExistingCandidatures = async (jobIds) => {
  try {
    const response = await fetch('http://localhost:8888/api/candidatures/check-multiple', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({ jobIds })
    })
    
    if (!response.ok) {
      console.warn('Impossible de vérifier les candidatures existantes')
      return
    }
    
    const results = await response.json()
    
    // Mettre à jour l'état des jobs avec les candidatures existantes
    jobs.value.forEach(job => {
      if (results[job.id] && results[job.id].hasApplied) {
        job.hasApplied = true
        appliedJobs.value.add(job.id)
      }
    })
    
  } catch (err) {
    console.error('Erreur lors de la vérification des candidatures:', err)
  }
}

// Fonction pour charger les emplois depuis l'API Pôle Emploi
const loadJobs = async (page = 1) => {
  isLoading.value = true
  error.value = ''
  
  try {
    // Construction de l'URL avec paramètres
    const params = new URLSearchParams({ page: page.toString() })
    if (searchQuery.value.trim()) {
      params.append('motsCles', searchQuery.value.trim())
    }
    
    const url = `http://localhost:8888/api/pole-emploi/search?${params}`
    
    const response = await fetch(url)
    
    if (!response.ok) {
      throw new Error(`Erreur HTTP: ${response.status}`)
    }
    
    const data = await response.json()
    
    if (data.success && data.data) {
      // Transformation des données vers le format JobCard
      jobs.value = data.data.jobs.map(job => ({
        id: job.id,
        title: job.title,
        company: job.company,
        location: job.location,
        type: job.contract_type || 'Non spécifié',
        salary: job.salary || 'Salaire non communiqué',
        description: job.description || 'Description non disponible',
        postedDate: job.publication_date,
        sourceUrl: job.application_url || '#',
        hasApplied: appliedJobs.value.has(job.id) // Vérifier si déjà candidaté
      }))
      
      // Récupération des données de pagination
      pagination.value = data.data.pagination
      
      // Vérifier les candidatures existantes pour ces jobs
      const jobIds = jobs.value.map(job => job.id)
      if (jobIds.length > 0) {
        await checkExistingCandidatures(jobIds)
      }
      
      // Scroll vers le haut après changement de page
      if (page > 1) {
        window.scrollTo({ top: 0, behavior: 'smooth' })
      }
    } else {
      throw new Error('Format de réponse invalide')
    }
    
  } catch (err) {
    console.error('Erreur lors du chargement:', err)
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

// Utiliser le layout dashboard
definePageMeta({
  layout: 'dashboard'
})
</script>
