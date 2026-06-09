<template>
  <NuxtLink
    :to="`/news/${article.slug}`"
    class="group block rounded-2xl overflow-hidden bg-white border border-gray-100 shadow-[0_6px_20px_rgba(31,41,55,0.05)] hover:shadow-xl hover:-translate-y-1 transition-all duration-300 h-full"
  >
    <div class="flex flex-col h-full">
      <div class="w-full h-48 overflow-hidden bg-[var(--color-bg)]">
        <img
          v-if="article.image"
          :src="correctImageUrl(article.image)"
          class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
          alt=""
        />
        <div v-else class="w-full h-full flex items-center justify-center">
          <i class="fa-regular fa-newspaper text-4xl text-gray-300"></i>
        </div>
      </div>
      <div class="flex flex-col flex-grow p-5">
        <h2 class="text-lg font-bold text-[var(--color-nav)] line-clamp-2">
          {{ article.title }}
        </h2>
        <p class="mt-2 text-sm text-gray-600 line-clamp-3 flex-grow">
          {{ article.excerpt }}
        </p>
        <span class="mt-4 text-sm font-semibold text-[var(--color-brand)] group-hover:underline">
          Lees verder →
        </span>
      </div>
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
