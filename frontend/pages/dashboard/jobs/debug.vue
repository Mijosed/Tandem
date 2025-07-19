<template>
  <div class="container max-w-4xl mx-auto p-6 space-y-6">
    <div class="text-center">
      <h1 class="text-2xl font-bold mb-4">Test API France Travail</h1>
      <p class="text-muted-foreground">Diagnostic de la connexion et des erreurs</p>
    </div>

    <!-- Test de connexion -->
    <Card>
      <CardHeader>
        <CardTitle>1. Test de connexion</CardTitle>
      </CardHeader>
      <CardContent>
        <div class="space-y-3">
          <Button @click="testConnection" :disabled="testing.connection">
            <Loader2 v-if="testing.connection" class="h-4 w-4 mr-2 animate-spin" />
            <CheckCircle v-else class="h-4 w-4 mr-2" />
            Tester la connexion
          </Button>
          
          <div v-if="results.connection" class="p-3 rounded border" :class="results.connection.success ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'">
            <p class="font-medium" :class="results.connection.success ? 'text-green-800' : 'text-red-800'">
              {{ results.connection.message }}
            </p>
            <pre v-if="results.connection.details" class="text-xs mt-2 overflow-auto">{{ JSON.stringify(results.connection.details, null, 2) }}</pre>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Test des secteurs -->
    <Card>
      <CardHeader>
        <CardTitle>2. Test des secteurs</CardTitle>
      </CardHeader>
      <CardContent>
        <div class="space-y-3">
          <Button @click="testSectors" :disabled="testing.sectors">
            <Loader2 v-if="testing.sectors" class="h-4 w-4 mr-2 animate-spin" />
            <Building v-else class="h-4 w-4 mr-2" />
            Charger les secteurs
          </Button>
          
          <div v-if="results.sectors" class="p-3 rounded border" :class="results.sectors.success ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'">
            <p class="font-medium" :class="results.sectors.success ? 'text-green-800' : 'text-red-800'">
              {{ results.sectors.message }}
            </p>
            <div v-if="results.sectors.success && results.sectors.data" class="mt-2">
              <p class="text-sm text-green-700">{{ results.sectors.data.length }} secteurs trouvés</p>
              <details class="mt-2">
                <summary class="cursor-pointer text-sm">Voir les premiers secteurs</summary>
                <pre class="text-xs mt-2 overflow-auto max-h-40">{{ JSON.stringify(results.sectors.data.slice(0, 5), null, 2) }}</pre>
              </details>
            </div>
            <pre v-else-if="results.sectors.error" class="text-xs mt-2 overflow-auto text-red-700">{{ results.sectors.error }}</pre>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Test de recherche -->
    <Card>
      <CardHeader>
        <CardTitle>3. Test de recherche</CardTitle>
      </CardHeader>
      <CardContent>
        <div class="space-y-3">
          <div class="grid gap-3 md:grid-cols-3">
            <Input v-model="searchParams.keywords" placeholder="Mots-clés (ex: développeur)" />
            <Input v-model="searchParams.location" placeholder="Localisation (ex: 75001)" />
            <Input v-model="searchParams.limit" placeholder="Limite (ex: 5)" type="number" />
          </div>
          
          <Button @click="testSearch" :disabled="testing.search">
            <Loader2 v-if="testing.search" class="h-4 w-4 mr-2 animate-spin" />
            <Search v-else class="h-4 w-4 mr-2" />
            Tester la recherche
          </Button>
          
          <div v-if="results.search" class="p-3 rounded border" :class="results.search.success ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'">
            <p class="font-medium" :class="results.search.success ? 'text-green-800' : 'text-red-800'">
              {{ results.search.message }}
            </p>
            <div v-if="results.search.success && results.search.data" class="mt-2">
              <p class="text-sm text-green-700">
                {{ results.search.data.jobs ? results.search.data.jobs.length : 0 }} offres trouvées
              </p>
              <details v-if="results.search.data.jobs && results.search.data.jobs.length > 0" class="mt-2">
                <summary class="cursor-pointer text-sm">Voir les premières offres</summary>
                <pre class="text-xs mt-2 overflow-auto max-h-60">{{ JSON.stringify(results.search.data.jobs.slice(0, 2), null, 2) }}</pre>
              </details>
            </div>
            <div v-else-if="results.search.error" class="mt-2">
              <p class="text-sm text-red-700 mb-2">Détails de l'erreur :</p>
              <pre class="text-xs overflow-auto max-h-40 bg-red-100 p-2 rounded text-red-800">{{ results.search.error }}</pre>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Test de recherche sans résultats -->
    <Card>
      <CardHeader>
        <CardTitle>4. Test de recherche sans résultats</CardTitle>
      </CardHeader>
      <CardContent>
        <div class="space-y-3">
          <div class="bg-yellow-100 p-3 rounded text-sm">
            <p class="font-medium text-yellow-800">Test avec des mots-clés impossibles</p>
            <p class="text-yellow-700">Cette recherche devrait retourner 0 résultats sans erreur</p>
          </div>
          
          <Button @click="testNoResults" :disabled="testing.noResults">
            <Loader2 v-if="testing.noResults" class="h-4 w-4 mr-2 animate-spin" />
            <Search v-else class="h-4 w-4 mr-2" />
            Tester recherche sans résultats
          </Button>
          
          <div v-if="results.noResults" class="p-3 rounded border" :class="results.noResults.success ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'">
            <p class="font-medium" :class="results.noResults.success ? 'text-green-800' : 'text-red-800'">
              {{ results.noResults.message }}
            </p>
            <div v-if="results.noResults.success && results.noResults.data" class="mt-2">
              <p class="text-sm text-green-700">
                {{ results.noResults.data.jobs ? results.noResults.data.jobs.length : 0 }} offres trouvées
              </p>
              <p class="text-sm text-green-700">
                Pagination: page {{ results.noResults.data.pagination?.current_page || 'N/A' }}
              </p>
            </div>
            <div v-else-if="results.noResults.error" class="mt-2">
              <p class="text-sm text-red-700 mb-2">Détails de l'erreur :</p>
              <pre class="text-xs overflow-auto max-h-40 bg-red-100 p-2 rounded text-red-800">{{ results.noResults.error }}</pre>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Test de l'URL exacte qui pose problème -->
    <Card>
      <CardHeader>
        <CardTitle>4. Test de l'URL problématique</CardTitle>
      </CardHeader>
      <CardContent>
        <div class="space-y-3">
          <div class="bg-gray-100 p-3 rounded text-sm font-mono">
            {{ problemUrl }}
          </div>
          
          <Button @click="testProblemUrl" :disabled="testing.problemUrl">
            <Loader2 v-if="testing.problemUrl" class="h-4 w-4 mr-2 animate-spin" />
            <AlertTriangle v-else class="h-4 w-4 mr-2" />
            Tester cette URL exacte
          </Button>
          
          <div v-if="results.problemUrl" class="p-3 rounded border" :class="results.problemUrl.success ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'">
            <p class="font-medium" :class="results.problemUrl.success ? 'text-green-800' : 'text-red-800'">
              {{ results.problemUrl.message }}
            </p>
            <div v-if="results.problemUrl.error" class="mt-2">
              <details>
                <summary class="cursor-pointer text-sm">Voir l'erreur complète</summary>
                <pre class="text-xs mt-2 overflow-auto max-h-60 bg-red-100 p-2 rounded text-red-800">{{ results.problemUrl.error }}</pre>
              </details>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Retour -->
    <div class="text-center">
      <Button @click="$router.push('/dashboard/jobs')" variant="outline">
        <ArrowLeft class="h-4 w-4 mr-2" />
        Retour aux offres
      </Button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { 
  CheckCircle, Building, Search, AlertTriangle, ArrowLeft, Loader2 
} from 'lucide-vue-next'
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'

