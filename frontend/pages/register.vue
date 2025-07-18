<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50 relative overflow-hidden">
    <AppHeader />
    
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="absolute top-20 right-10 w-64 h-64 bg-purple-200/30 rounded-full blur-3xl"></div>
      <div class="absolute bottom-20 left-10 w-80 h-80 bg-blue-200/20 rounded-full blur-3xl"></div>
      <div class="absolute top-1/3 left-1/3 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-indigo-100/10 rounded-full blur-3xl"></div>
    </div>

    <div class="relative flex items-center justify-center min-h-[calc(100vh-80px)] px-4 py-12">
      <div class="w-full max-w-md">
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-2xl border border-white/20 overflow-hidden">
          <div class="bg-gradient-to-r from-purple-600 via-blue-600 to-indigo-700 px-8 py-8 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white/20 rounded-full mb-4">
              <UserPlus class="w-8 h-8 text-white" />
            </div>
            <h1 class="text-2xl font-bold text-white mb-2">Rejoignez Tandem !</h1>
            <p class="text-blue-100">Créez votre compte gratuitement</p>
          </div>

          <div class="px-8 py-8">
            <form @submit.prevent="handleSubmit" class="space-y-6">
              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label for="firstName" class="text-sm font-medium text-gray-700">Prénom</Label>
                  <div class="relative group">
                    <User class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400 group-focus-within:text-purple-500 transition-colors" />
                    <Input 
                      id="firstName" 
                      v-model="form.firstName" 
                      type="text" 
                      placeholder="Jean" 
                      class="pl-10 h-12 border-gray-200 focus:border-purple-500 focus:ring-purple-500 rounded-xl"
                      required 
                    />
                  </div>
                </div>

                <div class="space-y-2">
                  <Label for="lastName" class="text-sm font-medium text-gray-700">Nom</Label>
                  <div class="relative group">
                    <User class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400 group-focus-within:text-purple-500 transition-colors" />
                    <Input 
                      id="lastName" 
                      v-model="form.lastName" 
                      type="text" 
                      placeholder="Dupont" 
                      class="pl-10 h-12 border-gray-200 focus:border-purple-500 focus:ring-purple-500 rounded-xl"
                      required 
                    />
                  </div>
                </div>
              </div>

              <div class="space-y-2">
                <Label for="email" class="text-sm font-medium text-gray-700">Adresse email</Label>
                <div class="relative group">
                  <Mail class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400 group-focus-within:text-purple-500 transition-colors" />
                  <Input 
                    id="email" 
                    v-model="form.email" 
                    type="email" 
                    placeholder="exemple@mail.com" 
                    class="pl-10 h-12 border-gray-200 focus:border-purple-500 focus:ring-purple-500 rounded-xl"
                    required 
                  />
                </div>
              </div>

              <div class="space-y-2">
                <Label for="password" class="text-sm font-medium text-gray-700">Mot de passe</Label>
                <div class="relative group">
                  <Lock class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400 group-focus-within:text-purple-500 transition-colors" />
                  <Input 
                    id="password" 
                    v-model="form.password" 
                    type="password" 
                    placeholder="••••••••" 
                    class="pl-10 h-12 border-gray-200 focus:border-purple-500 focus:ring-purple-500 rounded-xl"
                    required 
                  />
                </div>
                <p class="text-xs text-gray-500 mt-1">Minimum 6 caractères</p>
              </div>
              
              <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-center gap-3">
                <AlertCircle class="h-5 w-5 flex-shrink-0" />
                <span class="text-sm">{{ error }}</span>
              </div>
              
              <div v-if="success" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl flex items-center gap-3">
                <CheckCircle class="h-5 w-5 flex-shrink-0" />
                <span class="text-sm">{{ success }}</span>
              </div>
              
              <Button 
                type="submit" 
                class="w-full h-12 bg-gradient-to-r from-purple-600 via-blue-600 to-indigo-700 hover:from-purple-700 hover:via-blue-700 hover:to-indigo-800 text-white font-semibold rounded-xl transition-all duration-300 transform hover:scale-[1.02] shadow-lg"
                :disabled="loading"
              >
                <span v-if="loading" class="flex items-center gap-2">
                  <LoaderCircle class="w-5 h-5 animate-spin" />
                  Inscription en cours...
                </span>
                <span v-else class="flex items-center gap-2">
                  <UserPlus class="w-5 h-5" />
                  Créer mon compte
                </span>
              </Button>
            </form>

            <div class="mt-8 space-y-4">
              <div class="relative">
                <div class="absolute inset-0 flex items-center">
                  <div class="w-full border-t border-gray-200"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                  <span class="px-3 bg-white text-gray-500">ou</span>
                </div>
              </div>

              <div class="text-center">
                <p class="text-sm text-gray-600">
                  Déjà inscrit ?
                  <NuxtLink to="/login" class="text-purple-600 hover:text-purple-800 font-semibold hover:underline transition-colors ml-1">
                    Se connecter
                  </NuxtLink>
                </p>
              </div>

              <div class="text-center">
                <p class="text-xs text-gray-500 leading-relaxed">
                  En vous inscrivant, vous acceptez nos 
                  <a href="/" class="text-purple-600 hover:underline">conditions d'utilisation</a> 
                  et notre 
                  <a href="/" class="text-purple-600 hover:underline">politique de confidentialité</a>.
                </p>
              </div>
            </div>
          </div>
        </div>

        <div class="text-center mt-8">
          <div class="inline-flex items-center gap-2 text-gray-500">
            <img src="/public/logo.png" alt="Tandem" class="h-8" />
            <span class="text-sm font-medium">Tandem</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import { NuxtLink } from '#components'
import { 
  UserPlus, 
  User, 
  Mail, 
  Lock, 
  AlertCircle, 
  CheckCircle, 
  LoaderCircle 
} from 'lucide-vue-next'
import AppHeader from '@/components/sections/AppHeader.vue'

const form = ref({
  firstName: '',
  lastName: '',
  email: '',
  password: ''
})

const loading = ref(false)
const error = ref('')
const success = ref('')

const config = useRuntimeConfig()

const handleSubmit = async () => {
  loading.value = true
  error.value = ''
  success.value = ''

  try {
    if (!form.value.firstName || !form.value.lastName || !form.value.email || !form.value.password) {
      error.value = 'Tous les champs sont requis'
      return
    }

    if (form.value.password.length < 6) {
      error.value = 'Le mot de passe doit contenir au moins 6 caractères'
      return
    }

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
    
    form.value = {
      firstName: '',
      lastName: '',
      email: '',
      password: ''
    }

    setTimeout(() => {
      navigateTo('/login')
    }, 2000)

  } catch (err: any) {
    console.error('Erreur lors de l\'inscription:', err)
    
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
