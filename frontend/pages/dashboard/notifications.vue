<template>
  <div>
    <header class="flex h-16 shrink-0 items-center gap-2 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12">
      <div class="flex items-center gap-2 px-4">
        <SidebarTrigger class="-ml-1" />
        <h1 class="text-2xl font-bold">Notifications</h1>
        <Badge v-if="unreadCount > 0" variant="destructive" class="ml-2">
          {{ unreadCount }} non lu{{ unreadCount > 1 ? 's' : '' }}
        </Badge>
        <Badge v-if="isConnected" variant="secondary" class="ml-2">
          <div class="flex items-center gap-1">
            <div class="w-2 h-2 bg-green-500 rounded-full"></div>
            Connecté
          </div>
        </Badge>
      </div>
    </header>

    <div class="container py-6 px-4">
      <div class="flex justify-between items-center mb-6">
        <p class="text-muted-foreground">Gérez vos notifications</p>
        <div class="flex gap-2">
          <Button variant="outline" @click="createTestNotification" :disabled="!isConnected">
            <Bell class="mr-2 h-4 w-4" />
            Test notification
          </Button>
          <Button variant="outline" @click="markAllAsRead" :disabled="!hasUnreadNotifications">
            <CheckCheck class="mr-2 h-4 w-4" />
            Tout marquer comme lu
          </Button>
        </div>
      </div>

      <NotificationsList 
        :notifications="notifications" 
        @mark-as-read="markAsRead"
        @create="createNotification" 
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { CheckCheck, Bell } from 'lucide-vue-next'
import { Button } from '~/components/ui/button'
import { Badge } from '~/components/ui/badge'
import NotificationsList from '~/components/notifications/NotificationsList.vue'
import { SidebarTrigger } from '~/components/ui/sidebar'
import { useMercure } from '~/composables/useMercure'

definePageMeta({
  layout: 'dashboard'
})

const {
  notifications,
  unreadCount,
  isConnected,
  markAsRead,
  markAllAsRead,
  createTestNotification
} = useMercure()

const hasUnreadNotifications = computed(() => unreadCount.value > 0)

const createNotification = async (data: {
  title: string
  message: string
  type: 'reminder' | 'interview' | 'info'
}) => {
  await createTestNotification(data)
}
</script>
