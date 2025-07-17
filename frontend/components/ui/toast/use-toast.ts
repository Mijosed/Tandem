import { ref } from 'vue'

export interface Toast {
  id: string
  title?: string
  description?: string
  variant?: 'default' | 'destructive' | 'success'
  duration?: number
}

const toasts = ref<Toast[]>([])

export const useToast = () => {
  const addToast = (toast: Omit<Toast, 'id'>) => {
    const id = Date.now().toString()
    const newToast: Toast = {
      id,
      duration: 5000,
      variant: 'default',
      ...toast
    }
    
    toasts.value.push(newToast)
    
    // Auto-remove after duration
    if (newToast.duration && newToast.duration > 0) {
      setTimeout(() => {
        removeToast(id)
      }, newToast.duration)
    }
    
    return id
  }
  
  const removeToast = (id: string) => {
    const index = toasts.value.findIndex(toast => toast.id === id)
    if (index > -1) {
      toasts.value.splice(index, 1)
    }
  }
  
  const toast = (options: Omit<Toast, 'id'>) => {
    return addToast(options)
  }
  
  return {
    toasts: readonly(toasts),
    toast,
    addToast,
    removeToast
  }
}
