<template>
  <AppHeader />
  <div class="min-h-screen flex items-center justify-center bg-gray-100 px-4">
    <Card class="w-full max-w-md">
      <CardHeader>
        <CardTitle>Connexion</CardTitle>
        <CardDescription>Connectez-vous à votre compte Tandem</CardDescription>
      </CardHeader>
      <CardContent>
        <form @submit.prevent="handleSubmit" class="space-y-4">
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
            {{ loading ? 'Connexion en cours...' : 'Se connecter' }}
          </Button>
        </form>
        <p class="text-center text-sm mt-4">Pas encore de compte ?
          <NuxtLink to="/register" class="text-blue-600 hover:underline">S'inscrire</NuxtLink>
        </p>
        <p class="text-center text-sm mt-2">
          Mot de passe oublié ?
          <NuxtLink to="/forgot-password" class="text-blue-600 hover:underline">Réinitialiser</NuxtLink>
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
    if (!form.value.email || !form.value.password) {
      error.value = 'Email et mot de passe requis'
      return
    }

    // Appel à l'API de connexion
    console.log('Tentative de connexion avec:', { email: form.value.email })
    
    // Utilisation directe de l'URL Traefik qui fonctionne
    const response = await fetch(`http://localhost:8888/api/auth/login`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        email: form.value.email,
        password: form.value.password
      })
    })
    
    if (!response.ok) {
      const errorData = await response.json().catch(() => ({ error: 'Erreur de connexion' }))
      throw new Error(errorData.error || `HTTP ${response.status}: ${response.statusText}`)
    }
    
    const result = await response.json()
    console.log('Réponse API:', result)

    success.value = 'Connexion réussie ! Redirection...'
    
    // Sauvegarder les données utilisateur dans le localStorage
    if (result.user) {
      localStorage.setItem('user', JSON.stringify(result.user))
      localStorage.setItem('isLoggedIn', 'true')
    }
    
    // Réinitialiser le formulaire
    form.value = {
      email: '',
      password: ''
    }

    // Rediriger vers le dashboard après 1 seconde
    setTimeout(() => {
      navigateTo('/dashboard')
    }, 1000)

  } catch (err: any) {
    console.error('Erreur lors de la connexion:', err)
    
    // Gestion des erreurs spécifiques
    error.value = err.message || 'Une erreur est survenue lors de la connexion'
  } finally {
    loading.value = false
  }
}
</script>