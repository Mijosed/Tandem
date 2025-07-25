export default defineNuxtRouteMiddleware((to) => {
  // Track page views automatiquement
  if (typeof window !== 'undefined' && window._paq) {
    // Attendre que la page soit complètement chargée
    nextTick(() => {
      window._paq.push(['setCustomUrl', to.fullPath]);
      window._paq.push(['setDocumentTitle', document.title]);
      window._paq.push(['trackPageView']);
    });
  }
})
