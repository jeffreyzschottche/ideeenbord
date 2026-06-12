<script setup lang="ts">
/*
  Google AdSense banner. Rendert alleen wanneer AdSense is ingeschakeld
  (NUXT_PUBLIC_ADSENSE_ENABLED="1") en er een publisher-id is ingesteld.
  Gebruik:  <AdBanner />                  (gebruikt de standaard slot uit env)
            <AdBanner ad-slot="1234567890" />
*/
import { computed, onMounted } from "vue";

const props = withDefaults(
  defineProps<{
    adSlot?: string;
    format?: string;
    responsive?: boolean;
    label?: boolean;
  }>(),
  {
    format: "auto",
    responsive: true,
    label: true,
  }
);

const cfg = useRuntimeConfig().public;

const resolvedSlot = computed(() => props.adSlot || (cfg.adsenseSlot as string) || "");
const show = computed(
  () => String(cfg.adsenseEnabled) === "1" && !!cfg.adsenseClient && !!resolvedSlot.value
);

onMounted(() => {
  if (!show.value) return;
  try {
    // @ts-ignore — adsbygoogle wordt door het AdSense-script aangemaakt
    (window.adsbygoogle = window.adsbygoogle || []).push({});
  } catch {
    /* AdSense nog niet geladen of geblokkeerd — stil negeren */
  }
});
</script>

<template>
  <div v-if="show" class="ad-banner">
    <p v-if="label" class="ad-banner__label">Advertentie</p>
    <ins
      class="adsbygoogle"
      style="display: block"
      :data-ad-client="cfg.adsenseClient"
      :data-ad-slot="resolvedSlot"
      :data-ad-format="format"
      :data-full-width-responsive="responsive ? 'true' : 'false'"
    ></ins>
  </div>
</template>

<style scoped>
.ad-banner {
  margin: 1.5rem 0;
  text-align: center;
}
.ad-banner__label {
  font-size: 0.7rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #9ca3af;
  margin-bottom: 0.25rem;
}
</style>
