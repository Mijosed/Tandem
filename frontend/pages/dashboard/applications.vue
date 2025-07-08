<template>
  <div>
          <header class="flex h-16 shrink-0 items-center gap-2 border-b transition-[width,height] ease-linear">
        <div class="flex items-center gap-2 px-4">
          <SidebarTrigger v-if="isMobile" class="-ml-1" />
          <h1 class="text-2xl font-bold">Mes candidatures</h1>
        </div>
      </header>

    <div class="container py-6 px-4">
      <div class="flex justify-between items-center mb-8">
        <p class="text-muted-foreground">Gérez et suivez vos candidatures</p>
        <Button @click="openNewApplicationDialog">
          <Plus class="mr-2 h-4 w-4" />
          Nouvelle candidature
        </Button>
      </div>

      <ApplicationsList :applications="applications" @refresh="loadApplications" />
      <ApplicationDialog
        v-model:open="isDialogOpen"
        :application="selectedApplication"
        @save="handleSaveApplication"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { Plus } from 'lucide-vue-next'
import { ref, onMounted } from 'vue'
import { Button } from '~/components/ui/button'
import ApplicationsList from '~/components/applications/ApplicationsList.vue'
import ApplicationDialog from '~/components/applications/ApplicationDialog.vue'
import { SidebarTrigger } from '~/components/ui/sidebar'
import { useMediaQuery } from '@vueuse/core'

definePageMeta({
  layout: 'dashboard'
})

// Détection mobile pour afficher conditionnellement le SidebarTrigger
const isMobile = useMediaQuery('(max-width: 768px)')

interface Application {
  id?: number
  position: string
  company: string
  applicationDate: string
  interviewDate?: string
  status: 'pending' | 'followed_up' | 'interview' | 'rejected' | 'accepted'
  notes?: string
}

const applications = ref<Application[]>([])
const isDialogOpen = ref(false)
const selectedApplication = ref<Application | null>(null)

const openNewApplicationDialog = () => {
  selectedApplication.value = null
  isDialogOpen.value = true
}

const loadApplications = async () => {
  // TODO: Remplacer par l'appel API réel
  applications.value = [
    {
      id: 1,
      position: "Développeur Full Stack",
      company: "Tech Corp",
      applicationDate: "2025-05-20",
      status: "pending",
      notes: "Envoyé via LinkedIn"
    },
    {
      id: 2,
      position: "Développeur Frontend",
      company: "Web Agency",
      applicationDate: "2025-05-15",
      interviewDate: "2025-06-01",
      status: "interview",
      notes: "Entretien technique prévu"
    }
  ]
}

const handleSaveApplication = async (application: Application) => {
  // TODO: Remplacer par l'appel API réel
  if (application.id) {
    // Mise à jour
    applications.value = applications.value.map(app => 
      app.id === application.id ? application : app
    )
  } else {
    // Nouvelle candidature
    applications.value.push({
      ...application,
      id: Math.max(...applications.value.map(a => a.id || 0)) + 1
    })
  }
  isDialogOpen.value = false
  await loadApplications()
}

onMounted(() => {
  loadApplications()
})
</script>
