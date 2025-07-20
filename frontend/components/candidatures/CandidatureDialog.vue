<template>
  <Dialog :open="open" @update:open="$emit('update:open', $event)">
    <DialogContent class="sm:max-w-[525px]">
      <DialogHeader>
        <DialogTitle>{{ candidature ? 'Modifier la candidature' : 'Nouvelle candidature' }}</DialogTitle>
        <DialogDescription>
          {{ candidature ? 'Modifier les informations de votre candidature' : 'Ajouter une nouvelle candidature à votre suivi' }}
        </DialogDescription>
      </DialogHeader>
      <form @submit.prevent="handleSubmit">
        <div class="grid gap-4 py-4">
          <div class="grid grid-cols-4 items-center gap-4">
            <Label class="text-right" for="position">Poste</Label>
            <Input
              id="position"
              v-model="form.titrePoste"
              class="col-span-3"
              placeholder="Ex: Développeur Full Stack"
              required
            />
          </div>
          <div class="grid grid-cols-4 items-center gap-4">
            <Label class="text-right" for="company">Entreprise</Label>
            <Input
              id="company"
              v-model="form.entreprise"
              class="col-span-3"
              placeholder="Ex: Tech Corp"
              required
            />
          </div>
          <div class="grid grid-cols-4 items-center gap-4">
            <Label class="text-right" for="applicationDate">Date de candidature</Label>
            <Input
              id="applicationDate"
              v-model="form.dateDepot"
              type="date"
              class="col-span-3"
              required
            />
          </div>
          <div class="grid grid-cols-4 items-center gap-4">
            <Label class="text-right" for="interviewDate">Date d'entretien</Label>
            <Input
              id="interviewDate"
              v-model="form.dateEntretien"
              type="date"
              class="col-span-3"
            />
          </div>
          <div class="grid grid-cols-4 items-center gap-4">
            <Label class="text-right" for="interviewTime">Heure d'entretien</Label>
            <Input
              id="interviewTime"
              v-model="form.heureEntretien"
              type="time"
              class="col-span-3"
              placeholder="14:30"
              :disabled="!form.dateEntretien"
            />
          </div>
          <div class="grid grid-cols-4 items-start gap-4">
            <Label class="text-right mt-2">Statut</Label>
            <div class="col-span-3 space-y-3">
              <RadioGroup v-model="form.statut" class="flex flex-wrap gap-4">
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
            {{ candidature ? 'Mettre à jour' : 'Ajouter' }}
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
import type { Candidature, CandidatureStatus } from '~/types/candidature'

const props = defineProps<{
  open: boolean
  candidature: Candidature | null
  loading?: boolean
}>()

const emit = defineEmits<{
  (e: 'update:open', value: boolean): void
  (e: 'save', candidature: Candidature): void
}>()

const statuses = [
  { value: 'a_faire' as CandidatureStatus, label: 'À faire' },
  { value: 'en_attente' as CandidatureStatus, label: 'En attente' },
  { value: 'relance' as CandidatureStatus, label: 'Relancé' },
  { value: 'entretien' as CandidatureStatus, label: 'Entretien' },
  { value: 'refuse' as CandidatureStatus, label: 'Refusé' },
  { value: 'accepte' as CandidatureStatus, label: 'Accepté' }
]

const form = ref<{
  titrePoste: string
  entreprise: string
  dateDepot: string
  dateEntretien: string
  heureEntretien: string
  statut: CandidatureStatus
  notes: string
}>({
  titrePoste: '',
  entreprise: '',
  dateDepot: '',
  dateEntretien: '',
  heureEntretien: '',
  statut: 'a_faire',
  notes: '',
})

const isSubmitting = ref(false)

watch(() => props.candidature, (newVal) => {
  console.log('🔧 DEBUG CandidatureDialog - watch candidature:', newVal)
  if (newVal) {
    console.log('  heureEntretien from props:', newVal.heureEntretien, typeof newVal.heureEntretien)
    form.value = {
      titrePoste: newVal.titrePoste,
      entreprise: newVal.entreprise,
      dateDepot: newVal.dateDepot,
      dateEntretien: newVal.dateEntretien || '',
      heureEntretien: newVal.heureEntretien || '',
      statut: newVal.statut,
      notes: newVal.notes || ''
    }
    console.log('  form.heureEntretien after set:', form.value.heureEntretien, typeof form.value.heureEntretien)
  } else {
    form.value = {
      titrePoste: '',
      entreprise: '',
      dateDepot: new Date().toISOString().split('T')[0],
      dateEntretien: '',
      heureEntretien: '',
      statut: 'a_faire',
      notes: '',
    }
  }
}, { immediate: true })

const getStatusVariant = (status: string) => {
  const variants: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
    a_faire: 'secondary',
    en_attente: 'outline',
    relance: 'outline',
    entretien: 'default',
    refuse: 'destructive',
    accepte: 'default'
  }
  return variants[status] || 'default'
}

const handleSubmit = async () => {
  isSubmitting.value = true
  try {
    const candidatureData = {
      ...form.value,
      id: props.candidature?.id,
    }
    
    // Debug logs pour l'heure
    console.log('🔧 DEBUG CandidatureDialog - handleSubmit:')
    console.log('  form.heureEntretien:', form.value.heureEntretien, typeof form.value.heureEntretien)
    console.log('  candidatureData.heureEntretien:', candidatureData.heureEntretien, typeof candidatureData.heureEntretien)
    
    emit('save', candidatureData)
  } finally {
    isSubmitting.value = false
  }
}
</script>