// États
const testing = ref({
  connection: false,
  sectors: false,
  search: false,
  noResults: false,
  problemUrl: false
})

const results = ref({
  connection: null,
  sectors: null,
  search: null,
  noResults: null,
  problemUrl: null
})

const searchParams = ref({
  keywords: 'développeur',
  location: '93130',
  limit: '5'
})

const problemUrl = ref('http://localhost:8888/api/pole-emploi/search?page=1&keywords=developpeur&location=93130&limit=20')

// Fonctions de test
const testConnection = async () => {
  testing.value.connection = true
  try {
    const response = await fetch('http://localhost:8888/api/pole-emploi/test')
    const data = await response.json()
    
    results.value.connection = {
      success: response.ok && data.success,
      message: data.success ? 'Connexion réussie !' : `Erreur: ${data.message}`,
      details: data
    }
  } catch (error) {
    results.value.connection = {
      success: false,
      message: 'Erreur de connexion au serveur',
      details: { error: error.message }
    }
  } finally {
    testing.value.connection = false
  }
}

const testSectors = async () => {
  testing.value.sectors = true
  try {
    const response = await fetch('http://localhost:8888/api/pole-emploi/sectors')
    const data = await response.json()
    
    results.value.sectors = {
      success: response.ok && data.success,
      message: data.success ? `${data.data.length} secteurs chargés` : `Erreur: ${data.message}`,
      data: data.success ? data.data : null,
      error: !data.success ? JSON.stringify(data, null, 2) : null
    }
  } catch (error) {
    results.value.sectors = {
      success: false,
      message: 'Erreur lors du chargement des secteurs',
      error: error.message
    }
  } finally {
    testing.value.sectors = false
  }
}

