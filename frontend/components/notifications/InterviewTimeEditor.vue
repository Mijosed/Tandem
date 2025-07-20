<template>
  <div class="space-y-3">
    <!-- Affichage de l'heure actuelle -->
    <div v-if="notification.interviewTime" class="flex items-center gap-2 text-sm">
      <Clock class="h-4 w-4 text-blue-600" />
      <span class="text-gray-600">Heure prévue :</span>
      <span class="font-medium">{{ formatTime(notification.interviewTime) }}</span>
      <Button
        variant="ghost"
        size="sm"
        @click="toggleEdit"
        class="h-6 w-6 p-0"
      >
        <Edit2 class="h-3 w-3" />
      </Button>
    </div>
    
    <!-- Ajout d'heure si pas définie -->
    <div v-else class="flex items-center gap-2">
      <Clock class="h-4 w-4 text-gray-400" />
      <span class="text-gray-500 text-sm">Heure non définie</span>
      <Button
        variant="outline"
        size="sm"
        @click="toggleEdit"
        class="h-7 text-xs"
      >
        <Plus class="h-3 w-3 mr-1" />
        Ajouter heure
      </Button>
    </div>

    <!-- Formulaire de modification -->
    <div v-if="isEditing" class="p-3 bg-gray-50 rounded-lg border">
      <div class="space-y-3">
        <Label class="text-sm font-medium">Heure d'entretien</Label>
        <div class="flex gap-2">
          <Input
            v-model="editTime"
            type="time"
            class="w-32"
            placeholder="14:30"
          />
          <Button
            size="sm"
            @click="saveTime"
            :disabled="saving"
            class="px-3"
          >
            <Check class="h-3 w-3 mr-1" />
            {{ saving ? 'Sauvegarde...' : 'Sauvegarder' }}
          </Button>
          <Button
            variant="ghost"
            size="sm"
            @click="cancelEdit"
            class="px-3"
          >
            <X class="h-3 w-3 mr-1" />
            Annuler
          </Button>
        </div>
        <p class="text-xs text-gray-500">
          Cette modification mettra également à jour l'heure dans la candidature.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Clock, Edit2, Plus, Check, X } from 'lucide-vue-next'
import { Input } from '~/components/ui/input'
import { Label } from '~/components/ui/label'
import { Button } from '~/components/ui/button'
import type { Notification } from '~/types/notification'
import { useNotifications } from '~/composables/useNotifications'
import { useCandidatures } from '~/composables/useCandidatures'

const props = defineProps<{
  notification: Notification
}>()

const emit = defineEmits<{
  'updated': [notification: Notification]
}>()

const { updateNotificationTime } = useNotifications()
const { updateCandidature } = useCandidatures()

const isEditing = ref(false)
const editTime = ref('')
const saving = ref(false)

const formatTime = (timeString: string) => {
  try {
    // Si c'est déjà au format HH:MM, on le retourne tel quel
    if (timeString.match(/^\d{2}:\d{2}$/)) {
      return timeString
    }
    
    // Sinon, on parse la date/time et on extrait l'heure
    const date = new Date(timeString)
    return date.toLocaleTimeString('fr-FR', { 
      hour: '2-digit', 
      minute: '2-digit',
      hour12: false 
    })
  } catch (err) {
    return timeString
  }
}

const toggleEdit = () => {
  isEditing.value = !isEditing.value
  if (isEditing.value) {
    // Pré-remplir avec l'heure actuelle ou une heure par défaut
    if (props.notification.interviewTime) {
      editTime.value = formatTime(props.notification.interviewTime)
    } else {
      // Heure par défaut : 14:00
      editTime.value = '14:00'
    }
  }
}

const cancelEdit = () => {
  isEditing.value = false
  editTime.value = ''
}

const saveTime = async () => {
  if (!editTime.value) return
  
  saving.value = true
  
  try {
    console.log('Sauvegarde de l\'heure:', editTime.value)
    
    // 1. Mettre à jour la notification avec la nouvelle heure
    await updateNotificationTime(props.notification.id, editTime.value)
    
    // 2. Si la notification est liée à une candidature, mettre à jour la candidature aussi
    if (props.notification.candidature?.id && props.notification.scheduledFor) {
      // Combiner la date d'entretien avec la nouvelle heure
      const interviewDate = new Date(props.notification.scheduledFor)
      const [hours, minutes] = editTime.value.split(':')
      interviewDate.setHours(parseInt(hours), parseInt(minutes), 0, 0)
      
      const updatedDateTime = interviewDate.toISOString()
      
      console.log('Mise à jour candidature avec nouvelle date/heure:', updatedDateTime)
      
      // Mettre à jour la candidature avec la nouvelle date/heure complète
      await updateCandidature(props.notification.candidature.id, {
        dateEntretien: interviewDate.toISOString().split('T')[0], // Date seulement
        // On pourrait aussi ajouter un champ heureEntretien dans candidature
      })
    }
    
    // Émettre l'événement de mise à jour
    emit('updated', {
      ...props.notification,
      interviewTime: editTime.value
    })
    
    isEditing.value = false
    
    console.log('Heure d\'entretien mise à jour avec succès')
    
  } catch (err: any) {
    console.error('Erreur lors de la sauvegarde de l\'heure:', err)
    alert('Erreur lors de la sauvegarde de l\'heure: ' + err.message)
  } finally {
    saving.value = false
  }
}
</script>
