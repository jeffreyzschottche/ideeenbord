import tailwindcss from "@tailwindcss/vite";

// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: "2025-06-01",
  devtools: { enabled: true },
  typescript: {
    // Type-checking runs separately (CI / `npm run typecheck`) so a latent
    // type error never blocks a production build/deploy.
    typeCheck: false,
    strict: false,
  },
  css: [
    "~/assets/css/main.css",
    "@fortawesome/fontawesome-free/css/all.min.css",
  ],
  vite: {
    plugins: [tailwindcss()],
  },
  modules: ["@pinia/nuxt", "@nuxtjs/google-fonts"],
  googleFonts: {
    families: {
      Poppins: {
        wght: [400, 500, 600, 700, 800],
        ital: [400, 700],
      },
      Roboto: {
        wght: [300, 400, 500, 700],
        ital: [400],
      },
    },
    display: "swap",
    download: true,
    inject: true,
  },
  runtimeConfig: {
    public: {
      apiBaseUrl: process.env.NUXT_PUBLIC_API_BASE_URL,
      siteUrl: process.env.NUXT_PUBLIC_SITE_URL || "https://ideeenbord.nl",
      // Google AdSense — alleen tonen op publieke pagina's wanneer ingeschakeld.
      adsenseEnabled: process.env.NUXT_PUBLIC_ADSENSE_ENABLED || "0", // "1" = tonen, "0" = niet
      adsenseClient: process.env.NUXT_PUBLIC_ADSENSE_CLIENT || "", // bv. ca-pub-1234567890123456
      adsenseSlot: process.env.NUXT_PUBLIC_ADSENSE_SLOT || "", // standaard ad-slot id
    },
  },
  app: {
    head: {
      htmlAttrs: { lang: "nl" },
      viewport: "width=device-width, initial-scale=1",
      title: "Ideeënbord — deel je ideeën met merken",
      titleTemplate: "%s · Ideeënbord",
      link: [
        { rel: "icon", type: "image/x-icon", href: "/favicon.ico" },
        { rel: "icon", type: "image/png", href: "/favicon-ideeenbord.png" },
        { rel: "apple-touch-icon", href: "/favicon-ideeenbord.png" },
      ],
      meta: [
        {
          name: "description",
          content:
            "Ideeënbord is hét platform waar consumenten ideeën, wensen en verbeterpunten delen met merken — en waar merken écht luisteren.",
        },
      ],
    },
  },
});
