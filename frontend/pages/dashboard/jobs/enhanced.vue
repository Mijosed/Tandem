<template>
  <div class="container max-w-7xl mx-auto p-4 space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold">Offres d'emploi France Travail</h1>
        <p class="text-sm text-muted-foreground mt-1">
          Recherchez parmi les offres officielles de France Travail (ex-Pôle Emploi)
        </p>
      </div>
    </div>

    <!-- Aide à la recherche -->
    <JobSearchHelp />

    <!-- Composant de filtres amélioré -->
    <JobFilters 
      :loading="isLoading" 
      @search="handleSearch" 
    />

    <!-- Statistiques de recherche -->
    <div v-if="searchPerformed" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <Info class="h-5 w-5 text-blue-600" />
          <span class="text-sm font-medium text-blue-800">
            {{ pagination?.total_results || 0 }} résultat(s) sur cette page
            <span v-if="pagination?.estimated_total">
              (≈{{ pagination.estimated_total }} au total)
            </span>
          </span>
        </div>
        <div v-if="lastSearchCriteria" class="text-xs text-blue-600">
          Page {{ pagination?.current_page || 1 }}
        </div>
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
      
      <!-- Pagination améliorée -->
      <div v-if="pagination" class="flex flex-col sm:flex-row items-center justify-between border-t pt-6 gap-4">
        <div class="text-sm text-muted-foreground text-center sm:text-left">
          Page {{ pagination.current_page }} 
          <span class="block sm:inline">
            {{ pagination.total_results }} résultat(s) sur cette page
          </span>
          <span v-if="pagination.estimated_total" class="block sm:inline">
            (≈{{ pagination.estimated_total }} total estimé)
          </span>
        </div>
        
        <div class="flex items-center gap-2">
          <!-- Bouton Précédent -->
          <Button 
            v-if="pagination.has_previous_page"
            @click="goToPage(pagination.previous_page)"
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
            @click="goToPage(pagination.next_page)"
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
    <Card v-else-if="!isLoading && searchPerformed" class="text-center py-12">
      <CardContent>
        <div class="space-y-4">
          <div class="flex justify-center">
            <SearchX class="h-16 w-16 text-muted-foreground" />
          </div>
          <div>
            <h3 class="text-lg font-semibold mb-2">Aucune offre trouvée</h3>
            <p class="text-muted-foreground max-w-md mx-auto">
              Aucune offre d'emploi ne correspond à vos critères de recherche.
              Essayez de modifier vos filtres ou d'élargir votre recherche.
            </p>
          </div>
          <Button @click="clearSearch" variant="outline">
            Nouvelle recherche
          </Button>
        </div>
      </CardContent>
    </Card>

    <!-- État initial -->
    <Card v-else-if="!isLoading && !searchPerformed" class="text-center py-12">
      <CardContent>
        <div class="space-y-4">
          <div class="flex justify-center">
            <Search class="h-16 w-16 text-muted-foreground" />
          </div>
          <div>
            <h3 class="text-lg font-semibold mb-2">Recherchez votre emploi</h3>
            <p class="text-muted-foreground max-w-md mx-auto">
              Utilisez les filtres ci-dessus pour rechercher parmi les offres d'emploi 
              officielles de France Travail.
            </p>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- État de chargement -->
    <Card v-if="isLoading" class="text-center py-12">
      <CardContent>
        <div class="space-y-4">
          <div class="flex justify-center">
            <Loader2 class="h-16 w-16 text-blue-600 animate-spin" />
          </div>
          <div>
            <h3 class="text-lg font-semibold mb-2">Recherche en cours...</h3>
            <p class="text-muted-foreground">
              Nous cherchons les meilleures offres pour vous.
            </p>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Erreurs -->
    <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4">
      <div class="flex items-center">
        <AlertCircle class="h-5 w-5 text-red-600 mr-2" />
        <div>
          <p class="text-red-800 font-medium">Erreur lors de la recherche</p>
          <p class="text-red-700 text-sm mt-1">{{ error }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Search, SearchX, Loader2, AlertCircle, Info } from 'lucide-vue-next'
import {
  Card,
  CardContent,
} from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import JobCard from '@/components/jobs/JobCard.vue'
import JobFilters from '@/components/jobs/JobFilters.vue'
import JobSearchHelp from '@/components/jobs/JobSearchHelp.vue'

// Variables réactives
const isLoading = ref(false)
const jobs = ref([])
const pagination = ref(null)
const error = ref('')
const appliedJobs = ref(new Set()) // Tracker les candidatures
const searchPerformed = ref(false)
const lastSearchCriteria = ref(null)

// Fonction pour gérer une recherche
const handleSearch = async (filters) => {
  isLoading.value = true
  error.value = ''
  lastSearchCriteria.value = filters
  
  try {
    console.log('🔍 Recherche avec filtres:', filters)
    
    // Construction de l'URL avec paramètres
    const params = new URLSearchParams()
    
    // Ajouter tous les filtres non vides
    Object.keys(filters).forEach(key => {
      if (filters[key] && filters[key] !== '') {
        params.append(key, filters[key].toString())
      }
    })
    
    const url = `http://localhost:8888/api/pole-emploi/search?${params}`
    console.log('📡 URL de recherche:', url)
    
    const response = await fetch(url)
    
    if (!response.ok) {
      throw new Error(`Erreur HTTP: ${response.status}`)
    }
    
    const data = await response.json()
    console.log('📥 Réponse API:', data)
    
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
      
      searchPerformed.value = true
      
      console.log(`✅ ${jobs.value.length} offres chargées`)
      
    } else {
      throw new Error('Format de réponse invalide')
    }
    
  } catch (err) {
    console.error('❌ Erreur lors de la recherche:', err)
    error.value = err.message
    jobs.value = []
    pagination.value = null
  } finally {
    isLoading.value = false
  }
}

// Fonction pour aller à une page spécifique
const goToPage = (page) => {
  if (lastSearchCriteria.value) {
    const newCriteria = { ...lastSearchCriteria.value, page }
    handleSearch(newCriteria)
  }
  
  // Scroll vers le haut
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

// Fonction pour effacer la recherche
const clearSearch = () => {
  jobs.value = []
  pagination.value = null
  error.value = ''
  searchPerformed.value = false
  lastSearchCriteria.value = null
}

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
      notes: `Candidature via France Travail - ${job.location}\n\nType: ${job.type}\nSalaire: ${job.salary}\n\nDescription: ${job.description.substring(0, 300)}...`,
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

// Utiliser le layout dashboard
definePageMeta({
  layout: 'dashboard'
})
</script>
