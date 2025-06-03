import tailwindcss from "@tailwindcss/vite";

// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  devtools: { enabled: true },
  app: {
    head: {
      title: "Tandem",
      meta: [
        { charset: "utf-8" },
        { name: "viewport", content: "width=device-width, initial-scale=1" },
        {
          name: "description",
          content: "Application Tandem",
        },
      ],
    },
    layoutTransition: true,
  },
  css: ["/assets/css/main.css"],
  modules: [
    "@nuxtjs/tailwindcss",
    "shadcn-nuxt",
    "nuxt-aos",
    "@pinia/nuxt",
  ],
  shadcn: {
    prefix: "",
    componentDir: "./components/ui",
  },
  runtimeConfig: {
    public: {
      apiBaseUrl: process.env.API_BASE_URL || "http://localhost/api",
    },
  },
  // Configuration du proxy pour faciliter les requêtes API
  vite: {
    plugins: [tailwindcss()],
    server: {
      cors: true,
      proxy: {
        "/api": {
          target: "http://backend:8000",
          changeOrigin: true,
        },
      },
      watch: {
        usePolling: true
      },
      hmr: {
        protocol: 'ws',
        host: '0.0.0.0',
        port: 24678,
      }
    },
    optimizeDeps: {
      include: [
        'class-variance-authority',
        'clsx',
        'tailwind-merge',
        'lucide-vue-next'
      ]
    }
  },
  nitro: {
    preset: 'node'
  },
  aos: {
    disable: false,
    startEvent: "DOMContentLoaded",
    initClassName: "aos-init",
    animatedClassName: "aos-animate",
    useClassNames: false,
    disableMutationObserver: false,
    debounceDelay: 50,
    throttleDelay: 99,
    offset: 120,
    delay: 0,
    duration: 400,
    easing: "ease",
    once: false,
    mirror: false,
    anchorPlacement: "top-bottom",
  },
  devServer: {
    host: '0.0.0.0',
    port: 3000
  }
});