/**
 * Composable pour gérer le tracking Matomo
 */
export const useMatomo = () => {
  /**
   * Fonction pour tracker un événement
   * @param category Catégorie de l'événement (ex: 'Navigation', 'CTA', 'Form')
   * @param action Action effectuée (ex: 'Click', 'Submit', 'View')
   * @param name Nom de l'élément (ex: 'Login Button', 'Register Button')
   * @param value Valeur optionnelle (nombre)
   */
  const trackEvent = (category: string, action: string, name?: string, value?: number) => {
    if (typeof window !== 'undefined' && window._paq) {
      const eventData: (string | number)[] = ['trackEvent', category, action];
      if (name) eventData.push(name);
      if (value !== undefined) eventData.push(value);
      
      window._paq.push(eventData);
    }
  };

  /**
   * Fonction pour tracker un clic sur un bouton
   * @param buttonName Nom du bouton cliqué
   * @param location Localisation du bouton (ex: 'Header', 'Hero', 'Footer')
   */
  const trackButtonClick = (buttonName: string, location: string = 'Unknown') => {
    trackEvent('Button Click', 'Click', `${buttonName} - ${location}`);
  };

  /**
   * Fonction pour tracker une navigation
   * @param destination Destination de la navigation
   * @param source Source de la navigation
   */
  const trackNavigation = (destination: string, source: string = 'Unknown') => {
    trackEvent('Navigation', 'Click', `${source} to ${destination}`);
  };

  /**
   * Fonction pour tracker une action CTA (Call To Action)
   * @param ctaName Nom du CTA
   * @param location Localisation du CTA
   */
  const trackCTA = (ctaName: string, location: string = 'Unknown') => {
    trackEvent('CTA', 'Click', `${ctaName} - ${location}`);
  };

  return {
    trackEvent,
    trackButtonClick,
    trackNavigation,
    trackCTA
  };
};

// Déclaration TypeScript pour window._paq
declare global {
  interface Window {
    _paq: (string | number)[][];
  }
}