const testSearch = async () => {
  testing.value.search = true
  try {
    const params = new URLSearchParams({
      page: '1',
      keywords: searchParams.value.keywords,
      location: searchParams.value.location,
      limit: searchParams.value.limit
    })
    
    const url = `http://localhost:8888/api/pole-emploi/search?${params}`
    const response = await fetch(url)
    
    let data
    try {
      data = await response.json()
    } catch (jsonError) {
      const text = await response.text()
      data = { error: 'Réponse non-JSON', details: text }
    }
    
    results.value.search = {
      success: response.ok && data.success,
      message: data.success ? 'Recherche réussie !' : `Erreur ${response.status}: ${data.message || 'Erreur inconnue'}`,
      data: data.success ? data.data : null,
      error: !response.ok ? JSON.stringify({ status: response.status, data }, null, 2) : null
    }
  } catch (error) {
    results.value.search = {
      success: false,
      message: 'Erreur de connexion',
      error: error.message
    }
  } finally {
    testing.value.search = false
  }
}

const testNoResults = async () => {
  testing.value.noResults = true
  try {
    const params = new URLSearchParams({
      page: '1',
      keywords: 'xyznbvcxz123456impossible',
      location: '75001',
      limit: '5'
    })
    
    const url = `http://localhost:8888/api/pole-emploi/search?${params}`
    const response = await fetch(url)
    
    let data
    try {
      data = await response.json()
    } catch (jsonError) {
      const text = await response.text()
      data = { error: 'Réponse non-JSON', details: text }
    }
    
    results.value.noResults = {
      success: response.ok && data.success,
      message: data.success ? 
        `Recherche réussie - ${data.data?.jobs?.length || 0} résultats trouvés` : 
        `Erreur ${response.status}: ${data.message || 'Erreur inconnue'}`,
      data: data.success ? data.data : null,
      error: !response.ok ? JSON.stringify({ status: response.status, data }, null, 2) : null
    }
  } catch (error) {
    results.value.noResults = {
      success: false,
      message: 'Erreur de connexion',
      error: error.message
    }
  } finally {
    testing.value.noResults = false
  }
}

const testProblemUrl = async () => {
  testing.value.problemUrl = true
  try {
    const response = await fetch(problemUrl.value)
    
    let data
    try {
      data = await response.json()
    } catch (jsonError) {
      const text = await response.text()
      data = { error: 'Réponse non-JSON', content: text.substring(0, 1000) + '...' }
    }
    
    results.value.problemUrl = {
      success: response.ok && data.success,
      message: response.ok ? 'URL testée avec succès' : `Erreur ${response.status}`,
      error: !response.ok ? JSON.stringify({ 
        status: response.status, 
        statusText: response.statusText,
        data 
      }, null, 2) : null
    }
  } catch (error) {
    results.value.problemUrl = {
      success: false,
      message: 'Erreur lors du test de l\'URL',
      error: error.message
    }
  } finally {
    testing.value.problemUrl = false
  }
}

// Métadonnées
definePageMeta({
  layout: 'dashboard',
  title: 'Test API France Travail'
})
</script>
