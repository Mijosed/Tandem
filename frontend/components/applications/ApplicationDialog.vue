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
          <div class="grid grid-cols-4 items-start gap-4">
            <Label class="text-right mt-2">Statut</Label>
            <div class="col-span-3 space-y-3">
              <RadioGroup v-model="form.status" class="flex flex-wrap gap-4">
                <div v-for="status in statuses" :key="status.value">
                  <div class="flex items-center space-x-2">
                    <RadioGroupItem :value="status.value" :id="'status-' + status.value" />
                    <Label :for="'status-' + status.value" class="flex items-center gap-2 text-sm font-normal cursor-pointer">
                      <Badge :variant="getStatusVariant(status.value)">{{ status.label }}</Badge>
                    </Label>
                  </div>
                </div>
              </RadioGroup>
            </div>
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
import { Badge } from '~/components/ui/badge'
import { RadioGroup, RadioGroupItem } from '~/components/ui/radio-group'
import { Textarea } from '~/components/ui/textarea'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '~/components/ui/dialog'
import type { Application, ApplicationStatus } from '~/types/application'

const props = defineProps<{
  open: boolean
  application: Application | null
  loading?: boolean
}>()

const emit = defineEmits<{
  (e: 'update:open', value: boolean): void
  (e: 'save', application: Application): void
}>()

const statuses = [
  { value: 'pending' as ApplicationStatus, label: 'En attente' },
  { value: 'followed_up' as ApplicationStatus, label: 'Relancé' },
  { value: 'interview' as ApplicationStatus, label: 'Entretien' },
  { value: 'rejected' as ApplicationStatus, label: 'Refusé' },
  { value: 'accepted' as ApplicationStatus, label: 'Accepté' }
]

const form = ref<{
  position: string
  company: string
  applicationDate: string
  interviewDate: string
  status: ApplicationStatus
  notes: string
}>({
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
    form.value = {
      position: newVal.position,
      company: newVal.company,
      applicationDate: newVal.applicationDate,
      interviewDate: newVal.interviewDate || '',
      status: newVal.status,
      notes: newVal.notes || ''
    }
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

const getStatusVariant = (status: string) => {
  const variants: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
    pending: 'secondary',
    followed_up: 'outline',
    interview: 'default',
    rejected: 'destructive',
    accepted: 'default'
  }
  return variants[status] || 'default'
}

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
