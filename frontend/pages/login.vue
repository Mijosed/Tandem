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

    <!-- Modal 2FA -->
    <Dialog v-model:open="show2FAModal">
      <DialogContent class="max-w-md">
        <DialogHeader>
          <DialogTitle class="flex items-center gap-3">
            <Shield class="h-6 w-6 text-blue-600" />
            Authentification à deux facteurs
          </DialogTitle>
          <DialogDescription>
            Entrez le code à 6 chiffres de votre application d'authentification
          </DialogDescription>
        </DialogHeader>

        <div class="space-y-4 py-4">
          <div class="text-center">
            <p class="text-sm text-gray-600 mb-4">
              Bonjour {{ userInfo?.firstName || 'Utilisateur' }}, votre compte est protégé par 2FA
            </p>
          </div>

          <div class="space-y-2">
            <Label for="twoFactorCode">Code d'authentification</Label>
            <Input 
              id="twoFactorCode"
              v-model="twoFactorCode"
              placeholder="123456"
              maxlength="6"
              class="text-center text-lg tracking-widest font-mono"
              @input="error2FA = ''"
            />
            <p class="text-xs text-gray-500">
              Saisissez le code de votre application Google Authenticator
            </p>
          </div>

          <div v-if="error2FA" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-center gap-3">
            <AlertCircle class="h-5 w-5 flex-shrink-0" />
            <span class="text-sm">{{ error2FA }}</span>
          </div>

          <div class="text-center">
            <p class="text-xs text-gray-500">
              Vous avez perdu votre appareil ? Utilisez un 
              <button class="text-blue-600 hover:underline" @click="showBackupCodeInput = true">
                code de récupération
              </button>
            </p>
          </div>

          <!-- Champ pour code de récupération -->
          <div v-if="showBackupCodeInput" class="space-y-2">
            <Label for="backupCode">Code de récupération</Label>
            <Input 
              id="backupCode"
              v-model="backupCode"
              placeholder="CODE12AB"
              class="text-center font-mono"
            />
          </div>
        </div>

        <DialogFooter>
          <Button @click="show2FAModal = false" variant="outline">
            Annuler
          </Button>
          <Button 
            @click="verify2FA"
            :disabled="loading2FA || (!twoFactorCode && !backupCode)"
          >
            <Loader2 v-if="loading2FA" class="w-4 h-4 mr-2 animate-spin" />
            <Shield class="w-4 h-4 mr-2" />
            Vérifier
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
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
  LoaderCircle,
  Shield,
  Loader2
} from 'lucide-vue-next'
import AppHeader from '@/components/sections/AppHeader.vue'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'

const form = ref({
  email: '',
  password: ''
})

const loading = ref(false)
const error = ref('')
const success = ref('')

// Variables pour 2FA
const show2FAModal = ref(false)
const loading2FA = ref(false)
const error2FA = ref('')
const twoFactorCode = ref('')
const tempToken = ref('')
const userInfo = ref(null)
const showBackupCodeInput = ref(false)
const backupCode = ref('')

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
    
    const response = await fetch(`${config.public.apiBase}/api/auth/login`, {
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

    // Vérifier si 2FA est requis
    if (result.requiresTwoFactor) {
      // Stocker les informations temporaires et afficher le modal 2FA
      tempToken.value = result.tempToken
      userInfo.value = result.user
      show2FAModal.value = true
      return
    }

    // Connexion normale réussie
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

const verify2FA = async () => {
  loading2FA.value = true
  error2FA.value = ''

  try {
    const codeToVerify = twoFactorCode.value || backupCode.value
    
    if (!codeToVerify) {
      error2FA.value = 'Veuillez entrer un code d\'authentification ou de récupération'
      return
    }

    if (twoFactorCode.value && twoFactorCode.value.length !== 6) {
      error2FA.value = 'Le code d\'authentification doit contenir 6 chiffres'
      return
    }

    const response = await fetch(`${config.public.apiBase}/api/auth/2fa/verify`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        email: form.value.email,
        code: codeToVerify
      })
    })

    if (!response.ok) {
      const errorData = await response.json().catch(() => ({ error: 'Erreur de vérification' }))
      throw new Error(errorData.error || 'Code invalide')
    }

    const result = await response.json()

    // Connexion 2FA réussie
    success.value = 'Authentification réussie ! Redirection...'
    show2FAModal.value = false

    // Afficher un avertissement si c'est un code de récupération
    if (result.warning) {
      console.warn(result.warning)
      // Vous pourriez afficher une notification ici
    }

    // Stocker les informations utilisateur
    if (result.token) {
      localStorage.setItem('jwt', result.token)
    }
    if (result.user) {
      localStorage.setItem('user', JSON.stringify(result.user))
      localStorage.setItem('isLoggedIn', 'true')
    }

    // Nettoyer les données temporaires
    tempToken.value = ''
    twoFactorCode.value = ''
    backupCode.value = ''
    showBackupCodeInput.value = false
    form.value = { email: '', password: '' }

    setTimeout(() => {
      navigateTo('/dashboard')
    }, 1000)

  } catch (err: any) {
    console.error('Erreur lors de la vérification 2FA:', err)
    error2FA.value = err.message || 'Code invalide'
  } finally {
    loading2FA.value = false
  }
}
</script>