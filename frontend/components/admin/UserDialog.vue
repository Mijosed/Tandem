<template>
  <Dialog v-model:open="props.open">
    <DialogContent>
      <DialogHeader>
        <DialogTitle>{{ props.user ? 'Modifier' : 'Ajouter' }} un utilisateur</DialogTitle>
        <DialogDescription>
          {{ props.user ? 'Modifiez les informations de l\'utilisateur ci-dessous.' : 'Ajoutez un nouvel utilisateur en remplissant le formulaire ci-dessous.' }}
        </DialogDescription>
      </DialogHeader>
      <form @submit.prevent="saveUser" class="space-y-4">
        <div class="grid gap-4">
          <div class="grid grid-cols-2 gap-4">
            <div class="grid gap-2">
              <Label for="firstName">Prénom</Label>
              <Input id="firstName" v-model="form.firstName" required />
            </div>
            <div class="grid gap-2">
              <Label for="lastName">Nom</Label>
              <Input id="lastName" v-model="form.lastName" required />
            </div>
          </div>
          <div class="grid gap-2">
            <Label for="email">Email</Label>
            <Input id="email" type="email" v-model="form.email" required />
          </div>
          <div class="grid gap-2">
            <Label for="role">Rôle</Label>
            <Select id="role" v-model="form.role" required>
              <SelectTrigger>
                <SelectValue placeholder="Sélectionnez un rôle" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="ROLE_USER">
                  <div class="flex items-center gap-2">
                    Utilisateur
                  </div>
                </SelectItem>
                <SelectItem value="ROLE_PREMIUM">
                  <div class="flex items-center gap-2">
                    Premium
                  </div>
                </SelectItem>
                <SelectItem value="ROLE_ADMIN">
                  <div class="flex items-center gap-2">
                    Admin
                  </div>
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="grid gap-2">
            <Label for="status">Statut</Label>
            <div class="flex items-center space-x-2">
              <Switch id="status" v-model="form.isActive" />
              <Label for="status">{{ form.isActive ? 'Actif' : 'Inactif' }}</Label>
            </div>
          </div>
        </div>
        <DialogFooter>
          <Button type="button" variant="outline" @click="$emit('update:open', false)">
            Annuler
          </Button>
          <Button type="submit">
            {{ props.user ? 'Enregistrer' : 'Ajouter' }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Switch } from '@/components/ui/switch'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'

interface User {
  id?: number
  firstName: string
  lastName: string
  email: string
  role: string
  isActive: boolean
}

const props = defineProps<{
  open: boolean
  user: User | null
}>()

const emit = defineEmits<{
  (e: 'update:open', value: boolean): void
  (e: 'save', userData: Partial<User>): void
}>()

const form = ref<Partial<User>>({
  firstName: '',
  lastName: '',
  email: '',
  role: 'ROLE_USER',
  isActive: true,
})

watch(() => props.user, (newUser) => {
  if (newUser) {
    form.value = { ...newUser }
  } else {
    form.value = {
      firstName: '',
      lastName: '',
      email: '',
      role: 'ROLE_USER',
      isActive: true,
    }
  }
}, { immediate: true })

const saveUser = () => {
  emit('save', form.value)
}
</script>
