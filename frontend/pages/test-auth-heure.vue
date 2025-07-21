<template>
  <div class="container mx-auto py-8">
    <h1 class="text-3xl font-bold mb-8">🔑 Test Connexion & Heure</h1>
    
    <!-- Section authentification -->
    <div class="bg-white border rounded-lg p-6 mb-6" v-if="!isAuthenticated">
      <h2 class="text-xl font-bold mb-4">🔐 Connexion</h2>
      
      <form @submit.prevent="login" class="space-y-4">
        <div>
          <label class="block text-sm font-medium mb-1">Email</label>
          <input 
            v-model="loginForm.email" 
            type="email"
            class="border rounded px-3 py-2 w-full"
            placeholder="test@example.com"
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium mb-1">Mot de passe</label>
          <input 
            v-model="loginForm.password" 
            type="password"
            class="border rounded px-3 py-2 w-full"
            placeholder="password"
          />
        </div>
        
        <button 
          type="submit" 
          class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
          :disabled="loginLoading"
        >
          {{ loginLoading ? 'Connexion...' : 'Se connecter' }}
        </button>
        
        <div v-if="loginError" class="text-red-500 text-sm">
          {{ loginError }}
        </div>
      </form>
      
      <div class="mt-4 p-3 bg-gray-100 rounded text-sm">
        <p><strong>Comptes de test :</strong></p>
        <button @click="fillTestUser" class="text-blue-600 hover:underline">
          📧 test@test.com / password
        </button>
      </div>
    </div>

    <!-- Section tests candidatures (visible seulement si connecté) -->
    <div v-if="isAuthenticated" class="space-y-6">
      <!-- Info utilisateur -->
      <div class="bg-green-50 border border-green-200 rounded-lg p-4">
        <h2 class="text-lg font-bold text-green-800">✅ Connecté</h2>
        <p class="text-green-700">Utilisateur : {{ currentUser?.email }} (ID: {{ currentUser?.id }})</p>
        <button @click="logout" class="mt-2 text-sm bg-red-100 hover:bg-red-200 px-2 py-1 rounded">
          Se déconnecter
        </button>
      </div>

      <!-- Test candidatures -->
      <div class="bg-white border rounded-lg p-6">
        <h2 class="text-xl font-bold mb-4">🧪 Test Candidatures avec Heure</h2>
        
        <div class="flex gap-4 mb-4">
          <button 
            @click="testLoadCandidatures" 
            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
            :disabled="loading"
          >
            {{ loading ? 'Chargement...' : 'Charger candidatures' }}
          </button>
          
          <button 
            @click="testCreateWithTime" 
            class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600"
            :disabled="loading"
          >
            Créer candidature avec heure
          </button>
        </div>
        
        <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 p-3 rounded mb-4">
          ❌ {{ error }}
        </div>
        
        <div v-if="testResult" class="bg-blue-50 border border-blue-200 p-3 rounded mb-4">
          <h3 class="font-medium mb-2">Résultat du test :</h3>
          <pre class="text-xs overflow-auto">{{ JSON.stringify(testResult, null, 2) }}</pre>
        </div>
        
        <!-- Liste candidatures -->
        <div v-if="candidatures.length > 0">
          <h3 class="font-medium mb-3">📋 Candidatures ({{ candidatures.length }})</h3>
          <div class="space-y-3">
            <div 
              v-for="candidature in candidatures.slice(0, 3)" 
              :key="candidature.id"
              class="border p-3 rounded bg-gray-50"
            >
              <h4 class="font-medium">{{ candidature.titrePoste }} - {{ candidature.entreprise }}</h4>
              <div class="text-sm mt-1">
                <div>Date entretien: {{ candidature.dateEntretien || 'Non définie' }}</div>
                <div class="bg-yellow-100 p-1 rounded mt-1">
                  <strong>Heure entretien:</strong> 
                  <span class="font-mono">"{{ candidature.heureEntretien }}"</span>
                  <span class="text-gray-600">({{ typeof candidature.heureEntretien }})</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useAuth } from '~/composables/useAuth'
