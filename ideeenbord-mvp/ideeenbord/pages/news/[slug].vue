<template>
  <div v-if="article" class="py-12">
    <article class="max-w-3xl mx-auto px-4 md:px-0 space-y-6">
      <h1 class="text-3xl md:text-4xl font-bold">{{ article.title }}</h1>
      <img
        v-if="article.image"
        :src="correctImageUrl(article.image)"
        class="w-full rounded-xl"
        :alt="article.title"
      />
      <p class="prose max-w-none" v-html="article.excerpt"></p>
      <p class="prose max-w-none" v-html="article.body"></p>
    </article>
  </div>
  <div v-else class="py-20 text-center">Artikel niet gevonden…</div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { useRoute } from "vue-router";
import { useCmsContent } from "~/composables/content/useCmsContent";
import { useRuntimeConfig } from "nuxt/app";

const slug = useRoute().params.slug as string;
const { content, isLoading } = useCmsContent("news");

const cfg = useRuntimeConfig();
function correctImageUrl(path?: string) {
  if (!path) return "";
  if (
    path.startsWith("http") ||
    path.startsWith("//") ||
    path.startsWith("/img")
  )
    return path;
  return `${cfg.public.apiBaseUrl.replace("/api", "/storage")}/${path.replace(
    /^\/+/,
    ""
  )}`;
}

const article = computed(() => {
  if (isLoading.value) return null;
  const c = content.value;
  let i = 1;
  while (c[`article${i}-slug`]) {
    if (c[`article${i}-slug`] === slug) {
      return {
        title: c[`article${i}-title`],
        image: c[`article${i}-image`],
        excerpt: c[`article${i}-excerpt`],
        body: c[`article${i}-body`],
      };
    }
    i++;
  }
  return null;
});

console.log(article);
</script>
