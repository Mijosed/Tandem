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
    // "@openmost/nuxt-matomo", // Désactivé temporairement
  ],
  css: ["~/assets/css/main.css"],
  build: {
    transpile: ["cookie"],
  },
  devServer: {
    host: "0.0.0.0",
    port: 3000,
  },
  // // ✅ Configuration Matomo avec @openmost/nuxt-matomo - Désactivé temporairement
  // matomo: {
  //   host: "https://tandemssocial.matomo.cloud/",
  //   siteId: 1,
  //   enableLinkTracking: true,
  //   trackPageView: true,
  //   debug: false,
  //   verbose: false,
  //   cookies: true,
  //   consentRequired: false,
  //   doNotTrack: false,
  // },
  runtimeConfig: {
    public: {
      // 🔽 Ajout pour que Nuxt parle au backend Symfony
      apiBase: process.env.API_BASE || "https://tandems.social",
      stripe: {
        publishableKey: process.env.STRIPE_PUBLISHABLE_KEY,
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
