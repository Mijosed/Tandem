/**
 * Composable pour gérer le tracking Matomo avec @openmost/nuxt-matomo
 */
export const useMatomo = () => {
  const { $matomo } = useNuxtApp()

  /**
   * Fonction pour tracker un événement
   * @param category Catégorie de l'événement (ex: 'Navigation', 'CTA', 'Form')
   * @param action Action effectuée (ex: 'Click', 'Submit', 'View')
   * @param name Nom de l'élément (ex: 'Login Button', 'Register Button')
   * @param value Valeur optionnelle (nombre)
   */
  const trackEvent = (category: string, action: string, name?: string, value?: number) => {
    try {
      if ($matomo && typeof ($matomo as any).trackEvent === 'function') {
        ($matomo as any).trackEvent(category, action, name, value)
      }
    } catch (error) {
      console.warn('Erreur tracking Matomo:', error)
    }
  }

  /**
   * Fonction pour tracker un clic sur un bouton
   * @param buttonName Nom du bouton cliqué
   * @param location Localisation du bouton (ex: 'Header', 'Hero', 'Footer')
   */
  const trackButtonClick = (buttonName: string, location: string = 'Unknown') => {
    trackEvent('Button Click', 'Click', `${buttonName} - ${location}`)
  }

  /**
   * Fonction pour tracker une navigation
   * @param destination Destination de la navigation
   * @param source Source de la navigation
   */
  const trackNavigation = (destination: string, source: string = 'Unknown') => {
    trackEvent('Navigation', 'Click', `${source} to ${destination}`)
  }

  /**
   * Fonction pour tracker une action CTA (Call To Action)
   * @param ctaName Nom du CTA
   * @param location Localisation du CTA
   */
  const trackCTA = (ctaName: string, location: string = 'Unknown') => {
    trackEvent('CTA', 'Click', `${ctaName} - ${location}`)
  }

  /**
   * Fonction pour tracker une page vue personnalisée
   * @param customTitle Titre personnalisé de la page
   */
  const trackPageView = (customTitle?: string) => {
    try {
      if ($matomo && typeof ($matomo as any).trackPageView === 'function') {
        ($matomo as any).trackPageView(customTitle)
      }
    } catch (error) {
      console.warn('Erreur tracking page view Matomo:', error)
    }
  }

  return {
    trackEvent,
    trackButtonClick,
    trackNavigation,
    trackCTA,
    trackPageView
  }
}
