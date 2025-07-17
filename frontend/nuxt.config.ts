// https://nuxt.com/docs/api/configuration/nuxt-config
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
  ],
  css: ["~/assets/css/main.css"],
  build: {
    transpile: ["cookie"],
  },
  devServer: {
    host: "0.0.0.0",
    port: 3000,
  },
  nitro: {
    devProxy: {
      '/api': {
        target: 'http://traefik/api',
        changeOrigin: true,
        prependPath: true,
      }
    }
  },
  runtimeConfig: {
    public: {
      // 🔽 Ajout pour que Nuxt parle au backend Symfony
      apiBase: process.env.API_BASE || "http://localhost:8888/api",
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
