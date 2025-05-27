<template>
    <header class="w-full px-6 py-4 flex justify-between items-center bg-white/90 shadow-sm sticky backdrop-blur-md top-0 z-50">
        <NuxtLink to="/" class="flex items-center gap-2">
            <img src="/public/logo.png" alt="Tandem" class="h-14" />
        </NuxtLink>
      <div class="flex items-center gap-4">
        <template v-if="user">
          <span class="text-sm">Bonjour, {{ user.name }}</span>
          <NuxtLink to="/dashboard">
            <Button variant="default">Dashboard</Button>
          </NuxtLink>
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
  
  <script setup>
  import { Button } from '@/components/ui/button'
  
  const user = null;
</script>