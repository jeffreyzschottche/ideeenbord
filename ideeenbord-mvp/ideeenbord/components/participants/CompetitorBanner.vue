<template>
  <section class="font-default py-12">
    <div class="max-w-4xl mx-auto text-center space-y-6 px-4">
      <!-- image boven tekst -->
      <img
        v-if="c['banner-image']"
        :src="correctImageUrl(c['banner-image'])"
        class="mx-auto w-full max-w-md rounded-xl shadow"
        :alt="c['banner-title']"
      />

      <h1 class="text-3xl md:text-4xl font-bold dark-text">
        {{ c["banner-title"] }}
      </h1>
      <p class="text-lg main-text">
        {{ c["banner-paragraph"] }}
      </p>
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
