<template>
  <Dialog>
    <DialogTrigger asChild>
      <Button class="ml-auto">
        <Plus class="h-4 w-4 mr-2" />
        Nouvelle notification
      </Button>
    </DialogTrigger>
    <DialogContent class="sm:max-w-[500px]">
      <DialogHeader>
        <DialogTitle>Nouvelle notification</DialogTitle>
      </DialogHeader>
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div class="space-y-2">
          <Label for="title">Titre</Label>
          <Input id="title" v-model="form.title" placeholder="Titre de la notification" />
        </div>
        
        <div class="space-y-2">
          <Label for="message">Message</Label>
          <Textarea 
            id="message" 
            v-model="form.message" 
            placeholder="Contenu de la notification"
            :rows="3"
          />
        </div>

        <div class="space-y-2">
          <Label>Type de notification</Label>
          <Select v-model="form.type">
            <SelectTrigger>
              <SelectValue placeholder="Sélectionnez un type" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="reminder">
                <div class="flex items-center gap-2">
                  <Bell class="h-4 w-4 text-blue-500" />
                  <span>Rappel</span>
                </div>
              </SelectItem>
              <SelectItem value="interview">
                <div class="flex items-center gap-2">
                  <Calendar class="h-4 w-4 text-green-500" />
                  <span>Entretien</span>
                </div>
              </SelectItem>
              <SelectItem value="info">
                <div class="flex items-center gap-2">
                  <Info class="h-4 w-4 text-yellow-500" />
                  <span>Information</span>
                </div>
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label>Date de l'événement</Label>
            <Popover>
              <PopoverTrigger asChild>
                <Button
                  variant="outline"
                  class="w-full justify-start text-left font-normal"
                  :class="!form.eventDate && 'text-muted-foreground'"
                >
                  <Calendar class="mr-2 h-4 w-4" />
                  {{ form.eventDate ? formatDate(form.eventDate) : "Date de l'événement" }}
                </Button>
              </PopoverTrigger>
              <PopoverContent class="w-auto p-0">
                <CalendarComponent
                  mode="single"
                  :selected="form.eventDate"
                  @update:model-value="updateEventDate"
                  :disabled-dates="{ before: new Date() }"
                  initialFocus
                />
              </PopoverContent>
            </Popover>
          </div>

          <div class="space-y-2">
            <Label>Date de rappel</Label>
            <Popover>
              <PopoverTrigger asChild>
                <Button
                  variant="outline"
                  class="w-full justify-start text-left font-normal"
                  :class="!form.reminderDate && 'text-muted-foreground'"
                >
                  <Bell class="mr-2 h-4 w-4" />
                  {{ form.reminderDate ? formatDate(form.reminderDate) : "Date de rappel" }}
                </Button>
              </PopoverTrigger>
              <PopoverContent class="w-auto p-0">
                <CalendarComponent
                  mode="single"
                  :selected="form.reminderDate"
                  @update:model-value="updateReminderDate"
                  :disabled-dates="{ before: new Date() }"
                  initialFocus
                />
              </PopoverContent>
            </Popover>
          </div>
        </div>

        <DialogFooter>
          <Button type="submit" :disabled="!isValid">Créer la notification</Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Bell, Calendar, Info, Plus } from 'lucide-vue-next'
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '~/components/ui/dialog'
import { Popover, PopoverContent, PopoverTrigger } from '~/components/ui/popover'
import { Calendar as CalendarComponent } from '~/components/ui/calendar'
import { Button } from '~/components/ui/button'
import { Input } from '~/components/ui/input'
import { Label } from '~/components/ui/label'
import { Textarea } from '~/components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '~/components/ui/select'

interface NotificationForm {
  title: string
  message: string
  type: 'reminder' | 'interview' | 'info' | ''
  reminderDate?: Date | null
  eventDate?: Date | null
}

const emit = defineEmits<{
  'create': [notification: { 
    title: string, 
    message: string, 
    type: 'reminder' | 'interview' | 'info',
    reminderDate?: Date | null,
    eventDate?: Date | null
  }]
}>()

const form = ref<NotificationForm>({
  title: '',
  message: '',
  type: '',
  eventDate: null,
  reminderDate: null
})

const isValid = computed(() => {
  return form.value.title.trim() !== '' && 
         form.value.message.trim() !== '' && 
         form.value.type !== ''
})

const updateEventDate = (date: Date | null) => {
  form.value.eventDate = date
  // Si la date de rappel est après la date de l'événement, on la réinitialise
  if (form.value.reminderDate && date && form.value.reminderDate > date) {
    form.value.reminderDate = null
  }
}

const updateReminderDate = (date: Date | null) => {
  // Ne pas permettre une date de rappel après la date de l'événement
  if (date && form.value.eventDate && date > form.value.eventDate) {
    return
  }
  form.value.reminderDate = date
}

const formatDate = (date: Date) => {
  return new Intl.DateTimeFormat('fr-FR', {
    dateStyle: 'long'
  }).format(date)
}

const handleSubmit = () => {
  if (!isValid.value || form.value.type === '') return
  
  emit('create', {
    title: form.value.title.trim(),
    message: form.value.message.trim(),
    type: form.value.type,
    eventDate: form.value.eventDate,
    reminderDate: form.value.reminderDate
  })

  // Reset form
  form.value = {
    title: '',
    message: '',
    type: '',
    eventDate: null,
    reminderDate: null
  }
}
</script>
