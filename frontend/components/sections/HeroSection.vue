<template>
    <section class="text-center py-24 px-4 bg-white relative overflow-hidden">
      <img src="/alternance.jpg" alt="Tandem" class="absolute inset-0 w-full h-full object-cover opacity-10" />
      <div class="relative z-10">
        <h1 class="text-4xl md:text-6xl font-bold mb-4" data-aos="fade-down">
          <template v-if="isAuthenticated">
            Bienvenue, {{ user?.firstname }} !
          </template>
          <template v-else>
            Trouvez votre alternance facilement
          </template>
        </h1>
        <p class="text-lg text-gray-600 mb-6" data-aos="fade-up">
          <template v-if="isAuthenticated">
            Commencez à explorer les opportunités d'alternance.
          </template>
          <template v-else>
            Avec Tandem, organisez vos candidatures sans stress.
          </template>
        </p>
        <div class="flex justify-center gap-4" data-aos="zoom-in">
          <template v-if="isAuthenticated">
            <Button @click="navigateTo('/dashboard')" variant="default">Voir mon tableau de bord</Button>
            <Button @click="handleLogout" variant="outline">Se déconnecter</Button>
          </template>
          <template v-else>
            <Button @click="navigateTo('/register')" variant="default">Commencer gratuitement</Button>
            <Button @click="navigateTo('/login')" variant="outline">Se connecter</Button>
          </template>
        </div>
      </div>
    </section>
  </template>
  
  <script setup lang="ts">
  import { Button } from '@/components/ui/button'
  import { useAuthStore } from '@/stores/auth'
  import { navigateTo } from '#app'
  import { storeToRefs } from 'pinia'
  import { onMounted } from 'vue'

  const authStore = useAuthStore()
  const { user, isAuthenticated } = storeToRefs(authStore)

  // Vérifier l'état d'authentification au chargement du composant
  onMounted(() => {
    if (authStore.token && !authStore.user) {
      authStore.fetchUser()
    }
  })

  const handleLogout = () => {
    authStore.logout()
  }
  </script>
