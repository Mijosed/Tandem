<template>
  <Dialog :open="open" @update:open="$emit('update:open', $event)">
    <DialogContent class="sm:max-w-md">
      <DialogHeader>
        <DialogTitle>
          {{ isEdit ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur' }}
        </DialogTitle>
        <DialogDescription>
          {{ isEdit ? 'Modifiez les informations de l\'utilisateur' : 'Créez un nouvel utilisateur pour la plateforme' }}
        </DialogDescription>
      </DialogHeader>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label for="firstName">Prénom</Label>
            <Input
              id="firstName"
              v-model="formData.firstName"
              placeholder="John"
              required
            />
          </div>
          <div class="space-y-2">
            <Label for="lastName">Nom</Label>
            <Input
              id="lastName"
              v-model="formData.lastName"
              placeholder="Doe"
              required
            />
          </div>
        </div>

        <div class="space-y-2">
          <Label for="email">Email</Label>
          <Input
            id="email"
            v-model="formData.email"
            type="email"
            placeholder="john.doe@example.com"
            required
          />
        </div>

        <div v-if="!isEdit" class="space-y-2">
          <Label for="password">Mot de passe</Label>
          <Input
            id="password"
            v-model="formData.password"
            type="password"
            placeholder="••••••••"
            required
          />
        </div>

        <div class="space-y-2">
          <Label>Rôles</Label>
          <div class="space-y-2">
            <div v-for="role in availableRoles" :key="role.value" class="flex items-center space-x-2">
              <Checkbox
                :id="role.value"
                :model-value="formData.roles.includes(role.value)"
                @update:model-value="toggleRole(role.value)"
              />
              <Label :for="role.value" class="text-sm font-normal cursor-pointer">
                {{ role.label }}
              </Label>
            </div>
          </div>
        </div>

        <div class="flex items-center space-x-2">
          <Checkbox
            id="isActive"
            v-model="formData.isActive"
          />
          <Label for="isActive" class="text-sm font-normal cursor-pointer">
            Compte actif
          </Label>
        </div>

        <DialogFooter>
          <Button type="button" variant="outline" @click="$emit('update:open', false)">
            Annuler
          </Button>
          <Button type="submit" :disabled="loading">
            <LoaderCircle v-if="loading" class="mr-2 h-4 w-4 animate-spin" />
            {{ isEdit ? 'Sauvegarder' : 'Créer' }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { LoaderCircle } from 'lucide-vue-next'
import type { User, UserFormData } from '~/composables/useUsers'
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
import { Button } from '~/components/ui/button'
import { Checkbox } from '~/components/ui/checkbox'

interface Props {
  open: boolean
  user?: User | null
}

interface Emits {
  (e: 'update:open', value: boolean): void
  (e: 'save', userData: UserFormData): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const loading = ref(false)

const availableRoles = [
  { value: 'ROLE_USER', label: 'Utilisateur' },
  { value: 'ROLE_PREMIUM', label: 'Premium' },
  { value: 'ROLE_ADMIN', label: 'Administrateur' }
]

const isEdit = computed(() => !!props.user)

const formData = ref<UserFormData>({
  firstName: '',
  lastName: '',
  email: '',
  roles: ['ROLE_USER'],
  isActive: true,
  password: '',
})

watch(() => props.user, (newUser) => {
  if (newUser) {
    formData.value = {
      firstName: newUser.firstName,
      lastName: newUser.lastName,
      email: newUser.email,
      roles: [...newUser.roles],
      isActive: newUser.isActive,
    }
  } else {
    formData.value = {
      firstName: '',
      lastName: '',
      email: '',
      roles: ['ROLE_USER'],
      isActive: true,
      password: '',
    }
  }
}, { immediate: true })

const toggleRole = (role: string) => {
  const index = formData.value.roles.indexOf(role)
  if (index === -1) {
    formData.value.roles.push(role)
  } else {
    if (formData.value.roles.length > 1) {
      formData.value.roles.splice(index, 1)
    }
  }
}

const handleSubmit = async () => {
  loading.value = true
  
  try {
    emit('save', { ...formData.value })
  } catch (error) {
    console.error('Erreur lors de la sauvegarde:', error)
  } finally {
    loading.value = false
  }
}
</script>
