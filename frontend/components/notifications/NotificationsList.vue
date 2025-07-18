<template>
  <div class="space-y-4">
    <div class="flex flex-col gap-4 rounded-lg border p-4 bg-muted/50">
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

      </div>
      
      <div class="grid grid-cols-3 gap-6">
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
                    type.value === 'info' ? 'text-yellow-500' : '',
                    type.value === 'warning' ? 'text-orange-500' : '',
                    type.value === 'success' ? 'text-green-600' : ''
                  ]" />
                  <Label :for="type.value" class="text-sm font-normal cursor-pointer">
                    {{ type.label }}
                  </Label>
                </div>
              </div>
            </div>
          </div>
        </div>

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
              !notification.isRead ? 'bg-muted/30' : '',
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
                  notification.type === 'info' ? 'text-yellow-500' : '',
                  notification.type === 'warning' ? 'text-orange-500' : '',
                  notification.type === 'success' ? 'text-green-600' : ''
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
                  {{ formatDate(notification.createdAt) }}
                </p>
              </div>
            </div>
            <Button
              v-if="!notification.isRead"
              variant="ghost"
              size="sm"
              @click="markAsRead(notification)"
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

import { Checkbox } from '~/components/ui/checkbox'
import { RadioGroup, RadioGroupItem } from '~/components/ui/radio-group'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '~/components/ui/select'
import type { Notification } from '~/types/notification'

const props = defineProps<{
  notifications: Notification[]
}>()

const emit = defineEmits<{
  'update': [notification: Notification]
}>()

const search = ref('')
const selectedTypes = ref<string[]>([])
const selectedStatus = ref('')
const sortBy = ref('date')
const sortOrder = ref<'asc' | 'desc'>('desc')

const notificationTypes = [
  { value: 'reminder', label: 'Rappels', icon: Bell },
  { value: 'interview', label: 'Entretiens', icon: Calendar },
  { value: 'info', label: 'Informations', icon: Info },
  { value: 'warning', label: 'Avertissements', icon: Bell },
  { value: 'success', label: 'Succès', icon: CheckCircle2 }
]

const readStatuses = [
  { value: '', label: 'Tous', icon: Circle },
  { value: 'unread', label: 'Non lus', icon: Circle },
  { value: 'read', label: 'Lus', icon: CheckCircle2 }
]

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

const markAsRead = (notification: Notification) => {
  emit('update', notification)
}

const getNotificationIcon = (type: Notification['type']) => {
  switch (type) {
    case 'reminder':
      return Bell
    case 'interview':
      return Calendar
    case 'info':
      return Info
    case 'warning':
      return Bell
    case 'success':
      return CheckCircle2
    default:
      return Bell
  }
}

const formatDate = (dateString: string) => {
  return new Intl.DateTimeFormat('fr-FR', {
    dateStyle: 'long',
    timeStyle: 'short'
  }).format(new Date(dateString))
}

const filteredNotifications = computed(() => {
  let filtered = props.notifications

  if (search.value) {
    const searchLower = search.value.toLowerCase()
    filtered = filtered.filter(notification =>
      notification.title.toLowerCase().includes(searchLower) ||
      notification.message.toLowerCase().includes(searchLower)
    )
  }

  if (selectedTypes.value.length > 0) {
    filtered = filtered.filter(notification =>
      selectedTypes.value.includes(notification.type)
    )
  }

  if (selectedStatus.value) {
    filtered = filtered.filter(notification =>
      selectedStatus.value === 'read' ? notification.isRead : !notification.isRead
    )
  }

  filtered.sort((a, b) => {
    let comparison = 0
    
    switch (sortBy.value) {
      case 'date':
        comparison = new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime()
        break
      case 'type':
        comparison = a.type.localeCompare(b.type)
        break
      case 'priority':
        const priority = { interview: 0, reminder: 1, info: 2 }
        comparison = priority[a.type] - priority[b.type]
        break
    }

    return sortOrder.value === 'asc' ? comparison : -comparison
  })

  return filtered
})
</script>
