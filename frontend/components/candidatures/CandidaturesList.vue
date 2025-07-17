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
            <TableHead class="text-center">Actions rapides</TableHead>
            <TableHead class="text-right">Plus</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="candidature in filteredCandidatures" :key="candidature.id">
            <TableCell>{{ candidature.titrePoste }}</TableCell>
            <TableCell>{{ candidature.entreprise }}</TableCell>
            <TableCell>{{ formatDate(candidature.dateDepot) }}</TableCell>
            <TableCell>{{ candidature.dateEntretien ? formatDate(candidature.dateEntretien) : '-' }}</TableCell>
            <TableCell>
              <Badge :variant="getStatusVariant(candidature.statut)">
                {{ getStatusLabel(candidature.statut) }}
              </Badge>
            </TableCell>
            <TableCell class="text-center">
              <div class="flex items-center justify-center gap-1">
                <!-- Bouton Relancer (visible seulement si pas déjà relancé) -->
                <Button 
                  v-if="candidature.statut !== 'relance'" 
                  variant="ghost" 
                  size="sm"
                  @click="updateStatus(candidature, 'relance')"
                  title="Marquer comme relancé"
                  class="h-8 w-8 p-0"
                >
                  <Bell class="h-4 w-4 text-orange-600" />
                </Button>
                
                <!-- Bouton Entretien (visible seulement si pas déjà en entretien/accepté/refusé) -->
                <Button 
                  v-if="!['entretien', 'accepte', 'refuse'].includes(candidature.statut)" 
                  variant="ghost" 
                  size="sm"
                  @click="openInterviewDialog(candidature)"
                  title="Planifier un entretien"
                  class="h-8 w-8 p-0"
                >
                  <CalendarDays class="h-4 w-4 text-blue-600" />
                </Button>
                
                <!-- Bouton Accepté (visible seulement si en entretien) -->
                <Button 
                  v-if="candidature.statut === 'entretien'" 
                  variant="ghost" 
                  size="sm"
                  @click="updateStatus(candidature, 'accepte')"
                  title="Marquer comme accepté"
                  class="h-8 w-8 p-0"
                >
                  <UserCheck class="h-4 w-4 text-green-600" />
                </Button>
                
                <!-- Bouton Refusé (visible seulement si pas déjà refusé) -->
                <Button 
                  v-if="candidature.statut !== 'refuse'" 
                  variant="ghost" 
                  size="sm"
                  @click="updateStatus(candidature, 'refuse')"
                  title="Marquer comme refusé"
                  class="h-8 w-8 p-0"
                >
                  <UserX class="h-4 w-4 text-red-600" />
                </Button>
              </div>
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
                  <DropdownMenuItem @click="$emit('edit', candidature)">
                    <Pencil class="mr-2 h-4 w-4" />
                    Modifier
                  </DropdownMenuItem>
                  <DropdownMenuSeparator />
                  <DropdownMenuItem @click="deleteCandidature(candidature)" class="text-red-600">
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

    <!-- Dialog pour planifier un entretien -->
    <InterviewScheduleDialog
      :open="isInterviewDialogOpen"
      :candidature="selectedCandidatureForInterview"
      @update:open="isInterviewDialogOpen = $event"
      @schedule="handleScheduleInterview"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Search, MoreVertical, Pencil, Bell, CalendarDays, Trash, ArrowUpDown, UserCheck, UserX } from 'lucide-vue-next'
import { formatDate } from '~/lib/utils'
import { Input } from '~/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '~/components/ui/select'
import { Button } from '~/components/ui/button'
import { Badge } from '~/components/ui/badge'
import { Label } from '~/components/ui/label'
import { Checkbox } from '~/components/ui/checkbox'
import InterviewScheduleDialog from '~/components/candidatures/InterviewScheduleDialog.vue'
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
import type { Candidature, CandidatureStatus } from '~/types/candidature'

const search = ref('')
const selectedStatuses = ref<string[]>([])
const sortBy = ref('date')
const sortOrder = ref<'asc' | 'desc'>('desc')

// Variables pour le dialog d'entretien
const isInterviewDialogOpen = ref(false)
const selectedCandidatureForInterview = ref<Candidature | null>(null)

const statuses = [
  { value: 'a_faire', label: 'À faire' },
  { value: 'en_attente', label: 'En attente' },
  { value: 'relance', label: 'Relancé' },
  { value: 'entretien', label: 'Entretien' },
  { value: 'refuse', label: 'Refusé' },
  { value: 'accepte', label: 'Accepté' }
]

const props = defineProps<{
  candidatures: Candidature[]
  loading?: boolean
}>()

const emit = defineEmits<{
  (e: 'edit', candidature: Candidature): void
  (e: 'delete', candidature: Candidature): void
  (e: 'update-status', candidature: Candidature, status: CandidatureStatus): void
  (e: 'schedule-interview', candidature: Candidature, dateEntretien: string, notes: string): void
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

const filteredCandidatures = computed(() => {
  let filtered = props.candidatures.filter(candidature => {
    const matchesSearch = !search.value || 
      candidature.titrePoste.toLowerCase().includes(search.value.toLowerCase()) ||
      candidature.entreprise.toLowerCase().includes(search.value.toLowerCase())
    
    const matchesStatus = selectedStatuses.value.length === 0 || 
      selectedStatuses.value.includes(candidature.statut)
    
    return matchesSearch && matchesStatus
  })

  // Tri
  filtered.sort((a, b) => {
    let comparison = 0
    
    switch (sortBy.value) {
      case 'date':
        comparison = new Date(b.dateDepot).getTime() - new Date(a.dateDepot).getTime()
        break
      case 'company':
        comparison = a.entreprise.localeCompare(b.entreprise)
        break
      case 'position':
        comparison = a.titrePoste.localeCompare(b.titrePoste)
        break
    }

    return sortOrder.value === 'asc' ? comparison : -comparison
  })

  return filtered
})

const getStatusLabel = (status: string) => {
  const labels: Record<string, string> = {
    a_faire: 'À faire',
    en_attente: 'En attente',
    relance: 'Relancé',
    entretien: 'Entretien',
    refuse: 'Refusé',
    accepte: 'Accepté'
  }
  return labels[status] || status
}

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

const updateStatus = (candidature: Candidature, newStatus: CandidatureStatus) => {
  emit('update-status', candidature, newStatus)
}

const deleteCandidature = (candidature: Candidature) => {
  emit('delete', candidature)
}

// Fonctions pour gérer le dialog d'entretien
const openInterviewDialog = (candidature: Candidature) => {
  selectedCandidatureForInterview.value = candidature
  isInterviewDialogOpen.value = true
}

const handleScheduleInterview = (data: { candidature: Candidature, dateEntretien: string, notes: string }) => {
  // Émettre un événement spécifique pour la planification d'entretien
  emit('schedule-interview', data.candidature, data.dateEntretien, data.notes)
}
</script>
