<template>
  <div class="space-y-4">
    <div class="flex flex-col gap-4 rounded-lg border p-4 bg-muted/50">
      <div class="flex items-center gap-4">
        <div class="flex-1">
          <Input 
            v-model="search" 
            placeholder="Rechercher un utilisateur..." 
            class="w-full"
          >
            <template #prefix>
              <Search class="h-4 w-4 text-muted-foreground" />
            </template>
          </Input>
        </div>
        <Button @click="openUserDialog()">
          <UserPlus class="h-4 w-4 mr-2" />
          Nouvel utilisateur
        </Button>
      </div>

      <div class="flex justify-between items-center gap-6">
        <div class="space-y-2 flex-1">
          <Label class="text-sm text-muted-foreground font-medium">Rôle</Label>
          <div class="flex flex-wrap gap-4">
            <div v-for="role in roles" :key="role.value">
              <div class="flex items-center space-x-2">
                <Checkbox
                  :id="role.value"
                  :model-value="selectedRoles.includes(role.value)"
                  @update:model-value="toggleRole(role.value)"
                />
                <Label :for="role.value" class="flex items-center gap-2 text-sm font-normal cursor-pointer">
                  <Badge :variant="getRoleBadgeVariant(role.value)">{{ role.label }}</Badge>
                </Label>
              </div>
            </div>
          </div>
        </div>

        <div class="flex items-center gap-4 min-w-[200px]">
          <Label class="text-sm text-muted-foreground font-medium">Trier par</Label>
          <div class="flex gap-2">
            <Select v-model="sortBy">
              <SelectTrigger class="w-[140px]">
                <SelectValue placeholder="Trier par..." />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="name">Nom</SelectItem>
                <SelectItem value="email">Email</SelectItem>
                <SelectItem value="createdAt">Date d'inscription</SelectItem>
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
            <TableHead>Nom</TableHead>
            <TableHead>Email</TableHead>
            <TableHead>Rôle</TableHead>
            <TableHead>Date d'inscription</TableHead>
            <TableHead>Statut</TableHead>
            <TableHead class="text-right">Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="user in filteredUsers" :key="user.id">
            <TableCell class="font-medium">{{ user.firstName }} {{ user.lastName }}</TableCell>
            <TableCell>{{ user.email }}</TableCell>
            <TableCell>
              <div class="flex flex-wrap gap-1">
                <Badge 
                  v-for="role in user.roles" 
                  :key="role" 
                  :variant="getRoleBadgeVariant(role)"
                  class="text-xs"
                >
                  {{ getRoleLabel(role) }}
                </Badge>
              </div>
            </TableCell>
            <TableCell>{{ formatDate(user.createdAt) }}</TableCell>
            <TableCell>
              <Badge :variant="user.isActive ? 'default' : 'destructive'">
                {{ user.isActive ? 'Actif' : 'Inactif' }}
              </Badge>
            </TableCell>
            <TableCell class="text-right">
              <DropdownMenu>
                <DropdownMenuTrigger asChild>
                  <Button variant="ghost" size="icon">
                    <MoreVertical class="h-4 w-4" />
                    <span class="sr-only">Ouvrir le menu</span>
                  </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                  <DropdownMenuItem @click="openUserDialog(user)">
                    <Pencil class="mr-2 h-4 w-4" />
                    Modifier
                  </DropdownMenuItem>
                  <DropdownMenuItem @click="toggleUserStatus(user)">
                    <component :is="user.isActive ? Ban : CheckCircle" class="mr-2 h-4 w-4" />
                    {{ user.isActive ? 'Désactiver' : 'Activer' }}
                  </DropdownMenuItem>
                  <DropdownMenuSeparator />
                  <DropdownMenuItem @click="deleteUser(user)" class="text-red-600">
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

    <UserDialog 
      v-model:open="isUserDialogOpen"
      :user="selectedUser"
      @save="handleSaveUser"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useUsers } from '~/composables/useUsers'
import { 
  Search, 
  UserPlus, 
  MoreVertical, 
  Pencil, 
  Ban,
  CheckCircle,
  Trash,
  ArrowUpDown
} from 'lucide-vue-next'
import { formatDate } from '~/lib/utils'
import { Input } from '~/components/ui/input'
import { Label } from '~/components/ui/label'
import { Button } from '~/components/ui/button'
import { Badge } from '~/components/ui/badge'
import { Checkbox } from '~/components/ui/checkbox'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '~/components/ui/select'
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
import UserDialog from './UserDialog.vue'

import type { User, UserFormData } from '~/composables/useUsers'

const props = defineProps<{
  users: User[]
}>()

const emit = defineEmits<{
  (e: 'refresh'): void
}>()

const { createUser, updateUser, deleteUser: removeUser, toggleUserStatus: toggleStatus } = useUsers()

const search = ref('')
const selectedRoles = ref<string[]>([])
const sortBy = ref('name')
const sortOrder = ref<'asc' | 'desc'>('asc')
const isUserDialogOpen = ref(false)
const selectedUser = ref<User | null>(null)

const roles = [
  { value: 'ROLE_USER', label: 'Utilisateur' },
  { value: 'ROLE_PREMIUM', label: 'Premium' },
  { value: 'ROLE_ADMIN', label: 'Admin' }
]

const toggleRole = (role: string) => {
  const index = selectedRoles.value.indexOf(role)
  if (index === -1) {
    selectedRoles.value.push(role)
  } else {
    selectedRoles.value.splice(index, 1)
  }
}

const getRoleLabel = (role: string) => {
  const roleObj = roles.find(r => r.value === role)
  return roleObj?.label || role
}

const getRoleBadgeVariant = (role: string): 'default' | 'secondary' | 'outline' => {
  switch (role) {
    case 'ROLE_ADMIN':
      return 'default'
    case 'ROLE_PREMIUM':
      return 'secondary'
    default:
      return 'outline'
  }
}

const openUserDialog = (user: User | null = null) => {
  selectedUser.value = user
  isUserDialogOpen.value = true
}

const handleSaveUser = async (userData: UserFormData) => {
  if (selectedUser.value) {
    await updateUser(selectedUser.value.id, userData)
  } else {
    await createUser(userData)
  }
  isUserDialogOpen.value = false
  emit('refresh')
}

const toggleUserStatus = async (user: User) => {
  if (confirm(`Êtes-vous sûr de vouloir ${user.isActive ? 'désactiver' : 'activer'} cet utilisateur ?`)) {
    await toggleStatus(user.id)
    emit('refresh')
  }
}

const deleteUser = async (user: User) => {
  if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
    const success = await removeUser(user.id)
    if (success) {
      emit('refresh')
    }
  }
}

const filteredUsers = computed(() => {
  let filtered = props.users.filter(user => {
    const matchesSearch = !search.value || 
      `${user.firstName} ${user.lastName}`.toLowerCase().includes(search.value.toLowerCase()) ||
      user.email.toLowerCase().includes(search.value.toLowerCase())
    
    const matchesRole = selectedRoles.value.length === 0 || 
      user.roles.some(role => selectedRoles.value.includes(role))
    
    return matchesSearch && matchesRole
  })

  filtered.sort((a, b) => {
    let comparison = 0
    
    switch (sortBy.value) {
      case 'name':
        comparison = `${a.firstName} ${a.lastName}`.localeCompare(`${b.firstName} ${b.lastName}`)
        break
      case 'email':
        comparison = a.email.localeCompare(b.email)
        break
      case 'createdAt':
        comparison = new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime()
        break
    }

    return sortOrder.value === 'asc' ? comparison : -comparison
  })

  return filtered
})
</script>
