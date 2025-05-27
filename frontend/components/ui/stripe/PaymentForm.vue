<template>
  <form @submit.prevent="handleSubmit" id="payment-form" class="space-y-4">
    <div class="space-y-2">
      <Label>Informations de paiement</Label>
      <div id="payment-element" class="p-3 border rounded-lg"></div>
    </div>

    <div v-if="errorMessage" class="text-sm text-red-500">
      {{ errorMessage }}
    </div>

    <Button 
      type="submit" 
      class="w-full" 
      :disabled="isLoading"
    >
      <Loader2 v-if="isLoading" class="mr-2 h-4 w-4 animate-spin" />
      {{ isLoading ? 'Traitement en cours...' : 'Payer maintenant' }}
    </Button>
  </form>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { loadStripe } from '@stripe/stripe-js'
import { Button } from '~/components/ui/button'
import { Label } from '~/components/ui/label'
import { Loader2 } from 'lucide-vue-next'

const props = defineProps<{
  clientSecret: string
  successUrl: string
  amount: number
}>()

const emit = defineEmits<{
  (e: 'success'): void
  (e: 'error', error: string): void
}>()

const stripe = ref<any>(null)
const elements = ref<any>(null)
const isLoading = ref(false)
const errorMessage = ref('')

onMounted(async () => {
  // Initialisation de Stripe
  stripe.value = await loadStripe(process.env.STRIPE_PUBLIC_KEY || '')
  
  const appearance = {
    theme: 'stripe',
    variables: {
      colorPrimary: '#0366d6',
    },
  }

  // Création des éléments de paiement
  elements.value = stripe.value.elements({
    appearance,
    clientSecret: props.clientSecret
  })

  // Montage de l'élément de paiement
  const paymentElement = elements.value.create('payment')
  paymentElement.mount('#payment-element')
})

const handleSubmit = async () => {
  if (!stripe.value || !elements.value) return

  isLoading.value = true
  errorMessage.value = ''

  try {
    const { error } = await stripe.value.confirmPayment({
      elements: elements.value,
      confirmParams: {
        return_url: props.successUrl,
      },
    })

    if (error) {
      errorMessage.value = error.message || 'Une erreur est survenue lors du paiement.'
      emit('error', error.message)
    } else {
      emit('success')
    }
  } catch (e) {
    errorMessage.value = 'Une erreur inattendue est survenue.'
    emit('error', 'Une erreur inattendue est survenue.')
  } finally {
    isLoading.value = false
  }
}
</script>
