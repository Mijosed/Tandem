import { loadStripe } from '@stripe/stripe-js'

let stripePromise: any = null

export const useStripe = () => {
  const config = useRuntimeConfig()
  const getJwtHeaders = () => {
    const token = typeof window !== 'undefined' ? localStorage.getItem('jwt') : null
    return token ? { Authorization: `Bearer ${token}` } : {}
  }
  const getStripe = async () => {
    if (!stripePromise) {
      const response = await fetch('${config.public.apiBase}/api/stripe/config', {
        headers: {
          ...getJwtHeaders(),
          'Content-Type': 'application/json',
        }
      })
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

      const response = await fetch('${config.public.apiBase}/api/stripe/create-payment-intent', {
        method: 'POST',
        headers: {
          ...getJwtHeaders(),
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
      const response = await fetch(`${config.public.apiBase}/api/stripe/subscription-status/${userId}`, {
        headers: {
          ...getJwtHeaders(),
          'Content-Type': 'application/json',
        }
      })
      
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
