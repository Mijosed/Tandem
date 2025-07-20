<template>
  <SidebarProvider>
    <AppSidebar />
    <SidebarInset>
      <slot />
    </SidebarInset>
    <Toast />
  </SidebarProvider>
</template>

<script setup lang="ts">
import AppSidebar from '~/components/dashboard/AppSidebar.vue'
import { SidebarProvider, SidebarInset } from '~/components/ui/sidebar'
import { Toast } from '~/components/ui/toast'
</script>
