<template>
  <div class="mb-4">
    <div class="flex items-center justify-between bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-4">
      <div class="flex items-center gap-3">
        <div class="bg-blue-100 p-2 rounded-full">
          <HelpCircle class="h-5 w-5 text-blue-600" />
        </div>
        <div>
          <h3 class="font-semibold text-blue-900">Besoin d'aide pour votre recherche ?</h3>
          <p class="text-sm text-blue-700">
            Découvrez comment utiliser efficacement nos filtres de recherche
          </p>
        </div>
      </div>
      
      <div class="flex gap-2">
        <Button 
          @click="$router.push('/dashboard/jobs/guide')" 
          variant="outline" 
          size="sm"
          class="border-blue-300 text-blue-700 hover:bg-blue-50"
        >
          <BookOpen class="h-4 w-4 mr-2" />
          Guide complet
        </Button>
        
        <Button 
          @click="showQuickTips = !showQuickTips" 
          variant="ghost" 
          size="sm"
          class="text-blue-700 hover:bg-blue-50"
        >
          <Info class="h-4 w-4 mr-2" />
          Conseils rapides
        </Button>
      </div>
    </div>

    <!-- Conseils rapides (collapsible) -->
    <div v-if="showQuickTips" class="mt-3 bg-white border border-gray-200 rounded-lg p-4 space-y-3">
      <h4 class="font-medium text-gray-900 flex items-center gap-2">
        <Lightbulb class="h-4 w-4 text-yellow-500" />
        Conseils rapides
      </h4>
      
      <div class="grid gap-3 md:grid-cols-2">
        <div class="space-y-2">
          <div class="flex items-start gap-2">
            <Search class="h-4 w-4 text-blue-500 mt-0.5 flex-shrink-0" />
            <div>
              <p class="text-sm font-medium">Mots-clés efficaces</p>
              <p class="text-xs text-muted-foreground">
                Utilisez des termes spécifiques : "développeur React" plutôt que "informatique"
              </p>
            </div>
          </div>
          
          <div class="flex items-start gap-2">
            <MapPin class="h-4 w-4 text-green-500 mt-0.5 flex-shrink-0" />
            <div>
              <p class="text-sm font-medium">Localisation précise</p>
              <p class="text-xs text-muted-foreground">
                Utilisez les codes INSEE : 75001 pour Paris 1er, 69001 pour Lyon 1er
              </p>
            </div>
          </div>
        </div>
        
        <div class="space-y-2">
          <div class="flex items-start gap-2">
            <Target class="h-4 w-4 text-purple-500 mt-0.5 flex-shrink-0" />
            <div>
              <p class="text-sm font-medium">Combinez les filtres</p>
              <p class="text-xs text-muted-foreground">
                Secteur + Type de contrat + Expérience pour des résultats ciblés
              </p>
            </div>
          </div>
          
          <div class="flex items-start gap-2">
            <Zap class="h-4 w-4 text-orange-500 mt-0.5 flex-shrink-0" />
            <div>
              <p class="text-sm font-medium">Débutez large</p>
              <p class="text-xs text-muted-foreground">
                Commencez avec peu de filtres, puis affinez selon les résultats
              </p>
            </div>
          </div>
        </div>
      </div>
      
      <div class="flex items-center gap-2 pt-2 border-t">
        <Badge variant="secondary" class="text-xs">
          💡 Astuce du jour
        </Badge>
        <p class="text-xs text-muted-foreground">
          Les offres France Travail sont mises à jour en temps réel. Revenez régulièrement !
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { 
  HelpCircle, BookOpen, Info, Lightbulb, Search, 
  MapPin, Target, Zap 
} from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'

const showQuickTips = ref(false)
</script>
