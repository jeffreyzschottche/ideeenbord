<template>
  <div class="overflow-x-hidden">
    <HomeBoard />

    <!-- In de container -->
    <div class="mx-auto container px-4">
      <div v-reveal><HomeAboutSnippet /></div>
      <div v-reveal><HomeData /></div>
      <div v-reveal><HomeHowTo /></div>
    </div>

    <!-- Merken-slider op getinte band -->
    <div class="bg-[var(--color-bg)] px-4" v-reveal><HomeBrandSlider /></div>

    <!-- Full-width band -->
    <div v-reveal><HomeCurrentActions /></div>

    <div class="mx-auto container px-4">
      <div v-reveal><HomeVideo /></div>
    </div>

    <!-- Full-width -->
    <div v-reveal><HomeOptionsGrid /></div>

    <!-- Closing CTA band -->
    <section class="relative overflow-hidden bg-[var(--color-nav)] text-white">
      <div class="absolute -top-20 -right-20 w-72 h-72 rounded-full bg-[var(--color-brand)]/25 blur-3xl"></div>
      <div class="absolute -bottom-24 -left-20 w-72 h-72 rounded-full bg-[var(--color-brand)]/15 blur-3xl"></div>
      <div class="container mx-auto px-4 py-16 md:py-24 text-center relative">
        <i class="fa-solid fa-lightbulb text-4xl text-[var(--color-brand)] lamp-glow mb-4"></i>
        <h2 class="text-3xl md:text-5xl font-extrabold leading-tight max-w-3xl mx-auto">
          Klaar om je stem te laten horen?
        </h2>
        <p class="mt-4 text-lg text-gray-300 max-w-xl mx-auto">
          Maak gratis een account aan en deel vandaag nog je eerste idee met je favoriete merk.
        </p>
        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
          <NuxtLink to="/register" class="cta px-7 py-3.5 text-base">Gratis aanmelden</NuxtLink>
          <NuxtLink to="/brands" class="px-7 py-3.5 rounded-xl border-2 border-white/30 hover:bg-white/10 font-bold transition">
            Ontdek merken
          </NuxtLink>
        </div>
      </div>
    </section>
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
