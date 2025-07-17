export default defineNuxtConfig({
  compatibilityDate: "2025-05-15",
  devtools: { enabled: true },
  modules: [
    "@nuxt/image",
    "@nuxt/icon",
    "@pinia/nuxt",
    "@nuxtjs/tailwindcss",
    "shadcn-nuxt",
    "@nuxt/content",
    "@openmost/nuxt-matomo",
  ],
  css: ["~/assets/css/main.css"],
  build: {
    transpile: ["cookie"],
  },
  devServer: {
    host: "0.0.0.0",
    port: 3000,
  },
  runtimeConfig: {
    public: {
      // 🔽 Ajout pour que Nuxt parle au backend Symfony
      apiBase: process.env.API_BASE || "http://localhost:8888",
      stripe: {
        publishableKey: process.env.STRIPE_PUBLISHABLE_KEY,
      },
      // ✅ Matomo config pour @openmost/nuxt-matomo
      matomo: {
        host: process.env.NUXT_PUBLIC_MATOMO_HOST,
        containerId: process.env.NUXT_PUBLIC_MATOMO_CONTAINER_ID,
        debug: true,
        verbose: true,
        cookies: true,
        consentRequired: false,
        doNotTrack: false,
      },
    },
  },
  shadcn: {
    prefix: "",
    componentDir: "./components/ui",
  },
  typescript: {
    strict: false,
  },
});
