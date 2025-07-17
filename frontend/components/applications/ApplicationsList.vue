<template>
  <div class="space-y-4">
    <!-- Section des filtres -->
    <div class="flex flex-col gap-4 rounded-lg border p-4 bg-muted/50">
      <!-- Barre de recherche -->
      <div class="flex-1">
        <Input 
          v-model="search" 
          placeholder="Rechercher une candidature..." 
          class="w-full"
        >
          <template #prefix>
            <Search class="h-4 w-4 text-muted-foreground" />
          </template>
        </Input>
      </div>

      <!-- Filtres -->
      <div class="flex justify-between items-center gap-6">
        <!-- Statut -->
        <div class="space-y-2 flex-1">
          <Label class="text-sm text-muted-foreground font-medium">Statut</Label>
          <div class="flex flex-wrap gap-4">
            <div v-for="status in statuses" :key="status.value">
              <div class="flex items-center space-x-2">
                <Checkbox
                  :id="status.value"
                  :model-value="selectedStatuses.includes(status.value)"
                  @update:model-value="toggleStatus(status.value)"
                />
                <Label :for="status.value" class="flex items-center gap-2 text-sm font-normal cursor-pointer">
                  <Badge :variant="getStatusVariant(status.value)">{{ status.label }}</Badge>
                </Label>
              </div>
            </div>
          </div>
        </div>

        <!-- Tri -->
        <div class="flex items-center gap-4 min-w-[200px]">
          <Label class="text-sm text-muted-foreground font-medium">Trier par</Label>
          <div class="flex gap-2">
            <Select v-model="sortBy">
              <SelectTrigger class="w-[140px]">
                <SelectValue placeholder="Trier par..." />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="date">Date de candidature</SelectItem>
                <SelectItem value="company">Entreprise</SelectItem>
                <SelectItem value="position">Poste</SelectItem>
              </SelectContent>
            </Select>
            <Button
              variant="ghost"
              size="icon"
              class="h-10 w-10"
              @click="sortOrder = sortOrder === 'asc' ? 'desc' : 'asc'"
            >
              <ArrowUpDown 
                :class="[
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
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Poste</TableHead>
            <TableHead>Entreprise</TableHead>
            <TableHead>Date de candidature</TableHead>
            <TableHead>Entretien</TableHead>
            <TableHead>Statut</TableHead>
            <TableHead class="text-right">Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="application in filteredApplications" :key="application.id">
            <TableCell>{{ application.position }}</TableCell>
            <TableCell>{{ application.company }}</TableCell>
            <TableCell>{{ formatDate(application.applicationDate) }}</TableCell>
            <TableCell>{{ application.interviewDate ? formatDate(application.interviewDate) : '-' }}</TableCell>
            <TableCell>
              <Badge :variant="getStatusVariant(application.status)">
                {{ getStatusLabel(application.status) }}
              </Badge>
            </TableCell>
            <TableCell class="text-right">
              <DropdownMenu>
                <DropdownMenuTrigger as-child>
                  <Button variant="ghost" size="icon">
                    <MoreVertical class="h-4 w-4" />
                    <span class="sr-only">Ouvrir le menu</span>
                  </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                  <DropdownMenuItem @click="$emit('edit', application)">
                    <Pencil class="mr-2 h-4 w-4" />
                    Modifier
                  </DropdownMenuItem>
                  <DropdownMenuItem @click="updateStatus(application, 'followed_up')">
                    <Bell class="mr-2 h-4 w-4" />
                    Marquer comme relancé
                  </DropdownMenuItem>
                  <DropdownMenuItem @click="updateStatus(application, 'interview')">
                    <CalendarDays class="mr-2 h-4 w-4" />
                    Planifier un entretien
                  </DropdownMenuItem>
                  <DropdownMenuSeparator />
                  <DropdownMenuItem @click="deleteApplication(application)" class="text-red-600">
                    <Trash class="mr-2 h-4 w-4" />
                    Supprimer
                  </DropdownMenuItem>
                </DropdownMenuContent>
              </DropdownMenu>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Search, MoreVertical, Pencil, Bell, CalendarDays, Trash, ArrowUpDown } from 'lucide-vue-next'
import { formatDate } from '~/lib/utils'
import { Input } from '~/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '~/components/ui/select'
import { Button } from '~/components/ui/button'
import { Badge } from '~/components/ui/badge'
import { Label } from '~/components/ui/label'
import { Checkbox } from '~/components/ui/checkbox'
import {
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
} from '~/components/ui/dropdown-menu'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '~/components/ui/table'
import type { Application, ApplicationStatus } from '~/types/application'

const search = ref('')
const selectedStatuses = ref<string[]>([])
const sortBy = ref('date')
const sortOrder = ref<'asc' | 'desc'>('desc')

const statuses = [
  { value: 'pending', label: 'En attente' },
  { value: 'followed_up', label: 'Relancé' },
  { value: 'interview', label: 'Entretien' },
  { value: 'rejected', label: 'Refusé' },
  { value: 'accepted', label: 'Accepté' }
]

const props = defineProps<{
  applications: Application[]
  loading?: boolean
}>()

const emit = defineEmits<{
  (e: 'edit', application: Application): void
  (e: 'delete', application: Application): void
  (e: 'update-status', application: Application, status: ApplicationStatus): void
  (e: 'refresh'): void
}>()

const toggleStatus = (status: string) => {
  const index = selectedStatuses.value.indexOf(status)
  if (index === -1) {
    selectedStatuses.value.push(status)
  } else {
    selectedStatuses.value.splice(index, 1)
  }
}

const filteredApplications = computed(() => {
  let filtered = props.applications.filter(app => {
    const matchesSearch = !search.value || 
      app.position.toLowerCase().includes(search.value.toLowerCase()) ||
      app.company.toLowerCase().includes(search.value.toLowerCase())
    
    const matchesStatus = selectedStatuses.value.length === 0 || 
      selectedStatuses.value.includes(app.status)
    
    return matchesSearch && matchesStatus
  })

  // Tri
  filtered.sort((a, b) => {
    let comparison = 0
    
    switch (sortBy.value) {
      case 'date':
        comparison = new Date(b.applicationDate).getTime() - new Date(a.applicationDate).getTime()
        break
      case 'company':
        comparison = a.company.localeCompare(b.company)
        break
      case 'position':
        comparison = a.position.localeCompare(b.position)
        break
    }

    return sortOrder.value === 'asc' ? comparison : -comparison
  })

  return filtered
})

const getStatusLabel = (status: string) => {
  const labels: Record<string, string> = {
    pending: 'En attente',
    followed_up: 'Relancé',
    interview: 'Entretien',
    rejected: 'Refusé',
    accepted: 'Accepté'
  }
  return labels[status] || status
}

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

const updateStatus = (application: Application, newStatus: ApplicationStatus) => {
  emit('update-status', application, newStatus)
}

const deleteApplication = (application: Application) => {
  emit('delete', application)
}
</script>
