<script setup>
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'
import { SidebarTrigger } from '~/components/ui/sidebar'
import { Button } from '~/components/ui/button'
import { useMediaQuery } from '@vueuse/core'
import { Plus, Calendar, Clock, MapPin, Trash2, Edit } from 'lucide-vue-next'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '~/components/ui/dialog'
import { Input } from '~/components/ui/input'
import { Label } from '~/components/ui/label'
import { Textarea } from '~/components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '~/components/ui/select'
import { Switch } from '~/components/ui/switch'
import { useToast } from '~/components/ui/toast/use-toast'

definePageMeta({
  layout: 'dashboard',
  middleware: ['auth']
})

// Détection mobile pour afficher conditionnellement le SidebarTrigger
const isMobile = useMediaQuery('(max-width: 768px)')

// Composables
const { events, loading, error, fetchEvents, createEvent, updateEvent, deleteEvent, eventStats, getEventColor } = useSchedule()
const { toast } = useToast()

// État local
const isDialogOpen = ref(false)
const isEditMode = ref(false)
const selectedEvent = ref(null)
const newEvent = ref({
  title: '',
  description: '',
  startDate: '',
  endDate: '',
  type: 'personal',
  location: '',
  allDay: false
})

// Types d'événements disponibles
const eventTypes = [
  { value: 'personal', label: 'Personnel', color: '#8b5cf6' },
  { value: 'meeting', label: 'Réunion', color: '#3b82f6' },
  { value: 'interview', label: 'Entretien', color: '#10b981' },
  { value: 'reminder', label: 'Rappel', color: '#f59e0b' },
  { value: 'deadline', label: 'Échéance', color: '#ef4444' }
]

// Configuration du calendrier
const calendarOptions = computed(() => ({
  plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
  initialView: 'dayGridMonth',
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth,timeGridWeek,timeGridDay'
  },
  locale: 'fr',
  weekends: true,
  height: 'auto',
  aspectRatio: 1.35,
  selectable: true,
  selectMirror: true,
  dayMaxEvents: true,
  events: events.value,
  eventColor: '#3b82f6',
  eventTextColor: '#ffffff',
  eventBorderColor: '#2563eb',
  select: handleDateSelect,
  eventClick: handleEventClick,
  editable: true,
  droppable: true,
  eventDrop: handleEventDrop,
  eventResize: handleEventResize
}))

// Charger les événements au montage
onMounted(() => {
  fetchEvents()
})

// Méthodes
const openNewEventDialog = () => {
  isEditMode.value = false
  selectedEvent.value = null
  resetNewEvent()
  isDialogOpen.value = true
}

const resetNewEvent = () => {
  newEvent.value = {
    title: '',
    description: '',
    startDate: '',
    endDate: '',
    type: 'personal',
    location: '',
    allDay: false
  }
}

const handleDateSelect = (selectInfo) => {
  // Pré-remplir la date sélectionnée
  const startDate = selectInfo.startStr
  const endDate = selectInfo.endStr || selectInfo.startStr
  
  newEvent.value.startDate = startDate
  newEvent.value.endDate = endDate
  
  openNewEventDialog()
}

const handleEventClick = (clickInfo) => {
  const event = clickInfo.event
  selectedEvent.value = event
  
  // Pré-remplir le formulaire avec les données de l'événement
  newEvent.value = {
    title: event.title,
    description: event.extendedProps.description || '',
    startDate: event.start.toISOString().slice(0, 16),
    endDate: event.end ? event.end.toISOString().slice(0, 16) : event.start.toISOString().slice(0, 16),
    type: event.extendedProps.type || 'personal',
    location: event.extendedProps.location || '',
    allDay: event.allDay
  }
  
  isEditMode.value = true
  isDialogOpen.value = true
}

const saveEvent = async () => {
  if (!newEvent.value.title || !newEvent.value.startDate) {
    toast({
      title: "Erreur",
      description: "Le titre et la date de début sont obligatoires",
      variant: "destructive"
    })
    return
  }

  // Si pas de date de fin, utiliser la date de début
  if (!newEvent.value.endDate) {
    newEvent.value.endDate = newEvent.value.startDate
  }

  try {
    if (isEditMode.value && selectedEvent.value) {
      await updateEvent(selectedEvent.value.id, newEvent.value)
      toast({
        title: "Succès",
        description: "Événement mis à jour avec succès"
      })
    } else {
      await createEvent(newEvent.value)
      toast({
        title: "Succès",
        description: "Événement créé avec succès"
      })
    }
    
    isDialogOpen.value = false
    resetNewEvent()
  } catch (err) {
    toast({
      title: "Erreur",
      description: "Une erreur est survenue lors de la sauvegarde",
      variant: "destructive"
    })
  }
}

