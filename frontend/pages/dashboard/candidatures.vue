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
        <Button @click="fetchCandidatures" variant="outline" size="sm" class="mt-2">
          Réessayer
        </Button>
      </div>

      <!-- Statistiques rapides -->
      <!-- Statistiques principales -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
        <div class="bg-white border rounded-lg p-4 text-center">
          <div class="text-2xl font-bold text-blue-600">{{ stats.total }}</div>
          <div class="text-sm text-muted-foreground">Total</div>
        </div>
        <div class="bg-white border rounded-lg p-4 text-center">
          <div class="text-2xl font-bold text-amber-600">{{ stats.enAttente }}</div>
          <div class="text-sm text-muted-foreground">En attente</div>
        </div>
        <div class="bg-white border rounded-lg p-4 text-center">
          <div class="text-2xl font-bold text-purple-600">{{ stats.entretien }}</div>
          <div class="text-sm text-muted-foreground">Entretiens</div>
        </div>
        <div class="bg-white border rounded-lg p-4 text-center">
          <div class="text-2xl font-bold text-green-600">{{ stats.accepte }}</div>
          <div class="text-sm text-muted-foreground">Acceptées</div>
        </div>
      </div>

      <!-- Statistiques secondaires -->
      <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white border rounded-lg p-4 text-center">
          <div class="text-2xl font-bold text-gray-600">{{ stats.aFaire }}</div>
          <div class="text-sm text-muted-foreground">À faire</div>
        </div>
        <div class="bg-white border rounded-lg p-4 text-center">
          <div class="text-2xl font-bold text-orange-600">{{ stats.relance }}</div>
          <div class="text-sm text-muted-foreground">Relancées</div>
        </div>
        <div class="bg-white border rounded-lg p-4 text-center">
          <div class="text-2xl font-bold text-red-600">{{ stats.refuse }}</div>
          <div class="text-sm text-muted-foreground">Refusées</div>
        </div>
      </div>

      <div class="flex justify-between items-center mb-8">
        <p class="text-muted-foreground">Gérez et suivez vos candidatures</p>
        <Button @click="openNewCandidatureDialog" :disabled="loading">
          <Plus class="mr-2 h-4 w-4" />
          Nouvelle candidature
        </Button>
      </div>

      <CandidaturesList 
        :candidatures="candidatures" 
        :loading="loading"
        @edit="editCandidature"
        @delete="handleDeleteCandidature"
        @update-status="handleUpdateStatus"
        @schedule-interview="handleScheduleInterview"
        @refresh="fetchCandidatures" 
      />
      
      <CandidatureDialog
        v-model:open="isDialogOpen"
        :candidature="selectedCandidature"
        :loading="loading"
        @save="handleSaveCandidature"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { Plus } from 'lucide-vue-next'
import { ref, onMounted } from 'vue'
import { Button } from '~/components/ui/button'
import { Input } from '~/components/ui/input'
import CandidaturesList from '~/components/candidatures/CandidaturesList.vue'
import CandidatureDialog from '~/components/candidatures/CandidatureDialog.vue'
import { SidebarTrigger } from '~/components/ui/sidebar'
import { useMediaQuery } from '@vueuse/core'
import type { Candidature, CandidatureStatus } from '~/types/candidature'
import { useCandidatures } from '~/composables/useCandidatures'

definePageMeta({
  layout: 'dashboard',
  middleware: ['auth']
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
const selectedCandidature = ref<Candidature | null>(null)

const openNewCandidatureDialog = () => {
  selectedCandidature.value = null
  isDialogOpen.value = true
}

const editCandidature = (candidature: Candidature) => {
  selectedCandidature.value = { ...candidature }
  isDialogOpen.value = true
}

const handleSaveCandidature = async (candidature: Candidature) => {
  try {
    if (candidature.id) {
      // Mise à jour d'une candidature existante
      await updateCandidature(candidature.id, candidature)
    } else {
      // Création d'une nouvelle candidature
      const createData = {
        titrePoste: candidature.titrePoste || '',
        entreprise: candidature.entreprise || '',
        dateDepot: candidature.dateDepot || '',
        statut: candidature.statut,
        notes: candidature.notes || '',
        dateEntretien: candidature.dateEntretien,
        heureEntretien: candidature.heureEntretien || ''
      }
      await createCandidature(createData)
    }
    
    isDialogOpen.value = false
    selectedCandidature.value = null
    
    // Rafraîchir la liste
    await fetchCandidatures()
    
  } catch (err) {
    // L'erreur est déjà gérée dans le composable
    console.error('Erreur lors de la sauvegarde:', err)
  }
}

const handleDeleteCandidature = async (candidature: Candidature) => {
  if (!candidature.id) return
  
  if (confirm(`Êtes-vous sûr de vouloir supprimer la candidature "${candidature.titrePoste}" chez ${candidature.entreprise} ?`)) {
    try {
      await deleteCandidature(candidature.id)
    } catch (err) {
      console.error('Erreur lors de la suppression:', err)
    }
  }
}

const handleUpdateStatus = async (candidature: Candidature, status: CandidatureStatus) => {
  if (!candidature.id) return
  
  try {
    await updateCandidatureStatus(candidature.id, status)
    
    // Message de succès selon le statut
    const statusMessages = {
      'relance': 'Candidature marquée comme relancée !',
      'entretien': 'Candidature marquée comme en entretien !',
      'accepte': 'Candidature marquée comme acceptée !',
      'refuse': 'Candidature marquée comme refusée !',
      'en_attente': 'Candidature marquée comme en attente !',
      'a_faire': 'Candidature marquée comme à faire !'
    }
    
    // Afficher un message de succès (vous pouvez remplacer par une vraie notification toast)
    console.log(statusMessages[status] || 'Statut mis à jour avec succès !')
    
  } catch (err) {
    console.error('Erreur lors de la mise à jour du statut:', err)
    // Afficher un message d'erreur (vous pouvez remplacer par une vraie notification toast)
    console.error('Erreur lors de la mise à jour du statut')
  }
}

const handleScheduleInterview = async (candidature: Candidature, dateEntretien: string, notes: string) => {
  if (!candidature.id) return
  
  try {
    // Mettre à jour la candidature avec la date d'entretien et les notes
    await updateCandidature(candidature.id, {
      dateEntretien,
      notes,
      statut: 'entretien' // Changer automatiquement le statut vers "entretien"
    })
    
    console.log('Entretien planifié avec succès !')
    
  } catch (err) {
    console.error('Erreur lors de la planification de l\'entretien:', err)
    console.error('Erreur lors de la planification de l\'entretien')
  }
}

onMounted(() => {
  fetchCandidatures()
})
</script>
