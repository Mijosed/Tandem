<template>
  <AppHeader />
  <div class="min-h-screen flex items-center justify-center bg-gray-100 px-4">
    <Card class="w-full max-w-md">
      <CardHeader>
        <CardTitle>Inscription</CardTitle>
        <CardDescription>Créez un compte Tandem gratuitement</CardDescription>
      </CardHeader>
      <CardContent>
        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <Label for="firstname">Prénom</Label>
              <Input 
                id="firstname" 
                type="text" 
                v-model="formData.firstname"
                required
                class="mt-1"
              />
            </div>
            <div>
              <Label for="lastname">Nom</Label>
              <Input 
                id="lastname" 
                type="text" 
                v-model="formData.lastname"
                required
                class="mt-1"
              />
            </div>
          </div>
          <div>
            <Label for="email">Adresse email</Label>
            <Input 
              id="email" 
              type="email" 
              placeholder="exemple@mail.com" 
              v-model="formData.email"
              required
              class="mt-1"
            />
          </div>
          <div>
            <Label for="password">Mot de passe</Label>
            <Input 
              id="password" 
              type="password" 
              placeholder="••••••••" 
              v-model="formData.password"
              required
              class="mt-1"
            />
          </div>
          <div v-if="error" class="text-red-500 text-sm">
            {{ error }}
          </div>
          <Button type="submit" class="w-full" :disabled="loading">
            {{ loading ? 'Inscription en cours...' : "S'inscrire" }}
          </Button>
        </form>
        <p class="text-center text-sm mt-4">Déjà inscrit ?
          <NuxtLink to="/login" class="text-blue-600 hover:underline">Se connecter</NuxtLink>
        </p>
      </CardContent>
    </Card>
  </div>
</template>

<script setup lang="ts">
import {
  Card,
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent
} from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import { NuxtLink } from '#components'
import AppHeader from '@/components/sections/AppHeader.vue'
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const router = useRouter()
const authStore = useAuthStore()

const formData = ref({
  firstname: '',
  lastname: '',
  email: '',
  password: ''
})

const error = ref('')
const loading = ref(false)

const handleSubmit = async () => {
  try {
    loading.value = true
    error.value = ''
    await authStore.register(formData.value)
    router.push('/login')
  } catch (err: any) {
    error.value = err.message || "Une erreur est survenue lors de l'inscription"
  } finally {
    loading.value = false
  }
}
</script>
