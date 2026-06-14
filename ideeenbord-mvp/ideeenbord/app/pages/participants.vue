<template>
  <div class="font-default">
    <ParticipantsCompetitorBanner @search="onHeroSearch" />

    <!-- Grid + filters -->
    <ParticipantsCompetitorGrid ref="gridRef" />
  </div>
</template>

<script setup lang="ts">
import { ref } from "vue";

const gridRef = ref<{ setSearchFromHero: (q: string) => void } | null>(null);

function onHeroSearch(query: string) {
  gridRef.value?.setSearchFromHero(query);
}

usePageSeo({
  title: "Deelnemers",
  description: "Bekijk de merken en deelnemers die actief zijn op Ideeënbord.",
});
useJsonLd(
  breadcrumbLd([
    { name: "Home", path: "/" },
    { name: "Deelnemers", path: "/participants" },
  ])
);
</script>
