<template>
  <div>
    <header class="flex h-16 shrink-0 items-center gap-2 border-b transition-[width,height] ease-linear">
      <div class="flex items-center gap-2 px-4">
        <SidebarTrigger v-if="isMobile" class="-ml-1" />
        <h1 class="text-2xl font-bold">Mes candidatures</h1>
      </div>
    </header>

    <div class="container py-6 px-4">
      <!-- Affichage des erreurs -->
      <div v-if="error" class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
        <p class="text-red-800">{{ error }}</p>
        <Button @click="fetchApplications" variant="outline" size="sm" class="mt-2">
          Réessayer
        </Button>
      </div>

      <!-- Statistiques rapides -->
      <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white border rounded-lg p-4 text-center">
          <div class="text-2xl font-bold text-blue-600">{{ stats.total }}</div>
          <div class="text-sm text-muted-foreground">Total</div>
        </div>
        <div class="bg-white border rounded-lg p-4 text-center">
          <div class="text-2xl font-bold text-amber-600">{{ stats.pending }}</div>
          <div class="text-sm text-muted-foreground">En attente</div>
        </div>
        <div class="bg-white border rounded-lg p-4 text-center">
          <div class="text-2xl font-bold text-purple-600">{{ stats.interview }}</div>
          <div class="text-sm text-muted-foreground">Entretiens</div>
        </div>
        <div class="bg-white border rounded-lg p-4 text-center">
          <div class="text-2xl font-bold text-green-600">{{ stats.accepted }}</div>
          <div class="text-sm text-muted-foreground">Acceptées</div>
        </div>
        <div class="bg-white border rounded-lg p-4 text-center">
          <div class="text-2xl font-bold text-red-600">{{ stats.rejected }}</div>
          <div class="text-sm text-muted-foreground">Refusées</div>
        </div>
      </div>

      <div class="flex justify-between items-center mb-8">
        <p class="text-muted-foreground">Gérez et suivez vos candidatures</p>
        <Button @click="openNewApplicationDialog" :disabled="loading">
          <Plus class="mr-2 h-4 w-4" />
          Nouvelle candidature
        </Button>
      </div>

      <ApplicationsList 
        :applications="applications" 
        :loading="loading"
        @edit="editApplication"
        @delete="handleDeleteApplication"
        @update-status="handleUpdateStatus"
        @refresh="fetchApplications" 
      />
      
      <ApplicationDialog
        v-model:open="isDialogOpen"
        :application="selectedApplication"
        :loading="loading"
        @save="handleSaveApplication"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { Plus } from 'lucide-vue-next'
import { ref, onMounted } from 'vue'
import { Button } from '~/components/ui/button'
import { Input } from '~/components/ui/input'
import ApplicationsList from '~/components/applications/ApplicationsList.vue'
import ApplicationDialog from '~/components/applications/ApplicationDialog.vue'
import { SidebarTrigger } from '~/components/ui/sidebar'
import { useMediaQuery } from '@vueuse/core'
import type { Candidature, CandidatureStatus } from '~/types/candidature'
import { useCandidatures } from '~/composables/useCandidatures'

definePageMeta({
  layout: 'dashboard'
})

// Détection mobile pour afficher conditionnellement le SidebarTrigger
const isMobile = useMediaQuery('(max-width: 768px)')

// Utiliser le composable candidatures
const {
  candidatures,
  loading,
  error,
  stats,
  fetchCandidatures,
  createCandidature,
  updateCandidature,
  deleteCandidature,
  updateCandidatureStatus
} = useCandidatures()

const isDialogOpen = ref(false)
const selectedApplication = ref<Candidature | null>(null)

const openNewApplicationDialog = () => {
  selectedApplication.value = null
  isDialogOpen.value = true
}

const editApplication = (application: Application) => {
  selectedApplication.value = { ...application }
  isDialogOpen.value = true
}

const handleSaveApplication = async (application: Application) => {
  try {
    if (application.id) {
      // Mise à jour d'une candidature existante
      await updateApplication(application.id, application)
    } else {
      // Création d'une nouvelle candidature
      const createData = {
        position: application.position || '',
        company: application.company || '',
        applicationDate: application.applicationDate || application.appliedAt || '',
        status: application.status,
        notes: application.notes || application.coverLetter || '',
        interviewDate: application.interviewDate
      }
      await createApplication(createData)
    }
    
    isDialogOpen.value = false
    selectedApplication.value = null
    
    // Rafraîchir la liste
    await fetchApplications()
    
  } catch (err) {
    // L'erreur est déjà gérée dans le composable
    console.error('Erreur lors de la sauvegarde:', err)
  }
}

const handleDeleteApplication = async (application: Application) => {
  if (!application.id) return
  
  if (confirm(`Êtes-vous sûr de vouloir supprimer la candidature "${application.position}" chez ${application.company} ?`)) {
    try {
      await deleteApplication(application.id)
    } catch (err) {
      console.error('Erreur lors de la suppression:', err)
    }
  }
}

const handleUpdateStatus = async (application: Application, status: ApplicationStatus) => {
  if (!application.id) return
  
  try {
    await updateApplicationStatus(application.id, status)
  } catch (err) {
    console.error('Erreur lors de la mise à jour du statut:', err)
  }
}

onMounted(() => {
  fetchApplications()
})
</script>
