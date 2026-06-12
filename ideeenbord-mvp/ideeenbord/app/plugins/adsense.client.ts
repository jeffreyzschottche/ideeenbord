// Laadt het Google AdSense-script eenmalig, maar alleen wanneer
// NUXT_PUBLIC_ADSENSE_ENABLED === "1" én er een publisher-id is ingesteld.
export default defineNuxtPlugin(() => {
  const cfg = useRuntimeConfig().public;

  if (String(cfg.adsenseEnabled) !== "1" || !cfg.adsenseClient) {
    return;
  }

  useHead({
    script: [
      {
        src: `https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=${cfg.adsenseClient}`,
        async: true,
        crossorigin: "anonymous",
      },
    ],
  });
});
