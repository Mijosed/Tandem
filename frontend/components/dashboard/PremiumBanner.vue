<template>
  <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-6 rounded-lg shadow-lg mb-6">
    <div class="flex items-center justify-between">
      <div class="flex items-center space-x-4">
        <div class="p-3 bg-white bg-opacity-20 rounded-full">
          <Crown class="h-8 w-8" />
        </div>
        <div>
          <h3 class="text-xl font-bold mb-1">
            Débloquez votre potentiel avec Premium
          </h3>
          <p class="text-blue-100">
            Accédez à des milliers d'offres d'emploi en alternance et boostez votre carrière
          </p>
        </div>
      </div>
      
      <div class="hidden md:flex items-center space-x-4">
        <div class="text-right">
          <div class="text-2xl font-bold">9,99€</div>
          <div class="text-sm text-blue-100">par mois</div>
        </div>
        
        <Button 
          class="bg-white text-blue-600 hover:bg-gray-100 font-semibold"
          @click="navigateTo('/premium')"
        >
          Commencer maintenant
        </Button>
      </div>
      
      <div class="md:hidden">
        <Button 
          size="sm"
          class="bg-white text-blue-600 hover:bg-gray-100 font-semibold"
          @click="navigateTo('/premium')"
        >
          Premium
        </Button>
      </div>
    </div>
    
    <div class="md:hidden mt-4 pt-4 border-t border-blue-400">
      <div class="grid grid-cols-2 gap-2 text-sm">
        <div class="flex items-center">
          <Check class="h-4 w-4 mr-2" />
          <span>Offres illimitées</span>
        </div>
        <div class="flex items-center">
          <Check class="h-4 w-4 mr-2" />
          <span>Alertes personnalisées</span>
        </div>
      </div>
      <div class="mt-3 text-center">
        <span class="text-xl font-bold">9,99€/mois</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Crown, Check } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
</script>
