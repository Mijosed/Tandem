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

      <UsersList :users="users" @refresh="loadUsers" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { SidebarTrigger } from '~/components/ui/sidebar'
import { useMediaQuery } from '@vueuse/core'
import UsersList from '~/components/admin/UsersList.vue'

definePageMeta({
  layout: 'dashboard'
})

// Détection mobile pour afficher conditionnellement le SidebarTrigger
const isMobile = useMediaQuery('(max-width: 768px)')

interface User {
  id: number
  firstName: string
  lastName: string
  email: string
  role: string
  isActive: boolean
  createdAt: string
}

const users = ref<User[]>([])

const loadUsers = async () => {
  // TODO: Remplacer par l'appel API réel
  users.value = [
    {
      id: 1,
      firstName: 'Jean',
      lastName: 'Dupont',
      email: 'jean.dupont@example.com',
      role: 'ROLE_ADMIN',
      isActive: true,
      createdAt: '2025-05-20'
    },
    {
      id: 2,
      firstName: 'Marie',
      lastName: 'Martin',
      email: 'marie.martin@example.com',
      role: 'ROLE_USER',
      isActive: true,
      createdAt: '2025-05-15'
    },
    {
      id: 3,
      firstName: 'Pierre',
      lastName: 'Bernard',
      email: 'pierre.bernard@example.com',
      role: 'ROLE_PREMIUM',
      isActive: false,
      createdAt: '2025-05-10'
    }
  ]
}

onMounted(() => {
  loadUsers()
})
</script>
