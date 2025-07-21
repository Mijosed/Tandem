<template>
  <Card class="mb-6">
    <CardHeader>
      <CardTitle class="flex items-center gap-2">
        <Filter class="h-5 w-5" />
        Recherche d'emploi
      </CardTitle>
    </CardHeader>
    
    <CardContent class="space-y-4">
      <div>
        <label for="keywords" class="block text-sm font-medium mb-2">Mots-clés</label>
        <Input
          id="keywords"
          v-model="filters.keywords"
          placeholder="développeur, marketing, vente..."
          class="text-sm"
          @keypress.enter="search"
        />
        <p class="text-xs text-muted-foreground mt-1">Ex: développeur web, commercial, assistant</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div>
          <label for="location" class="block text-sm font-medium mb-2">Localisation</label>
          <Input
            id="location"
            v-model="filters.location"
            placeholder="Code commune (ex: 75001, 69001)"
            class="text-sm"
            @keypress.enter="search"
          />
          <p class="text-xs text-muted-foreground mt-1">Utilisez le code INSEE de la commune</p>
        </div>
        
        <div>
          <label for="contractType" class="block text-sm font-medium mb-2">Type de contrat</label>
          <select
            id="contractType"
            v-model="filters.contract_type"
            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Tous les contrats</option>
            <option value="CDI">CDI - Contrat à durée indéterminée</option>
            <option value="CDD">CDD - Contrat à durée déterminée</option>
            <option value="MIS">Intérim</option>
            <option value="SAI">Saisonnier</option>
            <option value="LIB">Libéral</option>
            <option value="REP">Remplacement</option>
            <option value="FRA">Franchise</option>
          </select>
        </div>
        
        <div>
          <label for="experience" class="block text-sm font-medium mb-2">Expérience requise</label>
          <select
            id="experience"
            v-model="filters.experience"
            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Toute expérience</option>
            <option value="1">Débutant accepté</option>
            <option value="2">1 à 3 ans d'expérience</option>
            <option value="3">Plus de 3 ans d'expérience</option>
          </select>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label for="sector" class="block text-sm font-medium mb-2">Secteur d'activité</label>
          <select
            id="sector"
            v-model="filters.sector"
            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            :disabled="loadingSectors"
          >
            <option value="">Tous les secteurs</option>
            <option 
              v-for="sector in sectors" 
              :key="sector.code" 
              :value="sector.code"
            >
              {{ sector.libelle }}
            </option>
          </select>
          <p v-if="loadingSectors" class="text-xs text-muted-foreground mt-1">Chargement des secteurs...</p>
          <p v-else-if="sectors.length === 0" class="text-xs text-red-500 mt-1">Impossible de charger les secteurs</p>
        </div>

        <div>
          <label for="limit" class="block text-sm font-medium mb-2">Résultats par page</label>
          <select
            id="limit"
            v-model="filters.limit"
            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="10">10 résultats</option>
            <option value="20">20 résultats</option>
            <option value="50">50 résultats</option>
          </select>
        </div>
      </div>
      
      <div class="flex gap-3 pt-2">
        <Button 
          @click="search" 
          class="flex-1 md:flex-none"
          :disabled="loading"
        >
          <Search class="h-4 w-4 mr-2" />
          {{ loading ? 'Recherche...' : 'Rechercher' }}
        </Button>
        
        <Button
          v-if="hasActiveFilters"
          variant="outline"
          @click="clearFilters"
        >
          <X class="h-4 w-4 mr-2" />
          Effacer
        </Button>

        <Button
          variant="outline"
          @click="refreshSectors"
          :disabled="loadingSectors"
          class="ml-auto"
        >
          <RotateCcw class="h-4 w-4 mr-2" />
          Actualiser secteurs
        </Button>
      </div>

      <div v-if="hasActiveFilters" class="text-xs text-muted-foreground bg-gray-50 rounded-md p-3">
        <p class="font-medium mb-1">Filtres actifs :</p>
        <div class="space-y-1">
          <p v-if="filters.keywords"><strong>Mots-clés :</strong> {{ filters.keywords }}</p>
          <p v-if="filters.location"><strong>Localisation :</strong> {{ filters.location }}</p>
          <p v-if="filters.contract_type"><strong>Contrat :</strong> {{ getContractTypeLabel(filters.contract_type) }}</p>
          <p v-if="filters.experience"><strong>Expérience :</strong> {{ getExperienceLabel(filters.experience) }}</p>
          <p v-if="filters.sector"><strong>Secteur :</strong> {{ getSectorLabel(filters.sector) }}</p>
        </div>
      </div>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Card, CardContent, CardHeader, CardTitle } from '~/components/ui/card'
