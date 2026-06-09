<template>
  <div class="pt-6">
    <HomeBoard />
    <!-- <HomeSlider v-if="content" :content="content" class="mt-14" /> -->
    <div class="mx-auto container pl-2 pr-2 md:pl-0 md:pr-0">
      <HomeAboutSnippet />
      <HomeData />
      <HomeHowTo />
      <HomeBrandSlider />
      <HomeCurrentActions />
      <HomeVideo />
      <HomeOptionsGrid />
    </div>
  </div>
</template>

<script setup lang="ts">
usePageSeo({
  title: "Deel je ideeën met merken",
  description:
    "Op Ideeënbord deel je ideeën, wensen en verbeterpunten met merken — en merken luisteren écht. Plaats een idee, stem mee en maak kans op prijzen.",
});
useJsonLd(breadcrumbLd([{ name: "Home", path: "/" }]));
definePageMeta({
  layout: "home",
});
import { useCmsContent } from "~/composables/content/useCmsContent";

const { content, isLoading, error } = useCmsContent("home");
</script>
