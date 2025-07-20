<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50 relative overflow-hidden">
    <AppHeader />
    
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="absolute top-20 left-10 w-64 h-64 bg-blue-200/30 rounded-full blur-3xl"></div>
      <div class="absolute bottom-20 right-10 w-80 h-80 bg-purple-200/20 rounded-full blur-3xl"></div>
      <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-indigo-100/10 rounded-full blur-3xl"></div>
    </div>

    <div class="relative flex items-center justify-center min-h-[calc(100vh-80px)] px-4 py-12">
      <div class="w-full max-w-md">
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-2xl border border-white/20 overflow-hidden">
          <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700 px-8 py-8 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white/20 rounded-full mb-4">
              <LogIn class="w-8 h-8 text-white" />
            </div>
            <h1 class="text-2xl font-bold text-white mb-2">Bon retour !</h1>
            <p class="text-blue-100">Connectez-vous à votre compte Tandem</p>
          </div>

          <div class="px-8 py-8">
            <form @submit.prevent="handleSubmit" class="space-y-6">
              <div class="space-y-2">
                <Label for="email" class="text-sm font-medium text-gray-700">Adresse email</Label>
                <div class="relative group">
                  <Mail class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400 group-focus-within:text-blue-500 transition-colors" />
                  <Input 
                    id="email" 
                    v-model="form.email"
                    type="email" 
                    placeholder="exemple@mail.com" 
                    class="pl-10 h-12 border-gray-200 focus:border-blue-500 focus:ring-blue-500 rounded-xl"
                    required
                  />
                </div>
              </div>

              <div class="space-y-2">
                <Label for="password" class="text-sm font-medium text-gray-700">Mot de passe</Label>
                <div class="relative group">
                  <Lock class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400 group-focus-within:text-blue-500 transition-colors" />
                  <Input 
                    id="password" 
                    v-model="form.password"
                    type="password" 
                    placeholder="••••••••" 
                    class="pl-10 h-12 border-gray-200 focus:border-blue-500 focus:ring-blue-500 rounded-xl"
                    required
                  />
                </div>
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
                class="w-full h-12 bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700 hover:from-blue-700 hover:via-purple-700 hover:to-indigo-800 text-white font-semibold rounded-xl transition-all duration-300 transform hover:scale-[1.02] shadow-lg"
                :disabled="loading"
              >
                <span v-if="loading" class="flex items-center gap-2">
                  <LoaderCircle class="w-5 h-5 animate-spin" />
                  Connexion en cours...
                </span>
                <span v-else class="flex items-center gap-2">
                  <LogIn class="w-5 h-5" />
                  Se connecter
                </span>
              </Button>
            </form>

            <div class="mt-8 space-y-4">
              <div class="text-center">
                <NuxtLink to="/forgot-password" class="text-sm text-blue-600 hover:text-blue-800 font-medium hover:underline transition-colors">
                  Mot de passe oublié ?
                </NuxtLink>
              </div>
              
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
                  Pas encore de compte ?
                  <NuxtLink to="/register" class="text-blue-600 hover:text-blue-800 font-semibold hover:underline transition-colors ml-1">
                    Créer un compte
                  </NuxtLink>
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
import { navigateTo } from 'nuxt/app'
import { 
  LogIn, 
  Mail, 
  Lock, 
  AlertCircle, 
  CheckCircle, 
  LoaderCircle 
} from 'lucide-vue-next'
import AppHeader from '@/components/sections/AppHeader.vue'

const form = ref({
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
    if (!form.value.email || !form.value.password) {
      error.value = 'Email et mot de passe requis'
      return
    }

    console.log('Tentative de connexion avec:', { email: form.value.email })
    
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

    // Stocke le token JWT si présent
    if (result.token) {
      localStorage.setItem('jwt', result.token)
    }
    if (result.user) {
      localStorage.setItem('user', JSON.stringify(result.user))
      localStorage.setItem('isLoggedIn', 'true')
    }

    form.value = {
      email: '',
      password: ''
    }

    setTimeout(() => {
      navigateTo('/dashboard')
    }, 1000)

  } catch (err: any) {
    console.error('Erreur lors de la connexion:', err)
    
    error.value = err.message || 'Une erreur est survenue lors de la connexion'
  } finally {
    loading.value = false
  }
}
</script>