// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  devtools: { enabled: true },
  app: {
    head: {
      title: 'Tandem',
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        { hid: 'description', name: 'description', content: 'Application Tandem' }
      ]
    }
  },
  css: [
    // Vous pouvez ajouter des fichiers CSS globaux ici
  ],
  modules: [
    // Ajoutez vos modules Nuxt ici
  ],
  runtimeConfig: {
    public: {
      apiBase: process.env.API_BASE || 'http://localhost/api'
    }
  },
  // Configuration du proxy pour faciliter les requêtes API
  vite: {
    server: {
      cors: true,
      proxy: {
        '/api': {
          target: 'http://localhost',
          changeOrigin: true
        }
      }
    }
  }
})
