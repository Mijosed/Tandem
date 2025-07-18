<template>
    <header class="w-full px-6 py-4 bg-white/90 shadow-sm sticky backdrop-blur-md top-0 z-50">
      <div class="grid grid-cols-3 items-center">
        <div class="flex justify-start">
          <NuxtLink to="/" class="flex items-center gap-2">
              <img src="/public/logo.png" alt="Tandem" class="h-14" />
          </NuxtLink>
        </div>
        
        <nav class="hidden md:flex items-center justify-center gap-8">
          <NuxtLink to="/about" class="text-gray-700 hover:text-blue-600 transition-colors font-medium">
            À propos
          </NuxtLink>
          <NuxtLink to="/blog" class="text-gray-700 hover:text-blue-600 transition-colors font-medium">
            Blog
          </NuxtLink>
          <NuxtLink to="/contact" class="text-gray-700 hover:text-blue-600 transition-colors font-medium">
            Contact
          </NuxtLink>
        </nav>
        
        <div class="flex items-center justify-end gap-4">
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
              <Button class="bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700">Inscription</Button>
            </NuxtLink>
          </template>
        </div>
      </div>
    </header>
  </template>
  
  <script setup>
  import { Button } from '@/components/ui/button'
  
  const user = null;
</script>