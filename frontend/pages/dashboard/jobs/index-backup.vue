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

    <!-- Statut de l'abonnement -->
    <SubscriptionStatus />

    <!-- Composant de filtres -->
    <JobFilters 
      :loading="isLoading" 
      @search="handleSearchWithFilters" 
    />

    

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
import JobFilters from '@/components/jobs/JobFilters.vue'
import SubscriptionStatus from '@/components/dashboard/SubscriptionStatus.vue'

// Variables réactives
const quickSearchQuery = ref('')
const isLoading = ref(false)
const jobs = ref([])
const pagination = ref(null)
const error = ref('')
const appliedJobs = ref(new Set()) // Tracker les candidatures

// Fonction pour gérer une recherche avec filtres
const handleSearchWithFilters = async (filters) => {
  console.log('🔍 Recherche avec filtres:', filters)
  await loadJobs(1, filters)
}

// Fonction pour recherche rapide
const quickSearch = async () => {
  if (quickSearchQuery.value.trim()) {
    const filters = { keywords: quickSearchQuery.value.trim() }
    await loadJobs(1, filters)
  }
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
    // Récupérer l'utilisateur depuis le localStorage
    const userData = typeof window !== 'undefined' ? JSON.parse(localStorage.getItem('user') || '{}') : {}
    const userId = userData.id || null
    
    const headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    }
    
    // Ajouter l'ID utilisateur dans le header si disponible
    if (userId) {
      headers['X-User-ID'] = userId.toString()
    }
    
    const response = await fetch('http://localhost:8888/api/candidatures/check-multiple', {
      method: 'POST',
      headers,
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
    console.warn('Impossible de vérifier les candidatures existantes')
  }
}

// Fonction pour charger les emplois depuis l'API France Travail
const loadJobs = async (page = 1, filters = {}) => {
  isLoading.value = true
  error.value = ''
  
  try {
    console.log('🔍 Chargement avec paramètres:', { page, ...filters })
    
    // Construction de l'URL avec paramètres
    const params = new URLSearchParams({ page: page.toString() })
    
    // Ajouter tous les filtres
    Object.keys(filters).forEach(key => {
      if (filters[key] && filters[key] !== '') {
        params.append(key, filters[key].toString())
      }
    })
    
    // Récupérer l'utilisateur depuis le localStorage pour l'authentification
    const userData = typeof window !== 'undefined' ? JSON.parse(localStorage.getItem('user') || '{}') : {}
    const userId = userData.id || null
    
    const headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    }
    
    // Ajouter l'ID utilisateur dans le header si disponible
    if (userId) {
      headers['X-User-ID'] = userId.toString()
    }
    
    const url = `http://localhost:8888/api/pole-emploi/search?${params}`
    console.log('📡 URL:', url)
    
    const response = await fetch(url, { headers })
    
    if (response.status === 403) {
      // L'utilisateur n'a pas d'abonnement premium
      const errorData = await response.json()
      if (errorData.error === 'premium_required') {
        // Rediriger vers la page premium
        await navigateTo('/premium')
        return
      }
    }
    
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
    console.error('❌ Erreur lors du chargement:', err)
    error.value = err.message
    jobs.value = []
    pagination.value = null
  } finally {
    isLoading.value = false
  }
}

// Chargement initial au montage de la page (recherche vide pour avoir des résultats)
onMounted(() => {
  console.log('🚀 Page montée, chargement initial avec recherche simple...')
  loadJobs(1, { keywords: 'alternance' }) // Recherche par défaut pour avoir des résultats
})

// Utiliser le layout dashboard
definePageMeta({
  layout: 'dashboard'
})
</script>
