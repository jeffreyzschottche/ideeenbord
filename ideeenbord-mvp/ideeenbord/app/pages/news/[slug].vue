<template>
  <div v-if="article" class="py-12">
    <article class="max-w-3xl mx-auto px-4">
      <NuxtLink to="/news" class="inline-flex items-center gap-1 text-sm font-semibold text-[var(--color-brand)] hover:underline mb-6">
        ← Terug naar nieuws
      </NuxtLink>
      <h1 class="text-3xl md:text-5xl font-extrabold text-[var(--color-nav)] leading-tight mb-6">
        {{ article.title }}
      </h1>
      <img
        v-if="article.image"
        :src="correctImageUrl(article.image)"
        class="w-full rounded-2xl shadow-lg mb-8"
        :alt="article.title"
      />
      <p class="text-xl text-gray-500 leading-relaxed mb-8 font-medium" v-html="sanitizeHtml(article.excerpt)"></p>
      <div class="article-prose" v-html="sanitizeHtml(article.body)"></div>
    </article>
  </div>
  <div v-else class="py-24 text-center text-gray-500">Artikel niet gevonden…</div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { useRoute } from "vue-router";
import { useCmsContent } from "~/composables/content/useCmsContent";
import { useRuntimeConfig } from "nuxt/app";
import { storageBaseFromApiBase } from "~/utils/apiUrl";
import { sanitizeHtml } from "~/utils/sanitizeHtml";

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
  return `${storageBaseFromApiBase(cfg.public.apiBaseUrl)}/${path.replace(
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

// --- SEO ---
const canonical = useCanonical();
useSeoMeta({
  title: () => article.value?.title ?? "Nieuws",
  description: () =>
    article.value?.excerpt ?? "Nieuws en updates van Ideeënbord.",
  ogTitle: () => article.value?.title ?? "Nieuws",
  ogDescription: () => article.value?.excerpt ?? "",
  ogType: "article",
  ogImage: () => correctImageUrl(article.value?.image),
  twitterCard: "summary_large_image",
});
useHead({
  link: [{ rel: "canonical", href: canonical }],
  script: [
    {
      type: "application/ld+json",
      innerHTML: computed(() =>
        JSON.stringify({
          "@context": "https://schema.org",
          "@type": "NewsArticle",
          headline: article.value?.title ?? "Nieuws",
          description: article.value?.excerpt ?? "",
          image: article.value?.image ? correctImageUrl(article.value.image) : undefined,
          mainEntityOfPage: canonical,
          publisher: { "@type": "Organization", name: "Ideeënbord" },
        })
      ) as any,
    },
  ],
});
</script>
