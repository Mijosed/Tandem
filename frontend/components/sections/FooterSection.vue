<template>
    <footer class=" text-black text-center py-6">
      <p>&copy; 2025 Tandem. Tous droits réservés.</p>
      <div class="mt-2 space-x-4 text-sm">
        <a href="#" class="underline" @click="trackNavigation('Legal Terms', 'Footer')">Mentions légales</a>
        <a href="#" class="underline" @click="trackNavigation('Privacy Policy', 'Footer')">Confidentialité</a>
      </div>
    </footer>
  </template>
  
  <script setup>
  import { useMatomo } from '@/composables/useMatomo'

  const { trackNavigation } = useMatomo()
  </script>