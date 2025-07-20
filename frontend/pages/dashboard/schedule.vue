<script setup>
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'
import { SidebarTrigger } from '~/components/ui/sidebar'
import { Button } from '~/components/ui/button'
import { useMediaQuery } from '@vueuse/core'
import { Plus, Calendar, Clock, MapPin, Trash2, Edit, Filter, ChevronDown } from 'lucide-vue-next'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '~/components/ui/dialog'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '~/components/ui/dropdown-menu'
import { Input } from '~/components/ui/input'
import { Label } from '~/components/ui/label'
import { Textarea } from '~/components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '~/components/ui/select'
import { Switch } from '~/components/ui/switch'
import { Badge } from '~/components/ui/badge'
import { useToast } from '~/components/ui/toast/use-toast'

definePageMeta({
  layout: 'dashboard',
  middleware: ['auth']
})

const isMobile = useMediaQuery('(max-width: 768px)')

const { events, loading, error, fetchEvents, createEvent, updateEvent, deleteEvent, eventStats, getEventColor } = useSchedule()
const { toast } = useToast()

const isDialogOpen = ref(false)
const isEditMode = ref(false)
const selectedEvent = ref(null)
const filterType = ref('all')
const currentView = ref('dayGridMonth')
const newEvent = ref({
  title: '',
  description: '',
  startDate: '',
  endDate: '',
  type: 'personal',
  location: '',
  allDay: false
})

const eventTypes = [
  { value: 'personal', label: 'Personnel', color: '#8b5cf6', icon: '👤' },
  { value: 'meeting', label: 'Réunion', color: '#3b82f6', icon: '🤝' },
  { value: 'interview', label: 'Entretien', color: '#10b981', icon: '💼' },
  { value: 'reminder', label: 'Rappel', color: '#f59e0b', icon: '⏰' },
  { value: 'deadline', label: 'Échéance', color: '#ef4444', icon: '🎯' }
]

const filteredEvents = computed(() => {
  if (filterType.value === 'all') return events.value
  return events.value.filter(event => event.extendedProps?.type === filterType.value)
})

const calendarOptions = computed(() => ({
  plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
  initialView: currentView.value,
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth,timeGridWeek,timeGridDay'
  },
  locale: 'fr',
  weekends: true,
  height: 'auto',
  aspectRatio: 1.5,
  selectable: true,
  selectMirror: true,
  dayMaxEvents: 3,
  events: filteredEvents.value,
  eventDisplay: 'block',
  eventClassNames: 'calendar-event',
  select: handleDateSelect,
  eventClick: handleEventClick,
  editable: true,
  droppable: true,
  eventDrop: handleEventDrop,
  eventResize: handleEventResize,
  dateClick: handleDateClick,
  viewDidMount: (info) => {
    currentView.value = info.view.type
  }
}))

onMounted(() => {
  fetchEvents()
})

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
  const startDate = selectInfo.startStr
  const endDate = selectInfo.endStr || selectInfo.startStr
  
  newEvent.value.startDate = startDate
  newEvent.value.endDate = endDate
  
  openNewEventDialog()
}

const handleDateClick = (info) => {
  if (currentView.value === 'dayGridMonth') {
    newEvent.value.startDate = info.dateStr
    newEvent.value.endDate = info.dateStr
    openNewEventDialog()
  }
}