const removeEvent = async () => {
  if (!selectedEvent.value) return
  
  try {
    await deleteEvent(selectedEvent.value.id)
    toast({
      title: "Succès",
      description: "Événement supprimé avec succès"
    })
    isDialogOpen.value = false
  } catch (err) {
    toast({
      title: "Erreur",
      description: "Une erreur est survenue lors de la suppression",
      variant: "destructive"
    })
  }
}

const handleEventDrop = async (info) => {
  const event = info.event
  try {
    await updateEvent(event.id, {
      title: event.title,
      startDate: event.start.toISOString(),
      endDate: event.end ? event.end.toISOString() : event.start.toISOString(),
      type: event.extendedProps.type,
      allDay: event.allDay
    })
  } catch (err) {
    info.revert()
    toast({
      title: "Erreur",
      description: "Impossible de déplacer l'événement",
      variant: "destructive"
    })
  }
}

const handleEventResize = async (info) => {
  const event = info.event
  try {
    await updateEvent(event.id, {
      title: event.title,
      startDate: event.start.toISOString(),
      endDate: event.end ? event.end.toISOString() : event.start.toISOString(),
      type: event.extendedProps.type,
      allDay: event.allDay
    })
  } catch (err) {
    info.revert()
    toast({
      title: "Erreur",
      description: "Impossible de redimensionner l'événement",
      variant: "destructive"
    })
  }
}
</script>
<template>
  <div>
    <header class="flex h-16 shrink-0 items-center gap-2 transition-[width,height] ease-linear">
      <div class="flex items-center gap-2 px-4">
        <SidebarTrigger v-if="isMobile" class="-ml-1" />
        <h1 class="text-2xl font-bold">Planning</h1>
      </div>
    </header>

    <div class="container py-6 px-4">
      <!-- Statistiques -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-card text-card-foreground rounded-lg border p-4">
          <div class="flex items-center space-x-2">
            <Calendar class="h-4 w-4 text-muted-foreground" />
            <span class="text-sm font-medium">Total</span>
          </div>
          <div class="text-2xl font-bold">{{ eventStats.total }}</div>
        </div>
        <div class="bg-card text-card-foreground rounded-lg border p-4">
          <div class="flex items-center space-x-2">
            <Clock class="h-4 w-4 text-muted-foreground" />
            <span class="text-sm font-medium">Aujourd'hui</span>
          </div>
          <div class="text-2xl font-bold">{{ eventStats.today }}</div>
        </div>
        <div class="bg-card text-card-foreground rounded-lg border p-4">
          <div class="flex items-center space-x-2">
            <Calendar class="h-4 w-4 text-muted-foreground" />
            <span class="text-sm font-medium">À venir</span>
          </div>
          <div class="text-2xl font-bold">{{ eventStats.upcoming }}</div>
        </div>
        <div class="bg-card text-card-foreground rounded-lg border p-4">
          <div class="flex items-center space-x-2">
            <MapPin class="h-4 w-4 text-muted-foreground" />
            <span class="text-sm font-medium">Entretiens</span>
          </div>
          <div class="text-2xl font-bold">{{ eventStats.byType.interview || 0 }}</div>
        </div>
      </div>

      <div class="flex justify-between items-center mb-8">
        <p class="text-muted-foreground">Gérez votre planning et vos rendez-vous</p>
        <Button @click="openNewEventDialog">
          <Plus class="mr-2 h-4 w-4" />
          Nouvel événement
        </Button>
      </div>

      <!-- Calendrier -->
      <div class="max-w-4xl mx-auto">
        <FullCalendar 
          v-if="!loading"
          :options="calendarOptions" 
          class="modern-calendar" 
        />
        <div v-else class="flex justify-center py-8">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
        </div>
      </div>

      <!-- Dialog pour créer/modifier un événement -->
      <Dialog v-model:open="isDialogOpen">
        <DialogContent class="sm:max-w-md">
          <DialogHeader>
            <DialogTitle>
              {{ isEditMode ? 'Modifier l\'événement' : 'Nouvel événement' }}
            </DialogTitle>
            <DialogDescription>
              {{ isEditMode ? 'Modifiez les détails de votre événement' : 'Créez un nouvel événement dans votre planning' }}
            </DialogDescription>
          </DialogHeader>
          
          <div class="grid gap-4 py-4">
            <div class="grid gap-2">
              <Label for="event-title">Titre de l'événement</Label>
              <Input
                id="event-title"
                v-model="newEvent.title"
                placeholder="Ex: Réunion équipe"
              />
            </div>
            
            <div class="grid gap-2">
              <Label for="event-description">Description (optionnel)</Label>
              <Textarea
                id="event-description"
                v-model="newEvent.description"
                placeholder="Détails de l'événement..."
                rows="3"
              />
            </div>
            
            <div class="grid gap-2">
              <Label for="event-type">Type d'événement</Label>
              <Select v-model="newEvent.type">
                <SelectTrigger>
                  <SelectValue placeholder="Choisir un type" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="type in eventTypes" :key="type.value" :value="type.value">
                    <div class="flex items-center space-x-2">
                      <div 
                        class="w-3 h-3 rounded-full" 
                        :style="{ backgroundColor: type.color }"
                      ></div>
                      <span>{{ type.label }}</span>
                    </div>
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>
            
            <div class="grid gap-2">
              <Label for="event-location">Lieu (optionnel)</Label>
              <Input
                id="event-location"
                v-model="newEvent.location"
                placeholder="Ex: Salle de conférence A"
              />
            </div>
            
            <div class="flex items-center space-x-2">
              <Switch
                id="all-day"
                v-model="newEvent.allDay"
              />
              <Label for="all-day">Toute la journée</Label>
            </div>
            
            <div class="grid gap-2">
              <Label for="event-start">Date et heure de début</Label>
              <Input
                id="event-start"
                :type="newEvent.allDay ? 'date' : 'datetime-local'"
                v-model="newEvent.startDate"
              />
            </div>
            
            <div v-if="!newEvent.allDay" class="grid gap-2">
              <Label for="event-end">Date et heure de fin</Label>
              <Input
                id="event-end"
                type="datetime-local"
                v-model="newEvent.endDate"
              />
            </div>
          </div>
          
          <DialogFooter>
            <div class="flex justify-between w-full">
              <div>
                <Button 
                  v-if="isEditMode" 
                  variant="destructive" 
                  @click="removeEvent"
                >
                  <Trash2 class="mr-2 h-4 w-4" />
                  Supprimer
                </Button>
              </div>
              <div class="space-x-2">
                <Button variant="outline" @click="isDialogOpen = false">
                  Annuler
                </Button>
                <Button @click="saveEvent" :disabled="loading">
                  {{ isEditMode ? 'Modifier' : 'Créer' }}
                </Button>
              </div>
            </div>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  </div>
