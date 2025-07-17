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
          <div>
            <Label for="firstName">Prénom</Label>
            <Input 
              id="firstName" 
              v-model="form.firstName" 
              type="text" 
              placeholder="Jean" 
              class="mt-1" 
              required 
            />
          </div>
          <div>
            <Label for="lastName">Nom</Label>
            <Input 
              id="lastName" 
              v-model="form.lastName" 
              type="text" 
              placeholder="Dupont" 
              class="mt-1" 
              required 
            />
          </div>
          <div>
            <Label for="email">Adresse email</Label>
            <Input 
              id="email" 
              v-model="form.email" 
              type="email" 
              placeholder="exemple@mail.com" 
              class="mt-1" 
              required 
            />
          </div>
          <div>
            <Label for="password">Mot de passe</Label>
            <Input 
              id="password" 
              v-model="form.password" 
              type="password" 
              placeholder="••••••••" 
              class="mt-1" 
              required 
            />
          </div>
          
          <!-- Affichage des erreurs -->
          <div v-if="error" class="text-red-600 text-sm bg-red-50 p-3 rounded">
            {{ error }}
          </div>
          
          <!-- Affichage du succès -->
          <div v-if="success" class="text-green-600 text-sm bg-green-50 p-3 rounded">
            {{ success }}
          </div>
          
          <Button 
            type="submit" 
            class="w-full" 
            :disabled="loading"
          >
            {{ loading ? 'Inscription en cours...' : 'S\'inscrire' }}
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

// Gestion du formulaire
const form = ref({
  firstName: '',
  lastName: '',
  email: '',
  password: ''
})

// États
const loading = ref(false)
const error = ref('')
const success = ref('')

// Configuration
const config = useRuntimeConfig()

// Fonction de soumission du formulaire
const handleSubmit = async () => {
  loading.value = true
  error.value = ''
  success.value = ''

  try {
    // Validation côté client
    if (!form.value.firstName || !form.value.lastName || !form.value.email || !form.value.password) {
      error.value = 'Tous les champs sont requis'
      return
    }

    if (form.value.password.length < 6) {
      error.value = 'Le mot de passe doit contenir au moins 6 caractères'
      return
    }

    // Appel à l'API d'inscription
    console.log('Tentative d\'inscription avec:', form.value)
    
    const response = await fetch(`${config.public.apiBase}/api/auth/register`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        firstName: form.value.firstName,
        lastName: form.value.lastName,
        email: form.value.email,
        password: form.value.password
      })
    })
    
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`)
    }
    
    const result = await response.json()
    console.log('Réponse API:', result)

    success.value = 'Inscription réussie ! Vous pouvez maintenant vous connecter.'
    
    // Réinitialiser le formulaire
    form.value = {
      firstName: '',
      lastName: '',
      email: '',
      password: ''
    }

    // Rediriger vers la page de connexion après 2 secondes
    setTimeout(() => {
      navigateTo('/login')
    }, 2000)

  } catch (err: any) {
    console.error('Erreur lors de l\'inscription:', err)
    
    // Gestion des erreurs spécifiques
    if (err.status === 409 || err.statusCode === 409) {
      error.value = 'Un utilisateur avec cet email existe déjà'
    } else if (err.status === 400 || err.statusCode === 400) {
      error.value = err.data?.error || err.message || 'Données invalides'
    } else {
      error.value = err.message || 'Une erreur est survenue lors de l\'inscription'
    }
  } finally {
    loading.value = false
  }
}
</script>
