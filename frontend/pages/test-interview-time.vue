<template>
  <div class="container mx-auto py-8">
    <h1 class="text-3xl font-bold mb-8">Test Modification Heure Entretien</h1>
    
    <!-- Section de test -->
    <div class="bg-white border rounded-lg p-6 mb-6">
      <h2 class="text-xl font-bold mb-4">Test création candidature avec heure</h2>
      
      <div class="space-y-4 mb-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Poste</label>
            <input 
              v-model="testForm.titrePoste" 
              class="border rounded px-3 py-2 w-full"
              placeholder="Développeur Test"
            />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Entreprise</label>
            <input 
              v-model="testForm.entreprise" 
              class="border rounded px-3 py-2 w-full"
              placeholder="Entreprise Test"
            />
          </div>
        </div>
        
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Date d'entretien</label>
            <input 
              v-model="testForm.dateEntretien" 
              type="date"
              class="border rounded px-3 py-2 w-full"
            />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Heure d'entretien</label>
            <input 
              v-model="testForm.heureEntretien" 
              type="time"
              class="border rounded px-3 py-2 w-full"
              :disabled="!testForm.dateEntretien"
            />
          </div>
        </div>
      </div>
      
      <div class="flex gap-4 mb-4">
        <button 
          @click="createTestCandidature" 
          class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
          :disabled="!testForm.titrePoste || !testForm.entreprise"
        >
          Créer candidature de test
        </button>
        
        <button 
          @click="loadData" 
          class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600"
        >
          Actualiser données
        </button>
      </div>
      
      <div v-if="status" class="p-3 rounded bg-gray-50 border">
        <pre class="text-sm">{{ status }}</pre>
      </div>
    </div>

    <!-- Notifications d'entretien avec modification d'heure -->
    <div class="bg-white border rounded-lg p-6 mb-6">
      <h2 class="text-xl font-bold mb-4">Notifications d'entretien ({{ interviewNotifications.length }})</h2>
      
      <div v-if="interviewNotifications.length === 0" class="text-gray-500">
        Aucune notification d'entretien trouvée
      </div>
      
      <div v-else class="space-y-4">
        <div 
          v-for="notification in interviewNotifications" 
          :key="notification.id"
          class="border p-4 rounded"
          :class="notification.isRead ? 'opacity-60' : 'bg-blue-50'"
        >
          <div class="flex justify-between items-start">
            <div class="flex-1">
              <h3 class="font-medium flex items-center gap-2">
                <Calendar class="h-4 w-4 text-green-600" />
                {{ notification.title }}
              </h3>
              <p class="text-sm text-gray-600 mt-1">{{ notification.message }}</p>
              
              <!-- Informations de candidature -->
              <div v-if="notification.candidature" class="mt-2 p-2 bg-gray-100 rounded text-sm">
                <div class="flex items-center gap-2">
                  <Briefcase class="h-4 w-4 text-blue-600" />
                  <strong>{{ notification.candidature.titrePoste }}</strong>
                  <span>chez {{ notification.candidature.entreprise }}</span>
                </div>
              </div>
              
              <!-- Date et heure d'entretien -->
              <div class="mt-2 space-y-2">
                <div v-if="notification.scheduledFor" class="text-sm text-green-600">
                  📅 {{ formatDate(notification.scheduledFor) }}
                </div>
                
                <!-- Modification de l'heure -->
                <div class="flex items-center gap-3">
                  <Clock class="h-4 w-4 text-blue-600" />
                  <span v-if="notification.interviewTime" class="text-sm">
                    Heure: <strong>{{ notification.interviewTime }}</strong>
                  </span>
                  <span v-else class="text-sm text-gray-500">
                    Heure non définie
                  </span>
                  
                  <button 
                    @click="editTime(notification)" 
                    class="text-xs bg-gray-200 hover:bg-gray-300 px-2 py-1 rounded"
                  >
                    {{ notification.interviewTime ? 'Modifier' : 'Ajouter' }} heure
                  </button>
                </div>
                
                <!-- Formulaire de modification d'heure -->
                <div v-if="editingTimeFor === notification.id" class="mt-2 p-3 bg-yellow-50 border rounded">
                  <div class="flex items-center gap-2">
                    <input 
                      v-model="newTime" 
                      type="time"
                      class="border rounded px-2 py-1 text-sm"
                      placeholder="14:30"
                    />
                    <button 
                      @click="saveTime(notification)" 
                      class="bg-green-500 text-white px-3 py-1 text-xs rounded hover:bg-green-600"
                      :disabled="!newTime"
                    >
                      Sauvegarder
                    </button>
                    <button 
                      @click="cancelEdit()" 
                      class="bg-gray-500 text-white px-3 py-1 text-xs rounded hover:bg-gray-600"
                    >
                      Annuler
                    </button>
                  </div>
                  <p class="text-xs text-gray-600 mt-1">
                    Cette modification mettra aussi à jour la candidature.
                  </p>
                </div>
              </div>
              
              <div class="text-xs text-gray-500 mt-2">
                Créé: {{ formatDate(notification.createdAt) }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Liste des candidatures récentes -->
    <div class="bg-white border rounded-lg p-6">
      <h2 class="text-xl font-bold mb-4">Candidatures récentes ({{ candidatures.length }})</h2>
      
      <div v-if="candidatures.length === 0" class="text-gray-500">
        Aucune candidature
      </div>
      
      <div v-else class="space-y-3">
        <div 
          v-for="candidature in candidatures.slice(0, 5)" 
          :key="candidature.id"
          class="border p-3 rounded"
        >
          <h3 class="font-medium">{{ candidature.titrePoste }}</h3>
          <p class="text-sm text-gray-600">{{ candidature.entreprise }}</p>
          <div class="text-xs text-gray-500 mt-1">
            <span>Entretien: {{ candidature.dateEntretien || 'Non programmé' }}</span>
            <span v-if="candidature.heureEntretien"> à {{ candidature.heureEntretien }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Calendar, Clock, Briefcase } from 'lucide-vue-next'
import { useCandidatures } from '~/composables/useCandidatures'
import { useNotifications } from '~/composables/useNotifications'

const { candidatures, fetchCandidatures, createCandidature } = useCandidatures()
const { notifications, fetchNotifications, updateNotificationTime } = useNotifications()

const status = ref('')
const editingTimeFor = ref<number | null>(null)
const newTime = ref('')

const testForm = ref({
  titrePoste: 'Développeur avec heure',
  entreprise: 'Entreprise Test',
  dateEntretien: '',
  heureEntretien: '14:30'
})

// Notifications d'entretien uniquement
const interviewNotifications = computed(() => 
  notifications.value.filter(n => n.type === 'interview' || n.type === 'reminder')
)

const formatDate = (dateString: string) => {
  return new Intl.DateTimeFormat('fr-FR', {
    dateStyle: 'full',
    timeStyle: 'short'
  }).format(new Date(dateString))
}

const loadData = async () => {
  try {
    status.value = 'Chargement des données...'
    await Promise.all([
      fetchCandidatures(),
      fetchNotifications()
    ])
    status.value = `✅ Données chargées: ${candidatures.value.length} candidatures, ${notifications.value.length} notifications`
  } catch (err: any) {
    status.value = `❌ Erreur: ${err.message}`
  }
}

const createTestCandidature = async () => {
  try {
    status.value = 'Création candidature avec heure...'
    
    // Date pour demain
    const tomorrow = new Date()
    tomorrow.setDate(tomorrow.getDate() + 1)
    const dateEntretien = tomorrow.toISOString().split('T')[0]
    
    await createCandidature({
      titrePoste: testForm.value.titrePoste,
      entreprise: testForm.value.entreprise,
      statut: 'entretien',
      dateDepot: new Date().toISOString().split('T')[0],
      dateEntretien: testForm.value.dateEntretien || dateEntretien,
      heureEntretien: testForm.value.heureEntretien,
      notes: 'Candidature créée pour tester les heures d\'entretien'
    })
    
    status.value = '✅ Candidature créée avec heure - notification générée'
    
    // Recharger après un délai
    setTimeout(loadData, 1000)
    
  } catch (err: any) {
    status.value = `❌ Erreur: ${err.message}`
  }
}

const editTime = (notification: any) => {
  editingTimeFor.value = notification.id
  newTime.value = notification.interviewTime || '14:30'
}

const saveTime = async (notification: any) => {
  try {
    status.value = `Modification heure pour notification ${notification.id}...`
    
    await updateNotificationTime(notification.id, newTime.value)
    
    status.value = `✅ Heure modifiée avec succès`
    editingTimeFor.value = null
    
    // Recharger les données
    setTimeout(loadData, 500)
    
  } catch (err: any) {
    status.value = `❌ Erreur: ${err.message}`
  }
}

const cancelEdit = () => {
  editingTimeFor.value = null
  newTime.value = ''
}

onMounted(() => {
  // Date par défaut pour demain
  const tomorrow = new Date()
  tomorrow.setDate(tomorrow.getDate() + 1)
  testForm.value.dateEntretien = tomorrow.toISOString().split('T')[0]
  
  loadData()
})
</script>
