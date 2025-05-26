<script lang="ts">
export const description = 'A sidebar that collapses to icons.'
export const iframeHeight = '800px'
export const containerClass = 'w-full h-full'
</script>

<script setup lang="ts">
import { SidebarTrigger } from '~/components/ui/sidebar'

definePageMeta({
  layout: 'dashboard'
})
</script>

<template>
  <div>
    <header class="flex h-16 shrink-0 items-center gap-2 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12">
      <div class="flex items-center gap-2 px-4">
        <SidebarTrigger class="-ml-1" />
        <h1 class="text-2xl font-bold">Tableau de bord</h1>
      </div>
    </header>
    <div class="container py-6 px-4">
      <!-- Contenu du tableau de bord -->
    </div>
  </div>
</template>
