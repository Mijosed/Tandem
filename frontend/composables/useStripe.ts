import { loadStripe } from '@stripe/stripe-js'

let stripePromise: any = null

export const useStripe = () => {
  const getStripe = async () => {
    if (!stripePromise) {
      // Récupérer la clé publique depuis l'API
      const response = await fetch('http://localhost:8888/api/stripe/config')
      const { publishableKey } = await response.json()
      
      stripePromise = loadStripe(publishableKey)
    }
    return stripePromise
  }

  const createPaymentIntent = async (plan: 'monthly' | 'yearly' = 'monthly') => {
    try {
      const userData = typeof window !== 'undefined' ? JSON.parse(localStorage.getItem('user') || '{}') : {}
      const userId = userData.id || null

      if (!userId) {
        throw new Error('Utilisateur non trouvé')
      }

      const response = await fetch('http://localhost:8888/api/stripe/create-payment-intent', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-User-ID': userId.toString()
        },
        body: JSON.stringify({ plan })
      })

      if (!response.ok) {
        const error = await response.json()
        throw new Error(error.error || 'Erreur lors de la création du paiement')
      }

      return await response.json()
    } catch (error) {
      console.error('Erreur createPaymentIntent:', error)
      throw error
    }
  }

  const getSubscriptionStatus = async (userId: number) => {
    try {
      const response = await fetch(`http://localhost:8888/api/stripe/subscription-status/${userId}`)
      
      if (!response.ok) {
        throw new Error('Erreur lors de la récupération du statut')
      }

      return await response.json()
    } catch (error) {
      console.error('Erreur getSubscriptionStatus:', error)
      throw error
    }
  }

  return {
    getStripe,
    createPaymentIntent,
    getSubscriptionStatus
  }
}
