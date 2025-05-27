<script>
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'
import { SidebarTrigger } from '~/components/ui/sidebar'
import { Button } from '~/components/ui/button'
import { Plus } from 'lucide-vue-next'
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

definePageMeta({
  layout: 'dashboard'
})

export default {
  components: {
    FullCalendar,
    SidebarTrigger,
    Button,
    Plus,
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    Input,
    Label
  },
  data: function() {
    return {
      isDialogOpen: false,
      newEvent: {
        title: '',
        date: '',
        time: ''
      },
      events: [
        { title: 'Meeting', start: new Date() }
      ],
      calendarOptions: {
        plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        weekends: true,
        height: 'auto',
        aspectRatio: 1.35,
        selectable: true,
        selectMirror: true,
        dayMaxEvents: true,
        events: this.events,
        eventColor: '#3b82f6',
        eventTextColor: '#ffffff',
        eventBorderColor: '#2563eb',
        select: this.handleDateSelect,
        eventClick: this.handleEventClick,
        editable: true,
        droppable: true
      }
    }
  },
  methods: {
    openNewEventDialog() {
      this.newEvent = {
        title: '',
        date: '',
        time: ''
      }
      this.isDialogOpen = true
    },
    addEvent() {
      if (!this.newEvent.title || !this.newEvent.date) {
        return
      }

      let eventDate = this.newEvent.date
      if (this.newEvent.time) {
        eventDate += 'T' + this.newEvent.time
      }

      const event = {
        title: this.newEvent.title,
        start: eventDate,
        id: Date.now().toString()
      }

      this.events.push(event)
      this.calendarOptions.events = [...this.events]
      this.isDialogOpen = false
    },
    handleDateSelect(selectInfo) {
      // Pré-remplir la date sélectionnée
      this.newEvent.date = selectInfo.startStr.split('T')[0]
      this.openNewEventDialog()
    },
    handleEventClick(clickInfo) {
      alert('Événement: ' + clickInfo.event.title)
    }
  }
}
</script>

<template>
  <div>
    <header class="flex h-16 shrink-0 items-center gap-2 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12">
      <div class="flex items-center gap-2 px-4">
        <SidebarTrigger class="-ml-1" />
        <h1 class="text-2xl font-bold">Planning</h1>
      </div>
    </header>

    <div class="container py-6 px-4">
      <div class="flex justify-between items-center mb-8">
        <p class="text-muted-foreground">Gérez votre planning et vos rendez-vous</p>
        <Button @click="openNewEventDialog">
          <Plus class="mr-2 h-4 w-4" />
          Nouvel événement
        </Button>
      </div>

      <div class="max-w-4xl mx-auto">
        <FullCalendar :options='calendarOptions' class="modern-calendar" />
      </div>

      <Dialog v-model:open="isDialogOpen">
        <DialogContent class="sm:max-w-md">
          <DialogHeader>
            <DialogTitle>Nouvel événement</DialogTitle>
            <DialogDescription>
              Créez un nouvel événement dans votre planning
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
              <Label for="event-date">Date</Label>
              <Input
                id="event-date"
                type="date"
                v-model="newEvent.date"
              />
            </div>
            <div class="grid gap-2">
              <Label for="event-time">Heure (optionnel)</Label>
              <Input
                id="event-time"
                type="time"
                v-model="newEvent.time"
              />
            </div>
          </div>
          <DialogFooter>
            <Button variant="outline" @click="isDialogOpen = false">
              Annuler
            </Button>
            <Button @click="addEvent">
              Ajouter
            </Button>
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