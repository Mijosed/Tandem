<template>
  <div>
          <header class="flex h-16 shrink-0 items-center gap-2 border-b transition-[width,height] ease-linear">
        <div class="flex items-center gap-2 px-4">
          <SidebarTrigger v-if="isMobile" class="-ml-1" />
          <h1 class="text-2xl font-bold">Gestion des utilisateurs</h1>
        </div>
      </header>

    <div class="container py-6 px-4">
      <div class="flex justify-between items-center mb-8">
        <p class="text-muted-foreground">Gérez les utilisateurs de la plateforme</p>
      </div>

      <div v-if="error" class="mb-6 p-4 bg-destructive/15 border border-destructive/50 rounded-lg">
        <p class="text-destructive font-medium">{{ error }}</p>
      </div>

      <div v-if="loading && users.length === 0" class="flex justify-center items-center py-12">
        <div class="flex items-center gap-2">
          <LoaderCircle class="h-6 w-6 animate-spin" />
          <span>Chargement des utilisateurs...</span>
        </div>
      </div>

      <UsersList v-else :users="users" @refresh="fetchUsers" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { LoaderCircle } from 'lucide-vue-next'
import { SidebarTrigger } from '~/components/ui/sidebar'
import { useMediaQuery } from '@vueuse/core'
import { useUsers } from '~/composables/useUsers'
import UsersList from '~/components/admin/UsersList.vue'

definePageMeta({
  layout: 'dashboard'
})

const isMobile = useMediaQuery('(max-width: 768px)')

const { users, loading, error, fetchUsers } = useUsers()

onMounted(() => {
  fetchUsers()
})
</script>
