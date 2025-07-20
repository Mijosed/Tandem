<template>
  <div>
          <header class="flex h-16 shrink-0 items-center gap-2 border-b transition-[width,height] ease-linear">
        <div class="flex items-center gap-2 px-4">
          <SidebarTrigger v-if="isMobile" class="-ml-1" />
          <h1 class="text-2xl font-bold">Mon compte</h1>
        </div>
      </header>

    <div class="container py-6 px-4">
      <div class="space-y-6">
        <!-- Section du profil -->
        <Card>
          <CardHeader>
            <CardTitle>Profil</CardTitle>
            <CardDescription>
              Gérez vos informations personnelles
            </CardDescription>
          </CardHeader>
          <CardContent>
            <form @submit.prevent="updateProfile" class="space-y-6">
              <div class="flex items-center gap-6">
                <Avatar class="h-20 w-20">
                  <AvatarImage :src="user.avatar" />
                  <AvatarFallback>{{ getInitials(user.firstName + ' ' + user.lastName) }}</AvatarFallback>
                </Avatar>
                <Button variant="outline">
                  <Upload class="mr-2 h-4 w-4" />
                  Changer l'avatar
                </Button>
              </div>

              <div class="grid gap-4 md:grid-cols-2">
                <div class="space-y-2">
                  <Label for="firstName">Prénom</Label>
                  <Input id="firstName" v-model="form.firstName" />
                </div>
                <div class="space-y-2">
                  <Label for="lastName">Nom</Label>
                  <Input id="lastName" v-model="form.lastName" />
                </div>
              </div>

              <div class="space-y-2">
                <Label for="email">Email</Label>
                <Input id="email" v-model="form.email" type="email" />
              </div>

              <div>
                <Button type="submit">
                  <Save class="mr-2 h-4 w-4" />
                  Enregistrer les modifications
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>

        <!-- Section du mot de passe -->
        <Card>
          <CardHeader>
            <CardTitle>Sécurité</CardTitle>
            <CardDescription>
              Mettez à jour votre mot de passe
            </CardDescription>
          </CardHeader>
          <CardContent>
            <form @submit.prevent="updatePassword" class="space-y-4">
              <div class="space-y-2">
                <Label for="currentPassword">Mot de passe actuel</Label>
                <Input id="currentPassword" v-model="passwordForm.currentPassword" type="password" />
              </div>

              <div class="space-y-2">
                <Label for="newPassword">Nouveau mot de passe</Label>
                <Input id="newPassword" v-model="passwordForm.newPassword" type="password" />
              </div>

              <div class="space-y-2">
                <Label for="confirmPassword">Confirmer le mot de passe</Label>
                <Input id="confirmPassword" v-model="passwordForm.confirmPassword" type="password" />
              </div>

              <Button type="submit">
                <Key class="mr-2 h-4 w-4" />
                Changer le mot de passe
              </Button>
            </form>
          </CardContent>
        </Card>

        <!-- Section Authentification à deux facteurs -->
        <TwoFactorAuth />

        <!-- Section de notification -->
        <Card>
          <CardHeader>
            <CardTitle>Préférences de notification</CardTitle>
            <CardDescription>
              Personnalisez vos notifications
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div class="space-y-4">
              <div class="flex items-center justify-between space-x-2">
                <Label for="emailNotifications">Notifications par email</Label>
                <Switch id="emailNotifications" v-model="preferences.emailNotifications" />
              </div>
              
              <div class="flex items-center justify-between space-x-2">
                <Label for="reminderNotifications">Rappels d'entretien</Label>
                <Switch id="reminderNotifications" v-model="preferences.reminderNotifications" />
              </div>

              <div class="flex items-center justify-between space-x-2">
                <Label for="applicationUpdates">Mises à jour des candidatures</Label>
                <Switch id="applicationUpdates" v-model="preferences.applicationUpdates" />
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Upload, Save, Key } from 'lucide-vue-next'
import { SidebarTrigger } from '~/components/ui/sidebar'
import { useMediaQuery } from '@vueuse/core'
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '~/components/ui/card'
import { Button } from '~/components/ui/button'
import { Input } from '~/components/ui/input'
import { Label } from '~/components/ui/label'
import { Switch } from '~/components/ui/switch'
import {
  Avatar,
  AvatarFallback,
  AvatarImage,
} from '~/components/ui/avatar'
import TwoFactorAuth from '~/components/dashboard/TwoFactorAuth.vue'

definePageMeta({
  layout: 'dashboard',
  middleware: ['auth']
})

// Détection mobile pour afficher conditionnellement le SidebarTrigger
const isMobile = useMediaQuery('(max-width: 768px)')

// Données utilisateur mockées
const user = ref({
  firstName: 'Jean',
  lastName: 'Dupont',
  email: 'jean.dupont@example.com',
  avatar: '/logo.png'
})

// Formulaire de profil
const form = ref({
  firstName: user.value.firstName,
  lastName: user.value.lastName,
  email: user.value.email
})

// Formulaire de mot de passe
const passwordForm = ref({
  currentPassword: '',
  newPassword: '',
  confirmPassword: ''
})

// Préférences de notification
const preferences = ref({
  emailNotifications: true,
  reminderNotifications: true,
  applicationUpdates: true
})

const getInitials = (name: string) => {
  return name
    .split(' ')
    .map(part => part.charAt(0))
    .join('')
    .toUpperCase()
}

const updateProfile = async () => {
  // TODO: Implémenter la mise à jour du profil
  console.log('Mise à jour du profil:', form.value)
}

const updatePassword = async () => {
  if (passwordForm.value.newPassword !== passwordForm.value.confirmPassword) {
    // TODO: Afficher une erreur
    return
  }
  // TODO: Implémenter le changement de mot de passe
  console.log('Changement de mot de passe')
  passwordForm.value = {
    currentPassword: '',
    newPassword: '',
    confirmPassword: ''
  }
}
</script>
