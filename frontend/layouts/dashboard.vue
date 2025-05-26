<template>
  <SidebarProvider>
    <AppSidebar />
    <SidebarInset>
      <slot />
    </SidebarInset>
  </SidebarProvider>
</template>

<script setup lang="ts">
import AppSidebar from '~/components/dashboard/AppSidebar.vue'
import { SidebarProvider, SidebarInset } from '~/components/ui/sidebar'
</script>
