<template>
  <div>
          <header class="flex h-16 shrink-0 items-center gap-2 border-b transition-[width,height] ease-linear">
        <div class="flex items-center gap-2 px-4">
          <SidebarTrigger v-if="isMobile" class="-ml-1" />
          <h1 class="text-2xl font-bold">Notifications</h1>
        </div>
      </header>

    <div class="container py-6 px-4">
      <div class="flex justify-between items-center mb-6">
        <p class="text-muted-foreground">Gérez vos notifications</p>
        <Button variant="outline" @click="markAllAsRead" :disabled="!hasUnreadNotifications">
          <CheckCheck class="mr-2 h-4 w-4" />
          Tout marquer comme lu
        </Button>
      </div>

      <NotificationsList :notifications="notifications" @update="handleUpdate" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { CheckCheck } from 'lucide-vue-next'
import { Button } from '~/components/ui/button'
import NotificationsList from '~/components/notifications/NotificationsList.vue'
import { SidebarTrigger } from '~/components/ui/sidebar'
import { useMediaQuery } from '@vueuse/core'

definePageMeta({
  layout: 'dashboard'
})

// Détection mobile pour afficher conditionnellement le SidebarTrigger
const isMobile = useMediaQuery('(max-width: 768px)')

interface Notification {
  id: number
  title: string
  message: string
  type: 'reminder' | 'interview' | 'info'
  date: string
  read: boolean
}

const notifications = ref<Notification[]>([
  {
    id: 1,
    title: "Rappel de relance",
    message: "N'oubliez pas de relancer Tech Corp concernant votre candidature",
    type: "reminder",
    date: "2025-05-28",
    read: false
  },
  {
    id: 2,
    title: "Entretien à venir",
    message: "Entretien technique prévu demain avec Web Agency",
    type: "interview",
    date: "2025-05-28",
    read: false
  },
  {
    id: 3,
    title: "Candidature vue",
    message: "Votre candidature chez Digital Solutions a été consultée",
    type: "info",
    date: "2025-05-26",
    read: true
  }
])

const hasUnreadNotifications = computed(() => 
  notifications.value.some(notification => !notification.read)
)

const markAllAsRead = () => {
  notifications.value = notifications.value.map(notification => ({
    ...notification,
    read: true
  }))
}

const handleUpdate = (updatedNotification: Notification) => {
  notifications.value = notifications.value.map(notification =>
    notification.id === updatedNotification.id ? updatedNotification : notification
  )
}

onMounted(() => {
  // TODO: Charger les notifications depuis l'API
})
</script>