import { useCandidatures } from '~/composables/useCandidatures'

const { currentUser, login: authLogin, logout: authLogout } = useAuth()
const { candidatures, loading, error, fetchCandidatures, createCandidature } = useCandidatures()

const config = useRuntimeConfig()

const loginForm = ref({
  email: '',
  password: ''
})

const config = useRuntimeConfig()

const loginLoading = ref(false)
const config = useRuntimeConfig()

const loginError = ref('')
const testResult = ref<any>(null)

const isAuthenticated = computed(() => !!currentUser.value)

const fillTestUser = () => {
  loginForm.value.email = 'test@test.com'
  loginForm.value.password = 'password'
}

const login = async () => {
  loginLoading.value = true
  loginError.value = ''
  testResult.value = null
  
  try {
    // Faire l'appel API de connexion
    const response = await fetch('${config.public.apiBase}/api/auth/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        email: loginForm.value.email,
        password: loginForm.value.password
      })
    })
    
    if (!response.ok) {
      const errorData = await response.json().catch(() => ({ message: 'Erreur de connexion' }))
      throw new Error(errorData.message || 'Erreur de connexion')
    }
    
    const data = await response.json()
    
    // Sauvegarder le token et l'utilisateur
    if (typeof window !== 'undefined') {
      localStorage.setItem('jwt', data.token)
      localStorage.setItem('user', JSON.stringify(data.user))
    }
    
    // Utiliser la fonction login de useAuth
    authLogin(data.user)
    
    console.log('✅ Connexion réussie', data.user)
  } catch (err: any) {
    loginError.value = err.message || 'Erreur de connexion'
    console.error('❌ Erreur de connexion:', err)
  } finally {
    loginLoading.value = false
  }
}

const logout = () => {
  authLogout()
  testResult.value = null
}

const testLoadCandidatures = async () => {
  testResult.value = null
  try {
    console.log('🔄 Test chargement candidatures...')
    await fetchCandidatures()
    testResult.value = {
      success: true,
      message: `${candidatures.value.length} candidatures chargées`,
      candidatures: candidatures.value.map(c => ({
        id: c.id,
        titrePoste: c.titrePoste,
        heureEntretien: c.heureEntretien,
        heureEntretienType: typeof c.heureEntretien
      }))
    }
  } catch (err: any) {
    testResult.value = {
      success: false,
      error: err.message
    }
  }
}

const testCreateWithTime = async () => {
  testResult.value = null
  try {
    console.log('🚀 Test création candidature avec heure...')
    
    const tomorrow = new Date()
    tomorrow.setDate(tomorrow.getDate() + 1)
    
    const candidatureData = {
      titrePoste: 'Test Heure ' + new Date().getTime(),
      entreprise: 'Debug Corp',
      statut: 'entretien' as const,
      dateDepot: new Date().toISOString().split('T')[0],
      dateEntretien: tomorrow.toISOString().split('T')[0],
      heureEntretien: '16:45',
      notes: 'Test création avec heure'
    }
    
    console.log('📤 Données à créer:', candidatureData)
    
    const result = await createCandidature(candidatureData)
    
    testResult.value = {
      success: true,
      message: 'Candidature créée avec succès',
      candidature: {
        id: result.id,
        titrePoste: result.titrePoste,
        heureEntretien: result.heureEntretien,
        heureEntretienType: typeof result.heureEntretien
      }
    }
    
    // Recharger la liste
    setTimeout(testLoadCandidatures, 1000)
    
  } catch (err: any) {
    testResult.value = {
      success: false,
      error: err.message
    }
  }
}

onMounted(() => {
  console.log('🚀 Page test chargée')
  console.log('👤 Utilisateur actuel:', currentUser.value)
})
</script>
