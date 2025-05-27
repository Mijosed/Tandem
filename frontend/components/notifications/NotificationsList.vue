<template>
  <div class="space-y-4">
    <!-- Section des filtres -->
    <div class="flex flex-col gap-4 rounded-lg border p-4 bg-muted/50">
      <!-- Barre de recherche avec bouton d'ajout -->
      <div class="flex items-center gap-4">
        <div class="flex-1">
          <Input 
            v-model="search" 
            placeholder="Rechercher dans les notifications..." 
            class="w-full"
          >
            <template #prefix>
              <Search class="h-4 w-4 text-muted-foreground" />
            </template>
          </Input>
        </div>
        <NotificationForm @create="createNotification" />
      </div>
      
      <!-- Filtres -->
      <div class="grid grid-cols-3 gap-6">
        <!-- Types de notifications -->
        <div class="space-y-3">
          <Label class="text-sm text-muted-foreground font-medium">Type de notification</Label>
          <div class="space-y-2">
            <div v-for="type in notificationTypes" :key="type.value">
              <div class="flex items-center space-x-2">
                <Checkbox
                  :id="type.value"
                  :model-value="selectedTypes.includes(type.value)"
                  @update:model-value="toggleType(type.value)"
                />
                <div class="flex items-center gap-2">
                  <component :is="type.icon" class="h-4 w-4" :class=" [
                    type.value === 'reminder' ? 'text-blue-500' : '',
                    type.value === 'interview' ? 'text-green-500' : '',
                    type.value === 'info' ? 'text-yellow-500' : ''
                  ]" />
                  <Label :for="type.value" class="text-sm font-normal cursor-pointer">
                    {{ type.label }}
                  </Label>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Statut de lecture -->
        <div class="space-y-3">
          <Label class="text-sm text-muted-foreground font-medium">Statut</Label>
          <RadioGroup :model-value="selectedStatus" @update:model-value="setReadStatus" class="space-y-2">
            <div v-for="status in readStatuses" :key="status.value">
              <div class="flex items-center space-x-2">
                <RadioGroupItem :value="status.value" :id="status.value || 'all'" />
                <Label :for="status.value || 'all'" class="flex items-center gap-2 text-sm font-normal cursor-pointer">
                  <span>{{ status.label }}</span>
                </Label>
              </div>
            </div>
          </RadioGroup>
        </div>

        <!-- Options de tri -->
        <div class="space-y-3">
          <Label class="text-sm text-muted-foreground font-medium">Trier par</Label>
          <div class="flex gap-2">
            <Select v-model="sortBy">
              <SelectTrigger class="w-[140px]">
                <SelectValue placeholder="Trier par..." />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="date">Date</SelectItem>
                <SelectItem value="type">Type</SelectItem>
                <SelectItem value="priority">Priorité</SelectItem>
              </SelectContent>
            </Select>
            <Button
              variant="ghost"
              size="icon"
              class="h-10 w-10"
              @click="sortOrder = sortOrder === 'asc' ? 'desc' : 'asc'"
            >
              <ArrowUpDown 
                :class=" [
                  'h-4 w-4',
                  sortOrder === 'desc' ? 'rotate-180' : ''
                ]"
              />
            </Button>
          </div>
        </div>
      </div>
    </div>

    <!-- Liste des notifications -->
    <div class="rounded-md border">
      <div class="space-y-1 p-2">
        <TransitionGroup
          enter-active-class="transition ease-out duration-100"
          enter-from-class="transform opacity-0"
          enter-to-class="transform opacity-100"
          leave-active-class="transition ease-in duration-75"
          leave-from-class="transform opacity-100"
          leave-to-class="transform opacity-0"
        >
          <div
            v-for="notification in filteredNotifications"
            :key="notification.id"
            class="flex items-center justify-between space-x-4 rounded-lg border p-4 hover:bg-muted/50"
            :class=" [
              !notification.read ? 'bg-muted/30' : '',
              'transition-colors duration-200'
            ]"
          >
            <div class="flex items-center space-x-4">
              <component
                :is="getNotificationIcon(notification.type)"
                class="h-5 w-5"
                :class=" [
                  notification.type === 'reminder' ? 'text-blue-500' : '',
                  notification.type === 'interview' ? 'text-green-500' : '',
                  notification.type === 'info' ? 'text-yellow-500' : ''
                ]"
              />
              <div class="space-y-1">
                <p class="text-sm font-medium leading-none">
                  {{ notification.title }}
                </p>
                <p class="text-sm text-muted-foreground">
                  {{ notification.message }}
                </p>
                <p class="text-xs text-muted-foreground">
                  {{ formatDate(notification.date) }}
                </p>
              </div>
            </div>
            <Button
              v-if="!notification.read"
              variant="ghost"
              size="sm"
              @click="markAsRead(notification.id)"
            >
              <Check class="mr-2 h-4 w-4" />
              Marquer comme lu
            </Button>
          </div>
        </TransitionGroup>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { 
  Bell, 
  Calendar,
  Info,
  Check,
  CheckCircle2,
  Circle,
  Search,
  ArrowUpDown
} from 'lucide-vue-next'
import { Input } from '~/components/ui/input'
import { Label } from '~/components/ui/label'
import { Button } from '~/components/ui/button'
import { Separator } from '~/components/ui/separator'
import { Checkbox } from '~/components/ui/checkbox'
import { RadioGroup, RadioGroupItem } from '~/components/ui/radio-group'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '~/components/ui/select'
import NotificationForm from './NotificationForm.vue'

