<template>
  <Teleport to="body">
    <div class="fixed top-0 right-0 z-50 w-full max-w-sm p-4 space-y-4 pointer-events-none">
      <TransitionGroup
        name="toast"
        tag="div"
        class="space-y-2"
      >
        <div
          v-for="toast in toasts"
          :key="toast.id"
          :class="[
            'relative w-full overflow-hidden rounded-md border p-4 shadow-lg transition-all pointer-events-auto',
            {
              'bg-background text-foreground': toast.variant === 'default',
              'bg-destructive text-destructive-foreground': toast.variant === 'destructive',
              'bg-green-50 text-green-900 border-green-200': toast.variant === 'success'
            }
          ]"
        >
          <div class="flex items-start space-x-3">
            <div class="flex-1">
              <div v-if="toast.title" class="font-medium">
                {{ toast.title }}
              </div>
              <div v-if="toast.description" class="text-sm opacity-90">
                {{ toast.description }}
              </div>
            </div>
            <button
              @click="removeToast(toast.id)"
              class="ml-auto rounded-md p-1 hover:bg-black/10 focus:outline-none focus:ring-2"
            >
              <X class="h-4 w-4" />
            </button>
          </div>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { X } from 'lucide-vue-next'
import { useToast } from './use-toast'

const { toasts, removeToast } = useToast()
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(100%);
}
</style>
