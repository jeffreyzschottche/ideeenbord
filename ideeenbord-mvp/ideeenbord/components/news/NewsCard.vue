<template>
  <NuxtLink
    :to="`/news/${article.slug}`"
    class="block rounded-2xl overflow-hidden shadow hover:shadow-lg transition"
  >
    <img
      v-if="article.image"
      :src="correctImageUrl(article.image)"
      class="w-full h-48 object-cover"
      alt=""
    />
    <div class="p-4 space-y-2 bg-white">
      <h2 class="text-lg font-bold dark-text line-clamp-2">
        {{ article.title }}
      </h2>
      <p class="text-sm main-text opacity-80 line-clamp-3">
        {{ article.excerpt }}
      </p>
      <span class="text-[var(--color-brand)] font-semibold">Lees verder →</span>
    </div>
  </NuxtLink>
</template>

<script setup lang="ts">
import { useRuntimeConfig } from "nuxt/app";
const props = defineProps<{
  article: { title: string; slug: string; excerpt: string; image: string };
}>();

function correctImageUrl(path?: string) {
  if (!path) return "";
  if (
    path.startsWith("http") ||
    path.startsWith("//") ||
    path.startsWith("/img")
  )
    return path;
  const base = (useRuntimeConfig().public.apiBaseUrl as string).replace(
    "/api",
    "/storage"
  );
  return `${base}/${path.replace(/^\/+/, "")}`;
}
</script>
