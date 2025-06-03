<template>
    <header class="w-full px-6 py-4 flex justify-between items-center bg-white/90 shadow-sm sticky backdrop-blur-md top-0 z-50">
        <NuxtLink to="/" class="flex items-center gap-2">
            <img src="/logo.png" alt="Tandem" class="h-14" />
        </NuxtLink>
      <div class="flex items-center gap-4">
        <template v-if="isAuthenticated">
          <span class="text-sm">Bonjour, {{ user?.firstname }}</span>
          <NuxtLink to="/dashboard">
            <Button variant="default">Dashboard</Button>
          </NuxtLink>
          <Button @click="handleLogout" variant="outline">Se déconnecter</Button>
        </template>
        <template v-else>
          <NuxtLink to="/login">
            <Button variant="outline">Connexion</Button>
          </NuxtLink>
          <NuxtLink to="/register">
            <Button>Inscription</Button>
          </NuxtLink>
        </template>
      </div>
    </header>
  </template>
  
  <script setup lang="ts">
  import { Button } from '@/components/ui/button'
  import { useAuthStore } from '@/stores/auth'
  import { storeToRefs } from 'pinia'
  
  const authStore = useAuthStore()
  const { user, isAuthenticated } = storeToRefs(authStore)

  const handleLogout = () => {
    authStore.logout()
  }
  </script>