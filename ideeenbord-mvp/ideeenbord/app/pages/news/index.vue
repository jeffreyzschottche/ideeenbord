<template>
  <div v-if="ready" class="container mx-auto px-4 py-12 font-default">
    <!-- ─── HERO / INTRO ─── -->
    <section class="max-w-3xl mx-auto text-center mb-14">
      <span class="inline-block text-xs font-bold uppercase tracking-[0.2em] text-[var(--color-brand)] mb-3">
        Nieuws
      </span>
      <h1 class="text-3xl md:text-5xl font-extrabold text-[var(--color-nav)]">
        {{ content["hero-title"] || "Nieuws & updates" }}
      </h1>
      <p v-if="content['hero-paragraph']" class="mt-4 text-lg text-gray-600">
        {{ content["hero-paragraph"] }}
      </p>
    </section>

    <!-- ─── NIEUWS-GRID ─── -->
    <NewsGrid :articles="articles" />
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