const handleEventClick = (clickInfo) => {
  const event = clickInfo.event
  selectedEvent.value = event
  
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
    
    toast({
      title: "Succès",
      description: "Événement déplacé avec succès"
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
    
    toast({
      title: "Succès",
      description: "Durée mise à jour avec succès"
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

const getTypeIcon = (type) => {
  const eventType = eventTypes.find(t => t.value === type)
  return eventType?.icon || '📅'
}

const getTypeLabel = (type) => {
  const eventType = eventTypes.find(t => t.value === type)
  return eventType?.label || 'Personnel'
}
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-background via-background to-muted/20">
    <header class="sticky top-0 z-50 w-full border-b bg-background/80 backdrop-blur-md supports-[backdrop-filter]:bg-background/60">
      <div class="container flex h-16 items-center justify-between px-4">
        <div class="flex items-center gap-3">
          <SidebarTrigger v-if="isMobile" class="-ml-1" />
          <div class="flex items-center gap-2">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10">
              <Calendar class="h-4 w-4 text-primary" />
            </div>
            <h1 class="text-2xl font-bold bg-gradient-to-r from-foreground to-foreground/70 bg-clip-text">
              Planning
            </h1>
          </div>
        </div>
        
        <div class="flex items-center gap-3">
          <DropdownMenu>
            <DropdownMenuTrigger asChild>
              <Button variant="outline" size="sm" class="gap-2">
                <Filter class="h-4 w-4" />
                <span class="hidden sm:inline">
                  {{ filterType === 'all' ? 'Tous' : getTypeLabel(filterType) }}
                </span>
                <ChevronDown class="h-4 w-4" />
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" class="w-48">
              <DropdownMenuLabel>Filtrer par type</DropdownMenuLabel>
              <DropdownMenuSeparator />
              <DropdownMenuItem @click="filterType = 'all'">
                <span class="mr-2">📅</span>
                Tous les événements
              </DropdownMenuItem>
              <DropdownMenuItem 
                v-for="type in eventTypes" 
                :key="type.value"
                @click="filterType = type.value"
              >
                <span class="mr-2">{{ type.icon }}</span>
                {{ type.label }}
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
          
          
          <Button @click="openNewEventDialog" class="gap-2 shadow-md">
            <Plus class="h-4 w-4" />
            <span class="hidden sm:inline">Nouvel événement</span>
          </Button>
        </div>
      </div>
    </header>

    <div class="container py-8 px-4 space-y-8">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="group relative overflow-hidden rounded-xl border bg-card p-6 transition-all hover:shadow-lg hover:shadow-primary/5">
          <div class="flex items-center justify-between">
            <div class="space-y-2">
              <p class="text-sm font-medium text-muted-foreground">Total événements</p>
              <div class="flex items-baseline gap-2">
                <p class="text-3xl font-bold">{{ eventStats.total }}</p>
                <Badge variant="secondary" class="text-xs">{{ filteredEvents.length }} affichés</Badge>
              </div>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 group-hover:bg-primary/20 transition-colors">
              <Calendar class="h-6 w-6 text-primary" />
            </div>
          </div>
        </div>

        <div class="group relative overflow-hidden rounded-xl border bg-card p-6 transition-all hover:shadow-lg hover:shadow-emerald-500/5">
          <div class="flex items-center justify-between">
            <div class="space-y-2">
              <p class="text-sm font-medium text-muted-foreground">Aujourd'hui</p>
              <p class="text-3xl font-bold text-emerald-600">{{ eventStats.today }}</p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-900/20 group-hover:bg-emerald-200 dark:group-hover:bg-emerald-900/30 transition-colors">
              <Clock class="h-6 w-6 text-emerald-600" />
            </div>
          </div>
        </div>

        <div class="group relative overflow-hidden rounded-xl border bg-card p-6 transition-all hover:shadow-lg hover:shadow-blue-500/5">
          <div class="flex items-center justify-between">
            <div class="space-y-2">
              <p class="text-sm font-medium text-muted-foreground">À venir</p>
              <p class="text-3xl font-bold text-blue-600">{{ eventStats.upcoming }}</p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/20 group-hover:bg-blue-200 dark:group-hover:bg-blue-900/30 transition-colors">
              <Calendar class="h-6 w-6 text-blue-600" />
            </div>
          </div>
        </div>

        <div class="group relative overflow-hidden rounded-xl border bg-card p-6 transition-all hover:shadow-lg hover:shadow-amber-500/5">
          <div class="flex items-center justify-between">
            <div class="space-y-2">
              <p class="text-sm font-medium text-muted-foreground">Entretiens</p>
              <p class="text-3xl font-bold text-amber-600">{{ eventStats.byType.interview || 0 }}</p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-900/20 group-hover:bg-amber-200 dark:group-hover:bg-amber-900/30 transition-colors">
              <MapPin class="h-6 w-6 text-amber-600" />
            </div>
          </div>
        </div>
      </div>

      <div class="flex flex-wrap gap-3 p-4 rounded-xl bg-muted/30 border">
        <span class="text-sm font-medium text-muted-foreground">Types d'événements :</span>
        <div class="flex flex-wrap gap-2">
          <Badge 
            v-for="type in eventTypes" 
            :key="type.value"
            variant="outline" 
            class="gap-1 cursor-pointer hover:bg-muted transition-colors"
            @click="filterType = filterType === type.value ? 'all' : type.value"
            :class="{ 'bg-muted': filterType === type.value }"
          >
            <span>{{ type.icon }}</span>
            {{ type.label }}
          </Badge>
        </div>
      </div>

      <div class="rounded-xl border bg-card shadow-sm overflow-hidden">
        <div class="p-6">
          <div v-if="loading" class="flex items-center justify-center py-12">
            <div class="flex flex-col items-center gap-3">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
              <p class="text-sm text-muted-foreground">Chargement des événements...</p>
            </div>
          </div>
          
          <FullCalendar 
            v-else-if="!error"
            :options="calendarOptions" 
            class="modern-calendar" 
          />
          
          <div v-else class="flex flex-col items-center justify-center py-12 text-center">
            <div class="rounded-full bg-destructive/10 p-3 mb-4">
              <Calendar class="h-6 w-6 text-destructive" />
            </div>
            <h3 class="text-lg font-semibold mb-2">Erreur de chargement</h3>
            <p class="text-muted-foreground mb-4">{{ error }}</p>
            <Button @click="fetchEvents" variant="outline">
              Réessayer
            </Button>
          </div>
        </div>
      </div>

      <Dialog v-model:open="isDialogOpen">
        <DialogContent class="sm:max-w-[540px]">
          <DialogHeader>
            <DialogTitle class="flex items-center gap-2">
              <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10">
                <Plus v-if="!isEditMode" class="h-4 w-4 text-primary" />
                <Edit v-else class="h-4 w-4 text-primary" />
              </div>
              {{ isEditMode ? 'Modifier l\'événement' : 'Nouvel événement' }}
            </DialogTitle>
            <DialogDescription>
              {{ isEditMode ? 'Modifiez les détails de votre événement' : 'Créez un nouvel événement dans votre planning' }}
            </DialogDescription>
          </DialogHeader>
          
          <form @submit.prevent="saveEvent" class="space-y-6">
            <div class="grid gap-4">
              <div class="grid gap-2">
                <Label for="event-title">Titre de l'événement *</Label>
                <Input
                  id="event-title"
                  v-model="newEvent.title"
                  placeholder="Ex: Entretien chez TechCorp"
                  required
                  class="text-base"
                />
              </div>
              
              <div class="grid gap-2">
                <Label for="event-type">Type d'événement</Label>
                <Select v-model="newEvent.type">
                  <SelectTrigger id="event-type">
                    <SelectValue>
                      <div class="flex items-center gap-2">
                        <span>{{ getTypeIcon(newEvent.type) }}</span>
                        {{ getTypeLabel(newEvent.type) }}
                      </div>
                    </SelectValue>
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem 
                      v-for="type in eventTypes" 
                      :key="type.value" 
                      :value="type.value"
                      class="cursor-pointer"
                    >
                      <div class="flex items-center gap-2">
                        <span>{{ type.icon }}</span>
                        {{ type.label }}
                      </div>
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>
              
              <div class="grid gap-2">
                <Label for="event-description">Description</Label>
                <Textarea
                  id="event-description"
                  v-model="newEvent.description"
                  placeholder="Ajoutez des détails sur cet événement..."
                  rows="3"
                />
              </div>
              
              <div class="grid gap-2">
                <Label for="event-location">Lieu</Label>
                <Input
                  id="event-location"
                  v-model="newEvent.location"
                  placeholder="Ex: Bureau, Paris, Visioconférence..."
                />
              </div>
              
              <div class="flex items-center space-x-3 p-3 rounded-lg bg-muted/50">
                <Switch
                  id="all-day"
                  v-model="newEvent.allDay"
                />
                <Label for="all-day" class="text-sm font-medium">Toute la journée</Label>
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="grid gap-2">
                  <Label for="event-start">{{ newEvent.allDay ? 'Date de début' : 'Date et heure de début' }} *</Label>
                  <Input
                    id="event-start"
                    :type="newEvent.allDay ? 'date' : 'datetime-local'"
                    v-model="newEvent.startDate"
                    required
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
            </div>
            
            <DialogFooter class="flex flex-col sm:flex-row gap-3">
              <div class="flex-1">
                <Button 
                  v-if="isEditMode" 
                  type="button"
                  variant="destructive" 
                  @click="removeEvent"
                  class="w-full sm:w-auto"
                >
                  <Trash2 class="mr-2 h-4 w-4" />
                  Supprimer
                </Button>
              </div>
              <div class="flex gap-2">
                <Button type="button" variant="outline" @click="isDialogOpen = false" class="flex-1 sm:flex-none">
                  Annuler
                </Button>
                <Button type="submit" :disabled="loading" class="flex-1 sm:flex-none">
                  <Plus v-if="!isEditMode" class="mr-2 h-4 w-4" />
                  <Edit v-else class="mr-2 h-4 w-4" />
                  {{ isEditMode ? 'Modifier' : 'Créer' }}
                </Button>
              </div>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>
    </div>
  </div>
</template>

<style scoped>
.modern-calendar {
  border-radius: 0.75rem;
  overflow: hidden;
}

:deep(.fc) {
  font-family: inherit;
  border-radius: 0.75rem;
}

:deep(.fc-toolbar) {
  margin-bottom: 1.5rem;
  gap: 1rem;
}

:deep(.fc-toolbar-title) {
  font-size: 1.5rem;
  font-weight: 700;
  color: hsl(var(--foreground));
}

:deep(.fc-button) {
  background: hsl(var(--background));
  border: 1px solid hsl(var(--border));
  color: hsl(var(--foreground));
  border-radius: 0.5rem;
  padding: 0.5rem 1rem;
  font-weight: 500;
  transition: all 0.2s ease;
}

:deep(.fc-button:hover) {
  background: hsl(var(--muted));
  border-color: hsl(var(--border));
  color: hsl(var(--foreground));
}

:deep(.fc-button-active) {
  background: hsl(var(--primary)) !important;
  border-color: hsl(var(--primary)) !important;
  color: hsl(var(--primary-foreground)) !important;
}

:deep(.fc-daygrid-day) {
  transition: background-color 0.2s ease;
}

:deep(.fc-daygrid-day:hover) {
  background-color: hsl(var(--muted) / 0.3);
}

:deep(.fc-day-today) {
  background-color: hsl(var(--primary) / 0.05) !important;
}

:deep(.fc-day-today .fc-daygrid-day-number) {
  background: hsl(var(--primary));
  color: hsl(var(--primary-foreground));
  border-radius: 50%;
  width: 2rem;
  height: 2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
}

:deep(.fc-event) {
  border: none !important;
  border-radius: 0.375rem !important;
  padding: 0.25rem 0.5rem !important;
  font-size: 0.875rem !important;
  font-weight: 500 !important;
  cursor: pointer !important;
  transition: all 0.2s ease !important;
  box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05) !important;
}

:deep(.fc-event:hover) {
  transform: translateY(-1px) !important;
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -1px rgb(0 0 0 / 0.06) !important;
}

:deep(.fc-daygrid-event-dot) {
  display: none !important;
}

:deep(.fc-event-title) {
  font-weight: 500 !important;
}

:deep(.fc-more-link) {
  color: hsl(var(--primary)) !important;
  font-weight: 500 !important;
  text-decoration: none !important;
  padding: 0.125rem 0.25rem !important;
  border-radius: 0.25rem !important;
  transition: background-color 0.2s ease !important;
}

:deep(.fc-more-link:hover) {
  background-color: hsl(var(--muted)) !important;
}

:deep(.fc-col-header-cell) {
  background: hsl(var(--muted) / 0.3);
  border: 1px solid hsl(var(--border));
  font-weight: 600;
  color: hsl(var(--muted-foreground));
  padding: 0.75rem 0.5rem;
}

:deep(.fc-scrollgrid) {
  border: 1px solid hsl(var(--border));
  border-radius: 0.75rem;
  overflow: hidden;
}

/* Animation pour les nouveaux événements */
@keyframes eventAppear {
  from {
    opacity: 0;
    transform: scale(0.9);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

:deep(.fc-event) {
  animation: eventAppear 0.3s ease-out;
}
</style>