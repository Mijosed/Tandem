<template>
  <div class="container mx-auto py-8">
    <h1 class="text-3xl font-bold mb-8">🔔 Debug Notifications</h1>
    
    <!-- Info utilisateur connecté -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
      <h2 class="text-lg font-bold text-blue-800">👤 Utilisateur connecté</h2>
      <p v-if="currentUser">ID: {{ currentUser.id }} - Email: {{ currentUser.email }}</p>
      <p v-else class="text-red-600">Aucun utilisateur connecté</p>
    </div>

    <!-- Actions de test -->
    <div class="bg-white border rounded-lg p-6 mb-6">
      <h2 class="text-xl font-bold mb-4">🧪 Actions de test</h2>
      
      <div class="flex gap-4 mb-4">
        <button 
          @click="testFetchNotifications" 
          class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
          :disabled="loading"
        >
          {{ loading ? 'Chargement...' : 'Charger notifications' }}
        </button>
        
        <button 
          @click="testCreateNotification" 
          class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600"
          :disabled="loading"
        >
          Créer notification test
        </button>
      </div>
      
      <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 p-3 rounded mb-4">
        ❌ {{ error }}
      </div>
      
      <div v-if="testResult" class="bg-blue-50 border border-blue-200 p-3 rounded mb-4">
        <h3 class="font-medium mb-2">Résultat du test :</h3>
        <pre class="text-xs overflow-auto">{{ JSON.stringify(testResult, null, 2) }}</pre>
      </div>
    </div>

    <!-- Notifications trouvées -->
    <div v-if="notifications.length > 0" class="bg-white border rounded-lg p-6">
      <h2 class="text-xl font-bold mb-4">📬 Notifications ({{ notifications.length }})</h2>
      
      <div class="space-y-3">
        <div 
          v-for="notification in notifications" 
          :key="notification.id"
          class="border p-3 rounded bg-gray-50"
        >
          <div class="flex justify-between items-start">
            <div>
              <h3 class="font-medium">{{ notification.title }}</h3>
              <p class="text-sm text-gray-600">{{ notification.message }}</p>
              <p class="text-xs text-gray-500 mt-1">
                Type: {{ notification.type }} | 
                Lu: {{ notification.isRead ? 'Oui' : 'Non' }} |
                User ID: {{ notification.user?.id }}
              </p>
            </div>
            <span class="text-xs bg-blue-100 px-2 py-1 rounded">
              ID: {{ notification.id }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <div v-else-if="!loading" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
      <p class="text-yellow-800">Aucune notification trouvée pour cet utilisateur.</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useAuth } from '~/composables/useAuth'
import { useNotifications } from '~/composables/useNotifications'

definePageMeta({
  layout: 'dashboard'
})

const { currentUser } = useAuth()
const { 
  notifications, 
  loading, 
  error, 
  fetchNotifications,
  createNotification 
} = useNotifications()

const testResult = ref<any>(null)

const testFetchNotifications = async () => {
  try {
    console.log('🔧 DEBUG: Début fetch notifications pour user:', currentUser.value?.id)
    await fetchNotifications()
    
    testResult.value = {
      success: true,
      message: `${notifications.value.length} notifications chargées`,
      userId: currentUser.value?.id,
      notifications: notifications.value.map(n => ({
        id: n.id,
        title: n.title,
        type: n.type,
        isRead: n.isRead,
        userId: n.user?.id
      }))
    }
    
    console.log('🔧 DEBUG: Résultat fetch notifications:', testResult.value)
  } catch (err: any) {
    console.error('🔧 DEBUG: Erreur fetch notifications:', err)
    testResult.value = {
      success: false,
      error: err.message
    }
  }
}

const testCreateNotification = async () => {
  try {
    console.log('🔧 DEBUG: Création notification test pour user:', currentUser.value?.id)
    
    await createNotification({
      title: `Test Debug ${Date.now()}`,
      message: 'Notification de test créée depuis la page debug',
      type: 'info'
    })
    
    // Recharger les notifications
    await fetchNotifications()
    
    testResult.value = {
      success: true,
      message: 'Notification créée et liste rechargée',
      userId: currentUser.value?.id
    }
    
    console.log('🔧 DEBUG: Notification créée avec succès')
  } catch (err: any) {
    console.error('🔧 DEBUG: Erreur création notification:', err)
    testResult.value = {
      success: false,
      error: err.message
    }
  }
}

// Charger les notifications au montage
onMounted(() => {
  if (currentUser.value) {
    testFetchNotifications()
  }
})
</script>
