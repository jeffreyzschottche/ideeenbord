<template>
  <div class="font-default">
    <ParticipantsCompetitorBanner />

    <!-- Grid + filters -->
    <ParticipantsCompetitorGrid />
  </div>
</template>

<script setup lang="ts">
// puur compositie – componenten verzorgen eigen data
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