import { Input } from '~/components/ui/input'
import { Button } from '~/components/ui/button'
import { Filter, Search, X, RotateCcw } from 'lucide-vue-next'

interface Sector {
  code: string
  libelle: string
}

const props = defineProps<{
  loading?: boolean
}>()

const emit = defineEmits<{
  search: [filters: any]
}>()

const sectors = ref<Sector[]>([])
const loadingSectors = ref(false)

const filters = ref({
  keywords: '',
  location: '',
  contract_type: '',
  experience: '',
  sector: '',
  limit: '20'
})

const hasActiveFilters = computed(() => {
  return Object.values(filters.value).some(value => value !== '' && value !== '20')
})

const getContractTypeLabel = (value: string): string => {
  const types: Record<string, string> = {
    'CDI': 'CDI - Contrat à durée indéterminée',
    'CDD': 'CDD - Contrat à durée déterminée',
    'MIS': 'Intérim',
    'SAI': 'Saisonnier',
    'LIB': 'Libéral',
    'REP': 'Remplacement',
    'FRA': 'Franchise'
  }
  return types[value] || value
}

const getExperienceLabel = (value: string): string => {
  const experiences: Record<string, string> = {
    '1': 'Débutant accepté',
    '2': '1 à 3 ans d\'expérience',
    '3': 'Plus de 3 ans d\'expérience'
  }
  return experiences[value] || value
}

const getSectorLabel = (value: string): string => {
  const sector = sectors.value.find(s => s.code === value)
  return sector ? sector.libelle : value
}

const loadSectors = async () => {
  if (loadingSectors.value) return
  
  loadingSectors.value = true
  try {
    const config = useRuntimeConfig()
    const token = localStorage.getItem('jwt')
    const response = await fetch(`${config.public.apiBase}/pole-emploi/sectors`, {
      headers: {
        'Authorization': token ? `Bearer ${token}` : '',
        'Content-Type': 'application/json',
      }
    })
    
    if (!response.ok) {
      console.warn('Impossible de charger les secteurs:', response.status)
      return
    }
    
    const data = await response.json()
    
    if (data.success && Array.isArray(data.data)) {
      sectors.value = data.data.sort((a: Sector, b: Sector) => a.libelle.localeCompare(b.libelle))
      console.log(`✅ ${sectors.value.length} secteurs chargés`)
    } else {
      console.warn('Format de réponse invalide pour les secteurs')
    }
    
  } catch (error) {
    console.error('Erreur lors du chargement des secteurs:', error)
  } finally {
    loadingSectors.value = false
  }
}

const search = () => {
  const searchFilters: any = { ...filters.value }
  
  if (searchFilters.limit) {
    searchFilters.limit = parseInt(searchFilters.limit as string)
  }
  
  Object.keys(searchFilters).forEach(key => {
    if (searchFilters[key] === '') {
      delete searchFilters[key]
    }
  })
  
  emit('search', searchFilters)
}

const clearFilters = () => {
  filters.value = {
    keywords: '',
    location: '',
    contract_type: '',
    experience: '',
    sector: '',
    limit: '20'
  }
  search()
}

const refreshSectors = () => {
  sectors.value = []
  loadSectors()
}

onMounted(() => {
  loadSectors()
})
</script>