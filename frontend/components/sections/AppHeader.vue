<template>
  <header
    class="w-full px-6 py-4 bg-white/90 shadow-sm sticky backdrop-blur-md top-0 z-50"
  >
    <div class="grid grid-cols-3 items-center">
      <div class="flex justify-start">
        <NuxtLink to="/" class="flex items-center gap-2">
          <img src="/public/logo.png" alt="Tandem" class="h-14" />
        </NuxtLink>
      </div>

      <nav class="hidden md:flex items-center justify-center gap-8">
        <NuxtLink
          to="/about"
          class="text-gray-700 hover:text-blue-600 transition-colors font-medium"
          @click="trackNavigation('About', 'Header')"
        >
          À propos
        </NuxtLink>
        <NuxtLink
          to="/blog"
          class="text-gray-700 hover:text-blue-600 transition-colors font-medium"
          @click="trackNavigation('Blog', 'Header')"
        >
          Blog
        </NuxtLink>
        <NuxtLink
          to="/contact"
          class="text-gray-700 hover:text-blue-600 transition-colors font-medium"
          @click="trackNavigation('Contact', 'Header')"
        >
          Contact
        </NuxtLink>
      </nav>

      <div class="flex items-center justify-end gap-4">
        <template v-if="isAuthenticated">
          <span class="text-sm">Bonjour, {{ userFullName }}</span>
          <NuxtLink to="/dashboard" @click="trackNavigation('Dashboard', 'Header')">
            <Button variant="default">Dashboard</Button>
          </NuxtLink>
        </template>
        <template v-else>
          <NuxtLink to="/login" @click="trackButtonClick('Login', 'Header')">
            <Button variant="outline">Connexion</Button>
          </NuxtLink>
          <NuxtLink to="/register" @click="trackButtonClick('Register', 'Header')">
            <Button
              class="bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700"
              >Inscription</Button
            >
          </NuxtLink>
        </template>
      </div>
    </div>
  </header>
</template>

<script setup>
import { Button } from "@/components/ui/button";
import { useAuth } from "@/composables/useAuth";
import { useMatomo } from "@/composables/useMatomo";

const { isAuthenticated, userFullName } = useAuth();
const { trackButtonClick, trackNavigation } = useMatomo();
</script>
