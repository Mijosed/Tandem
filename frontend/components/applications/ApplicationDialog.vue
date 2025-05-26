<template>
  <Dialog :open="open" @update:open="$emit('update:open', $event)">
    <DialogContent class="sm:max-w-[525px]">
      <DialogHeader>
        <DialogTitle>{{ application ? 'Modifier la candidature' : 'Nouvelle candidature' }}</DialogTitle>
        <DialogDescription>
          {{ application ? 'Modifier les informations de votre candidature' : 'Ajouter une nouvelle candidature à votre suivi' }}
        </DialogDescription>
      </DialogHeader>
      <form @submit.prevent="handleSubmit">
        <div class="grid gap-4 py-4">
          <div class="grid grid-cols-4 items-center gap-4">
            <Label class="text-right" for="position">Poste</Label>
            <Input
              id="position"
              v-model="form.position"
              class="col-span-3"
              placeholder="Ex: Développeur Full Stack"
              required
            />
          </div>
          <div class="grid grid-cols-4 items-center gap-4">
            <Label class="text-right" for="company">Entreprise</Label>
            <Input
              id="company"
              v-model="form.company"
              class="col-span-3"
              placeholder="Ex: Tech Corp"
              required
            />
          </div>
          <div class="grid grid-cols-4 items-center gap-4">
            <Label class="text-right" for="applicationDate">Date de candidature</Label>
            <Input
              id="applicationDate"
              v-model="form.applicationDate"
              type="date"
              class="col-span-3"
              required
            />
          </div>
          <div class="grid grid-cols-4 items-center gap-4">
            <Label class="text-right" for="interviewDate">Date d'entretien</Label>
            <Input
              id="interviewDate"
              v-model="form.interviewDate"
              type="date"
              class="col-span-3"
            />
          </div>
          <div class="grid grid-cols-4 items-center gap-4">
            <Label class="text-right" for="status">Statut</Label>
            <Select id="status" v-model="form.status" class="col-span-3" required>
              <option value="pending">En attente</option>
              <option value="followed_up">Relancé</option>
              <option value="interview">Entretien</option>
              <option value="rejected">Refusé</option>
              <option value="accepted">Accepté</option>
            </Select>
          </div>
          <div class="grid grid-cols-4 items-center gap-4">
            <Label class="text-right" for="notes">Notes</Label>
            <Textarea
              id="notes"
              v-model="form.notes"
              class="col-span-3"
              placeholder="Ajoutez des notes sur votre candidature..."
            />
          </div>
        </div>
        <DialogFooter>
          <Button type="submit" :disabled="isSubmitting">
            {{ application ? 'Mettre à jour' : 'Ajouter' }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { Button } from '~/components/ui/button'
import { Input } from '~/components/ui/input'
import { Label } from '~/components/ui/label'
import { Select } from '~/components/ui/select'
import { Textarea } from '~/components/ui/textarea'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '~/components/ui/dialog'

const props = defineProps<{
  open: boolean
  application: {
    id?: number
    position: string
    company: string
    applicationDate: string
    interviewDate?: string
    status: string
    notes?: string
  } | null
}>()

const emit = defineEmits<{
  (e: 'update:open', value: boolean): void
  (e: 'save', application: typeof props.application): void
}>()

const form = ref({
  position: '',
  company: '',
  applicationDate: '',
  interviewDate: '',
  status: 'pending',
  notes: '',
})

const isSubmitting = ref(false)

watch(() => props.application, (newVal) => {
  if (newVal) {
    form.value = { ...newVal }
  } else {
    form.value = {
      position: '',
      company: '',
      applicationDate: new Date().toISOString().split('T')[0],
      interviewDate: '',
      status: 'pending',
      notes: '',
    }
  }
}, { immediate: true })

const handleSubmit = async () => {
  isSubmitting.value = true
  try {
    const applicationData = {
      ...form.value,
      id: props.application?.id,
    }
    emit('save', applicationData)
  } finally {
    isSubmitting.value = false
  }
}
</script>
