<template>
  <section class="light-bg w-full py-12">
    <div
      class="container mx-auto flex flex-col md:flex-row items-center gap-10 px-6"
    >
      <!-- tekst -->
      <div class="md:w-1/2 space-y-4">
        <h1 class="text-4xl md:text-5xl font-bold dark-text">
          {{ c["banner-title"] }}
        </h1>
        <p class="text-lg main-text">
          {{ c["banner-paragraph"] }}
        </p>
      </div>

      <!-- image -->
      <div class="md:w-1/2">
        <img
          v-if="c['banner-image']"
          :src="correctImageUrl(c['banner-image'])"
          class="w-full rounded-lg shadow-lg"
          :alt="c['banner-title']"
        />
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { useCmsContent } from "~/composables/content/useCmsContent";
import { useRuntimeConfig } from "nuxt/app";

const { content: c } = useCmsContent("deelnemers");

function correctImageUrl(p?: string) {
  if (!p) return "";
  if (p.startsWith("http") || p.startsWith("//") || p.startsWith("/img"))
    return p;
  const base = (useRuntimeConfig().public.apiBaseUrl as string).replace(
    "/api",
    "/storage"
  );
  return `${base}/${p.replace(/^\/+/, "")}`;
}
</script>
