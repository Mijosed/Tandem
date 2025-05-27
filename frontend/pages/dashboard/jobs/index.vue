<template>
<div class="container max-w-7xl mx-auto p-4 space-y-6">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold">Offres d'emploi</h1>
      <p class="text-sm text-muted-foreground mt-1">
        Trouvez les meilleures opportunités en alternance
      </p>
    </div>
  </div>
  <!-- Section Premium Lock -->
  <Card v-if="!isPremium" class="bg-gradient-to-br from-amber-50/80 to-yellow-50/80 border-amber-200/50">
    <CardHeader>
      <CardTitle class="flex items-center gap-2 text-amber-900">
        <Crown class="h-5 w-5 text-amber-500" />
        Accédez à toutes les offres d'emploi
      </CardTitle>
      <CardDescription class="text-amber-800">
        Débloquez notre moteur de recherche d'emploi premium pour trouver les meilleures opportunités
        en alternance, avec un accès direct aux offres Indeed et bien plus encore.
      </CardDescription>
    </CardHeader>
    <CardContent class="flex flex-col sm:flex-row gap-4 items-center">
      <Button 
        class="bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-600 hover:to-yellow-600 text-white border-0"
        size="lg"
      >
        Passer à l'offre Premium
      </Button>
      <span class="text-sm text-amber-700">
        Accès illimité aux offres d'emploi
      </span>
    </CardContent>
  </Card>

  <template v-else>
    <!-- Barre de recherche -->
    <div class="flex flex-col sm:flex-row gap-4">
      <div class="flex-1">
        <div class="relative">
          <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
          <Input
            v-model="searchQuery"
            placeholder="Rechercher par poste, entreprise ou lieu..."
            class="pl-9"
            @keyup.enter="searchJobs"
          />
        </div>
      </div>
      <div class="flex gap-2">
        <Button variant="outline" class="w-full sm:w-auto">
          <Filter class="h-4 w-4 mr-2" />
          Filtres
        </Button>
        <Button 
          @click="searchJobs" 
          :disabled="isLoading"
          class="w-full sm:w-auto"
        >
          <Loader2 
            v-if="isLoading" 
            class="h-4 w-4 mr-2 animate-spin" 
          />
          <Search v-else class="h-4 w-4 mr-2" />
          Rechercher
        </Button>
      </div>
    </div>

    <!-- Résultats -->
    <div v-if="jobs.length" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
      <JobCard
        v-for="job in jobs"
        :key="job.id"
        :job="job"
      />
    </div>

    <!-- État vide -->
    <Card v-else-if="!isLoading" class="text-center py-12">
      <CardContent>
        <p class="text-muted-foreground">
          Aucune offre d'emploi ne correspond à votre recherche.
          <br>
          Essayez de modifier vos critères de recherche.
        </p>
      </CardContent>
    </Card>
  </template>
</div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'dashboard',
})

import { ref, onMounted } from 'vue'
import { Search, Filter, Loader2, Crown } from 'lucide-vue-next'
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import JobCard from '@/components/jobs/JobCard.vue'

// Simulation de l'état premium(à connecter avec le vrai état utilisateur)
const isPremium = ref(false)

// États de recherche
const searchQuery = ref('')
const isLoading = ref(false)
const jobs = ref([
  {
    id: '1',
    title: 'Développeur Full Stack en Alternance',
    company: 'Innovative Tech Solutions',
    location: 'Paris (75)',
    type: 'Alternance',
    salary: '1250€/mois',
    description: 'Rejoignez notre équipe dynamique en tant que développeur full stack en alternance. Stack technique : Vue.js, Node.js, PostgreSQL. Vous participerez au développement de solutions innovantes pour nos clients.',
    postedDate: '2025-05-27',
    sourceUrl: 'https://example.com/job1'
  },
  {
    id: '2',
    title: 'Alternance Data Engineer',
    company: 'DataCorp Solutions',
    location: 'Lyon (69)',
    type: 'Alternance',
    salary: '1400€/mois',
    description: 'Nous recherchons un(e) alternant(e) Data Engineer passionné(e) pour rejoindre notre équipe. Vous travaillerez sur des projets Big Data utilisant les technologies les plus récentes.',
    postedDate: '2025-05-26',
    sourceUrl: 'https://example.com/job2'
  }
])

const searchJobs = async () => {
  if (!searchQuery.value.trim()) return
  
  isLoading.value = true
  try {
    // Simuler un appel API à Indeed
    await new Promise(resolve => setTimeout(resolve, 1500))
    // En production : appel à l'API avec mise en cache des résultats
    isLoading.value = false
  } catch (error) {
    console.error('Erreur lors de la recherche:', error)
    isLoading.value = false
  }
}

onMounted(() => {
  if (isPremium.value) {
    searchJobs()
  }
})
</script>