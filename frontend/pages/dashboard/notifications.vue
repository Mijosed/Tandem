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
        <div>
          <p class="text-muted-foreground">Gérez vos notifications</p>
          <p v-if="!loading && !error" class="text-sm text-muted-foreground mt-1">
            {{ stats.total }} notification(s) - {{ unreadCount }} non lue(s)
          </p>
        </div>
        <Button 
          variant="outline" 
          @click="markAllAsRead" 
          :disabled="!hasUnreadNotifications || loading"
        >
          <CheckCheck class="mr-2 h-4 w-4" />
          Tout marquer comme lu
        </Button>
      </div>

      <div v-if="loading" class="flex items-center justify-center py-8">
        <LoaderIcon class="h-6 w-6 animate-spin mr-2" />
        <span class="text-muted-foreground">Chargement des notifications...</span>
      </div>

      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-md p-4 mb-4">
        <div class="flex items-center">
          <AlertCircle class="h-5 w-5 text-red-500 mr-2" />
          <p class="text-red-800">{{ error }}</p>
        </div>
        <Button 
          variant="outline" 
          size="sm" 
          @click="fetchNotifications" 
          class="mt-2"
        >
          Réessayer
        </Button>
      </div>

      <!-- Liste des notifications -->
      <div v-else-if="notifications.length === 0" class="text-center py-8">
        <BellIcon class="h-12 w-12 text-muted-foreground mx-auto mb-4" />
        <p class="text-muted-foreground">Aucune notification pour le moment</p>
      </div>

      <NotificationsList 
        v-else
        :notifications="notifications" 
        @update="handleUpdate" 
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { CheckCheck, LoaderIcon, AlertCircle, BellIcon } from 'lucide-vue-next'
import { Button } from '~/components/ui/button'
import NotificationsList from '~/components/notifications/NotificationsList.vue'
import { SidebarTrigger } from '~/components/ui/sidebar'
import { useMediaQuery } from '@vueuse/core'
import { useNotifications } from '~/composables/useNotifications'
import type { Notification } from '~/types/notification'

definePageMeta({
  layout: 'dashboard'
})

const isMobile = useMediaQuery('(max-width: 768px)')

const {
  notifications,
  loading,
  error,
  hasUnreadNotifications,
  unreadCount,
  stats,
  fetchNotifications,
  markAllAsRead,
  markAsRead
} = useNotifications()

const handleUpdate = (updatedNotification: Notification) => {
  if (!updatedNotification.isRead) {
    markAsRead(updatedNotification.id)
  }
}

onMounted(async () => {
  await fetchNotifications()
})
</script>
