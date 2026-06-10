<template>
  <div v-if="ready" class="font-default pb-16">
    <PageHero
      eyebrow="Nieuws"
      :title="content['hero-title'] || 'Nieuws & updates'"
      :subtitle="content['hero-paragraph']"
    />
    <div class="container mx-auto px-4">
      <NewsGrid :articles="articles" />
    </div>
  </div>
</template>

<script setup lang="ts">
usePageSeo({
  title: "Nieuws",
  description: "Het laatste nieuws en de updates van Ideeënbord.",
});
useJsonLd([
  { "@type": "CollectionPage", name: "Nieuws" },
  breadcrumbLd([
    { name: "Home", path: "/" },
    { name: "Nieuws", path: "/news" },
  ]),
]);
import { computed } from "vue";
import { useCmsContent } from "~/composables/content/useCmsContent";
import { useRuntimeConfig } from "nuxt/app";

const { content, isLoading } = useCmsContent("news");
const ready = computed(
  () => !isLoading.value && Object.keys(content.value).length > 0
);

const articles = computed(() => {
  const c = content.value;
  const list = [];
  let i = 1;
  while (c[`article${i}-title`]) {
    list.push({
      title: c[`article${i}-title`],
      slug: c[`article${i}-slug`],
      excerpt: c[`article${i}-excerpt`],
      image: c[`article${i}-image`],
    });
    i++;
  }
  return list;
});

/* ─── helper voor image URL ─── */
function imageUrl(path?: string) {
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