</template>

<style scoped>
.modern-calendar {
  border-radius: 0.5rem;
  border: 1px solid hsl(var(--border));
  box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
}

:deep(.fc) {
  font-family: inherit;
}

:deep(.fc-toolbar) {
  margin-bottom: 1rem;
}

:deep(.fc-toolbar-title) {
  font-size: 1.25rem;
  font-weight: 600;
  color: hsl(var(--foreground));
}

:deep(.fc-button) {
  background-color: hsl(var(--primary));
  color: hsl(var(--primary-foreground));
  border: 1px solid hsl(var(--primary));
  border-radius: 6px;
  font-weight: 500;
  transition: all 0.2s;
}

:deep(.fc-button:hover) {
  background-color: hsl(var(--primary) / 0.9);
}

:deep(.fc-button:not(:disabled):active),
:deep(.fc-button:not(:disabled).fc-button-active) {
  background-color: hsl(var(--primary) / 0.8);
}

:deep(.fc-daygrid-day) {
  transition: background-color 0.2s;
}

:deep(.fc-daygrid-day:hover) {
  background-color: hsl(var(--muted) / 0.5);
}

:deep(.fc-daygrid-day-number) {
  color: hsl(var(--foreground));
  font-weight: 500;
}

:deep(.fc-col-header-cell) {
  background-color: hsl(var(--muted) / 0.3);
  font-weight: 600;
  color: hsl(var(--muted-foreground));
}

:deep(.fc-event) {
  border-radius: 6px;
  border: 0;
  box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
}

:deep(.fc-event:hover) {
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
  transform: translateY(-1px);
  transition: all 0.2s;
}

:deep(.fc-daygrid-event-dot) {
  border-color: hsl(var(--primary));
}

:deep(.fc-today) {
  background-color: hsl(var(--primary) / 0.05);
}
</style>