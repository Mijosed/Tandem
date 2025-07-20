<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-12">
    <div class="max-w-4xl mx-auto px-4">
      <!-- Header -->
      <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">
          Accédez aux meilleures opportunités
        </h1>
        <p class="text-xl text-gray-600 max-w-2xl mx-auto">
          Débloquez l'accès complet à toutes les offres d'emploi en alternance avec notre abonnement Premium
        </p>
      </div>

      <!-- Plan Premium Simple -->
      <div class="max-w-md mx-auto">
        <Card class="border-blue-200 ring-2 ring-blue-500">
          <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
            <span class="bg-blue-500 text-white px-4 py-1 rounded-full text-sm font-medium">
              Paiement sécurisé
            </span>
          </div>
          
          <CardContent class="p-8">
            <div class="text-center mb-6">
              <h3 class="text-2xl font-bold text-gray-900 mb-2">Premium</h3>
              <div class="mb-4">
                <span class="text-4xl font-bold text-blue-600">9,99€</span>
                <span class="text-gray-600">/mois</span>
              </div>
              
              <ul class="space-y-3 mb-6 text-left">
                <li class="flex items-center">
                  <Check class="h-5 w-5 text-green-500 mr-3" />
                  <span class="text-gray-700">Accès illimité aux offres</span>
                </li>
                <li class="flex items-center">
                  <Check class="h-5 w-5 text-green-500 mr-3" />
                  <span class="text-gray-700">Alertes personnalisées</span>
                </li>
                <li class="flex items-center">
                  <Check class="h-5 w-5 text-green-500 mr-3" />
                  <span class="text-gray-700">Support prioritaire</span>
                </li>
              </ul>
            </div>

            <!-- Formulaire de paiement par carte -->
            <div class="space-y-4">
              <div class="text-center">
                <p class="text-sm text-gray-600 mb-4">
                  <CreditCard class="h-4 w-4 inline mr-1" />
                  Paiement sécurisé par carte bancaire
                </p>
              </div>

              <!-- Stripe Payment Element -->
              <div v-if="showPaymentForm" id="payment-element" class="border rounded-lg p-4 bg-white"></div>
              
              <!-- Boutons d'action -->
              <div v-if="!showPaymentForm" class="space-y-3">
                <Button 
                  class="w-full bg-blue-600 hover:bg-blue-700 py-3"
                  @click="initializePayment"
                  :disabled="isProcessing"
                >
                  <Loader2 v-if="isProcessing" class="mr-2 h-4 w-4 animate-spin" />
                  <CreditCard v-else class="mr-2 h-4 w-4" />
                  Payer par carte bancaire
                </Button>
              </div>

              <div v-else class="flex space-x-3">
                <Button 
                  variant="outline" 
                  class="flex-1"
                  @click="cancelPayment"
                  :disabled="isProcessing"
                >
                  Annuler
                </Button>
                <Button 
                  class="flex-1 bg-blue-600 hover:bg-blue-700"
                  @click="confirmPayment"
                  :disabled="isProcessing"
                >
                  <Loader2 v-if="isProcessing" class="mr-2 h-4 w-4 animate-spin" />
                  Confirmer le paiement
                </Button>
              </div>

              <!-- Sécurité -->
              <div class="text-center">
                <p class="text-xs text-gray-500">
                  🔒 Paiement 100% sécurisé avec Stripe<br>
                  Vos données bancaires ne sont jamais stockées
                </p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- FAQ Section -->
      <div class="max-w-2xl mx-auto mt-16">
        <h2 class="text-2xl font-bold text-center text-gray-900 mb-8">Questions fréquentes</h2>
        
        <div class="space-y-4">
          <Card>
            <CardContent class="p-6">
              <h3 class="font-semibold text-gray-900 mb-2">Puis-je annuler mon abonnement ?</h3>
              <p class="text-gray-600">Oui, vous pouvez annuler votre abonnement à tout moment depuis votre profil.</p>
            </CardContent>
          </Card>
          
          <Card>
            <CardContent class="p-6">
              <h3 class="font-semibold text-gray-900 mb-2">Mes données de paiement sont-elles sécurisées ?</h3>
              <p class="text-gray-600">Absolument. Nous utilisons Stripe, leader mondial des paiements en ligne, pour traiter vos paiements de manière sécurisée.</p>
            </CardContent>
          </Card>
          
          <Card>
            <CardContent class="p-6">
              <h3 class="font-semibold text-gray-900 mb-2">Que se passe-t-il après le paiement ?</h3>
              <p class="text-gray-600">Votre compte Premium est activé immédiatement après le paiement réussi.</p>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'
import { Check, CreditCard, Loader2 } from 'lucide-vue-next'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { useStripe } from '@/composables/useStripe'

// State
const isProcessing = ref(false)
const showPaymentForm = ref(false)
const stripe = ref(null)
const elements = ref(null)
const clientSecret = ref('')

// Composables
const { getStripe, createPaymentIntent } = useStripe()

// Vérifier si l'utilisateur est connecté
onMounted(async () => {
  const userData = typeof window !== 'undefined' ? JSON.parse(localStorage.getItem('user') || '{}') : {}
  
  if (!userData.id) {
    await navigateTo('/login')
  }
})

const initializePayment = async () => {
  try {
    isProcessing.value = true
    
    // Créer le PaymentIntent (carte bancaire uniquement)
    const { clientSecret: secret } = await createPaymentIntent('monthly')
    clientSecret.value = secret
    
    // Initialiser Stripe
    stripe.value = await getStripe()
    
    // Créer les elements Stripe pour cartes bancaires uniquement
    elements.value = stripe.value.elements({
      clientSecret: clientSecret.value
    })
    
    showPaymentForm.value = true
    
    // Attendre que le formulaire soit affiché
    await nextTick()
    
    // Créer l'élément de paiement (cartes seulement)
    const paymentElement = elements.value.create('payment', {
      paymentMethodOrder: ['card'], // Forcer seulement les cartes
    })
    paymentElement.mount('#payment-element')
    
  } catch (error) {
    console.error('Erreur lors de l\'initialisation du paiement:', error)
    alert('Erreur lors de l\'initialisation du paiement: ' + error.message)
  } finally {
    isProcessing.value = false
  }
}

const confirmPayment = async () => {
  if (!stripe.value || !elements.value) return
  
  try {
    isProcessing.value = true
    
    const { error, paymentIntent } = await stripe.value.confirmPayment({
      elements: elements.value,
      redirect: 'if_required'
    })
    
    if (error) {
      console.error('Erreur de paiement:', error)
      alert('Erreur de paiement: ' + error.message)
    } else if (paymentIntent.status === 'succeeded') {
      alert('Paiement réussi ! Votre abonnement premium est maintenant actif.')
      
      // Rediriger vers la page des jobs
      await navigateTo('/dashboard/jobs')
    }
    
  } catch (error) {
    console.error('Erreur lors de la confirmation du paiement:', error)
    alert('Erreur lors de la confirmation du paiement: ' + error.message)
  } finally {
    isProcessing.value = false
  }
}

const cancelPayment = () => {
  showPaymentForm.value = false
  clientSecret.value = ''
  elements.value = null
}

// Meta
definePageMeta({
  layout: 'dashboard'
})
</script>
