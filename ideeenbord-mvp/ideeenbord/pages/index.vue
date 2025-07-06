<template>
  <div class="pt-6">
    <HomeSlider v-if="content" :content="content" class="mt-14" />
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
definePageMeta({
  layout: "home",
});
import { useCmsContent } from "~/composables/content/useCmsContent";

const { content, isLoading, error } = useCmsContent("home");
</script>
