<template>
    <section class="text-center py-24 px-4 bg-white relative overflow-hidden">
      <img src="/public/alternance.jpg" alt="Tandem" class="absolute inset-0 w-full h-full object-cover opacity-10" />
      <div class="relative z-10">
        <h1 class="text-4xl md:text-6xl font-bold mb-4" data-aos="fade-down">Trouvez votre alternance facilement</h1>
        <p class="text-lg text-gray-600 mb-6" data-aos="fade-up">Avec Tandem, organisez vos candidatures sans stress.</p>
        <div class="flex justify-center gap-4" data-aos="zoom-in">
          <div v-if="isAuthenticated">
            <NuxtLink to="/dashboard" @click="trackCTA('Access Dashboard', 'Hero')">
              <Button class="bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700">Accéder au dashboard</Button>
            </NuxtLink>
          </div>
          <div v-else class="flex gap-4">
            <NuxtLink to="/register" @click="trackCTA('Start Free', 'Hero')">
              <Button class="bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700">Commencer gratuitement</Button>
            </NuxtLink>
            <NuxtLink to="/login" @click="trackCTA('Login', 'Hero')">
              <Button variant="outline">Se connecter</Button>
            </NuxtLink>
          </div>
        </div>
      </div>
    </section>
  </template>
  
  <script setup>
  import { Button } from '@/components/ui/button'
  import { useAuth } from '@/composables/useAuth'
  import { useMatomo } from '@/composables/useMatomo'
  
  const { isAuthenticated } = useAuth()
  const { trackCTA } = useMatomo()
  </script>
