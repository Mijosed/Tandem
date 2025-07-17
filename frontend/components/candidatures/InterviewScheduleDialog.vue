<template>
  <Dialog :open="open" @update:open="$emit('update:open', $event)">
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>Planifier un entretien</DialogTitle>
        <DialogDescription>
          Sélectionnez une date d'entretien pour la candidature "{{ candidature?.titrePoste }}" chez {{ candidature?.entreprise }}
        </DialogDescription>
      </DialogHeader>
      <form @submit.prevent="handleSubmit">
        <div class="grid gap-4 py-4">
          <div class="grid grid-cols-4 items-center gap-4">
            <Label class="text-right" for="interview-date">Date d'entretien</Label>
            <Input
              id="interview-date"
              v-model="selectedDate"
              type="date"
              class="col-span-3"
              required
              :min="today"
            />
          </div>
          <div class="grid grid-cols-4 items-center gap-4">
            <Label class="text-right" for="interview-notes">Notes (optionnel)</Label>
            <Textarea
              id="interview-notes"
              v-model="notes"
              class="col-span-3"
              placeholder="Ex: Entretien technique avec l'équipe..."
              rows="3"
            />
          </div>
        </div>
        <DialogFooter>
          <Button type="button" variant="outline" @click="$emit('update:open', false)">
            Annuler
          </Button>
          <Button type="submit" :disabled="!selectedDate || isSubmitting">
            <CalendarDays class="mr-2 h-4 w-4" />
            {{ isSubmitting ? 'Planification...' : 'Planifier l\'entretien' }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { CalendarDays } from 'lucide-vue-next'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '~/components/ui/dialog'
import { Button } from '~/components/ui/button'
import { Input } from '~/components/ui/input'
import { Label } from '~/components/ui/label'
import { Textarea } from '~/components/ui/textarea'
import type { Candidature } from '~/types/candidature'

interface Props {
  open: boolean
  candidature?: Candidature | null
}

interface Emits {
  (e: 'update:open', value: boolean): void
  (e: 'schedule', data: { candidature: Candidature, dateEntretien: string, notes: string }): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const selectedDate = ref('')
const notes = ref('')
const isSubmitting = ref(false)

// Date d'aujourd'hui au format YYYY-MM-DD pour la validation
const today = computed(() => {
  return new Date().toISOString().split('T')[0]
})

// Réinitialiser le formulaire quand le dialog s'ouvre
watch(() => props.open, (newValue) => {
  if (newValue) {
    selectedDate.value = props.candidature?.dateEntretien || ''
    notes.value = props.candidature?.notes || ''
  }
})

const handleSubmit = async () => {
  if (!props.candidature || !selectedDate.value) return

  isSubmitting.value = true
  try {
    emit('schedule', {
      candidature: props.candidature,
      dateEntretien: selectedDate.value,
      notes: notes.value
    })
    emit('update:open', false)
  } finally {
    isSubmitting.value = false
  }
}
</script>
