export default defineNuxtPlugin(() => {
  // Vérifie si Matomo est déjà initialisé
  if (typeof window !== 'undefined') {
    // Initialise _paq si ce n'est pas déjà fait
    if (!window._paq) {
      window._paq = window._paq || [];
    }

    // Configure les paramètres par défaut
    window._paq.push(['requireCookieConsent']);
    window._paq.push(['setDocumentTitle', document.domain + '/' + document.title]);
    window._paq.push(['setCookieDomain', '*.tandem.com']); // Remplacez par votre domaine
    window._paq.push(['setDomains', ['*.tandem.com']]); // Remplacez par votre domaine
  }
})
