// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: "2025-05-15",
  devtools: { enabled: true },
  modules: [
    "@nuxt/image",
    "shadcn-nuxt",
    "@nuxt/icon",
    "@pinia/nuxt",
    "@nuxtjs/tailwindcss"
  ],
  css: ["~/assets/css/main.css"],
  build: {
    transpile: ["cookie"],
  },
  runtimeConfig: {
    public: {
      // 🔽 Ajout pour que Nuxt parle au backend Symfony
      apiBase: process.env.API_BASE || "http://backend:8000/api",
      stripe: {
        publishableKey: process.env.STRIPE_PUBLISHABLE_KEY,
      }
    },
  },
  shadcn: {
    prefix: "",
    componentDir: "./components/ui",
  },
  typescript: {
    strict: true
  }
})
