<template>
  <div class="space-y-4">
    <div class="flex gap-2 mb-4">
      <Input v-model="search" placeholder="Rechercher une candidature..." class="max-w-sm">
        <template #prefix>
          <Search class="h-4 w-4" />
        </template>
      </Input>
      <Select v-model="statusFilter">
        <option value="">Tous les statuts</option>
        <option value="pending">En attente</option>
        <option value="followed_up">Relancé</option>
        <option value="interview">Entretien</option>
        <option value="rejected">Refusé</option>
        <option value="accepted">Accepté</option>
      </Select>
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
import { Search, MoreVertical, Pencil, Bell, CalendarDays, Trash } from 'lucide-vue-next'
import { formatDate } from '~/lib/utils'
import { Input } from '~/components/ui/input'
import { Select } from '~/components/ui/select'
import { Button } from '~/components/ui/button'
import { Badge } from '~/components/ui/badge'
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

const search = ref('')
const statusFilter = ref('')

const props = defineProps<{
  applications: {
    id: number
    position: string
    company: string
    applicationDate: string
    interviewDate?: string
    status: string
    notes?: string
  }[]
}>()

const emit = defineEmits<{
  (e: 'edit', application: typeof props.applications[0]): void
  (e: 'refresh'): void
}>()

const filteredApplications = computed(() => {
  return props.applications.filter(app => {
    const matchesSearch = !search.value || 
      app.position.toLowerCase().includes(search.value.toLowerCase()) ||
      app.company.toLowerCase().includes(search.value.toLowerCase())
    
    const matchesStatus = !statusFilter.value || app.status === statusFilter.value
    
    return matchesSearch && matchesStatus
  })
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

const updateStatus = async (application: typeof props.applications[0], newStatus: string) => {
  // TODO: Appel API pour mettre à jour le statut
  emit('refresh')
}

const deleteApplication = async (application: typeof props.applications[0]) => {
  if (confirm('Êtes-vous sûr de vouloir supprimer cette candidature ?')) {
    // TODO: Appel API pour supprimer
    emit('refresh')
  }
}
</script>
