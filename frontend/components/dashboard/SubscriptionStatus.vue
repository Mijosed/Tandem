<template>
  <Card class="mb-6" :class="subscriptionClass">
    <CardContent class="p-4">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <div class="p-2 rounded-full" :class="iconClass">
            <Crown v-if="isPremium" class="h-5 w-5" />
            <User v-else class="h-5 w-5" />
          </div>
          
          <div>
            <h3 class="font-semibold text-gray-900">
              {{ planName }}
            </h3>
            <p class="text-sm text-gray-600">
              {{ statusMessage }}
            </p>
          </div>
        </div>
        
        <div v-if="!isPremium">
          <Button 
            class="bg-blue-600 hover:bg-blue-700"
            @click="navigateTo('/premium')"
          >
            <Crown class="mr-2 h-4 w-4" />
            Passer à Premium
          </Button>
        </div>
      </div>
    </CardContent>
  </Card>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Crown, User } from 'lucide-vue-next'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { useStripe } from '@/composables/useStripe'

// State
const subscriptionStatus = ref({
  plan: 'free',
  status: 'inactive',
  isPremium: false,
  currentPeriodEnd: null
})

// Composables
const { getSubscriptionStatus } = useStripe()

// Computed
const isPremium = computed(() => subscriptionStatus.value.isPremium)

const planName = computed(() => {
  return isPremium.value ? 'Premium' : 'Gratuit'
})

const statusMessage = computed(() => {
  if (isPremium.value) {
    if (subscriptionStatus.value.currentPeriodEnd) {
      const endDate = new Date(subscriptionStatus.value.currentPeriodEnd)
      return `Actif jusqu'au ${endDate.toLocaleDateString('fr-FR')}`
    }
    return 'Abonnement actif'
  }
  return 'Accès limité - Passez à Premium pour accéder aux offres d\'emploi'
})

const subscriptionClass = computed(() => {
  return isPremium.value 
    ? 'border-yellow-200 bg-gradient-to-r from-yellow-50 to-orange-50' 
    : 'border-gray-200 bg-gray-50'
})

const iconClass = computed(() => {
  return isPremium.value
    ? 'bg-yellow-100 text-yellow-600'
    : 'bg-gray-100 text-gray-600'
})

// Methods
const loadSubscriptionStatus = async () => {
  try {
    const userData = typeof window !== 'undefined' ? JSON.parse(localStorage.getItem('user') || '{}') : {}
    const userId = userData.id
    
    if (userId) {
      const status = await getSubscriptionStatus(userId)
      subscriptionStatus.value = status
    }
  } catch (error) {
    console.warn('Impossible de charger le statut de l\'abonnement:', error)
    // Garder les valeurs par défaut
  }
}

// Lifecycle
onMounted(() => {
  loadSubscriptionStatus()
})
</script>