interface Notification {
  id: string
  type: 'reminder' | 'interview' | 'info'
  title: string
  message: string
  date: Date
  read: boolean
}

const props = defineProps<{
  notifications: Notification[]
}>()

const emit = defineEmits<{
  'mark-as-read': [id: string]
  'create': [notification: Omit<Notification, 'id' | 'date' | 'read'>]
}>()

// État des filtres
const search = ref('')
const selectedTypes = ref<string[]>([])
const selectedStatus = ref('')
const sortBy = ref('date')
const sortOrder = ref<'asc' | 'desc'>('desc')

// Options des filtres
const notificationTypes = [
  { value: 'reminder', label: 'Rappels', icon: Bell },
  { value: 'interview', label: 'Entretiens', icon: Calendar },
  { value: 'info', label: 'Informations', icon: Info }
]

const readStatuses = [
  { value: '', label: 'Tous', icon: Circle },
  { value: 'unread', label: 'Non lus', icon: Circle },
  { value: 'read', label: 'Lus', icon: CheckCircle2 }
]

// Méthodes
const toggleType = (type: string) => {
  const index = selectedTypes.value.indexOf(type)
  if (index === -1) {
    selectedTypes.value.push(type)
  } else {
    selectedTypes.value.splice(index, 1)
  }
}

const setReadStatus = (status: string) => {
  selectedStatus.value = status
}

const markAsRead = (id: string) => {
  emit('mark-as-read', id)
}

const createNotification = (data: Omit<Notification, 'id' | 'date' | 'read'>) => {
  emit('create', data)
}

const getNotificationIcon = (type: Notification['type']) => {
  switch (type) {
    case 'reminder':
      return Bell
    case 'interview':
      return Calendar
    case 'info':
      return Info
    default:
      return Bell
  }
}

const formatDate = (date: Date) => {
  return new Intl.DateTimeFormat('fr-FR', {
    dateStyle: 'long',
    timeStyle: 'short'
  }).format(new Date(date))
}

// Computed
const filteredNotifications = computed(() => {
  let filtered = props.notifications

  // Filtre par recherche
  if (search.value) {
    const searchLower = search.value.toLowerCase()
    filtered = filtered.filter(notification =>
      notification.title.toLowerCase().includes(searchLower) ||
      notification.message.toLowerCase().includes(searchLower)
    )
  }

  // Filtre par type
  if (selectedTypes.value.length > 0) {
    filtered = filtered.filter(notification =>
      selectedTypes.value.includes(notification.type)
    )
  }

  // Filtre par statut de lecture
  if (selectedStatus.value) {
    filtered = filtered.filter(notification =>
      selectedStatus.value === 'read' ? notification.read : !notification.read
    )
  }

  // Tri
  filtered.sort((a, b) => {
    let comparison = 0
    
    switch (sortBy.value) {
      case 'date':
        comparison = new Date(b.date).getTime() - new Date(a.date).getTime()
        break
      case 'type':
        comparison = a.type.localeCompare(b.type)
        break
      case 'priority':
        // Ordre de priorité : interview > reminder > info
        const priority = { interview: 0, reminder: 1, info: 2 }
        comparison = priority[a.type] - priority[b.type]
        break
    }

    return sortOrder.value === 'asc' ? comparison : -comparison
  })

  return filtered
})
</script>
