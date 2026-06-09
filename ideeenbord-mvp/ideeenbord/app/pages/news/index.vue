<template>
  <div v-if="ready" class="py-12 font-default">
    <!-- ─── HERO / INTRO ─── -->
    <section class="max-w-4xl mx-auto text-center space-y-6 px-4 mb-16">
      <img
        v-if="content['hero-image']"
        :src="imageUrl(content['hero-image'])"
        class="mx-auto w-full max-w-md rounded-xl shadow"
        alt="Nieuws"
      />

      <h1 class="text-3xl md:text-4xl font-bold dark-text">
        {{ content["hero-title"] }}
      </h1>
      <p class="font-alt text-lg main-text">
        {{ content["hero-paragraph"] }}
      </p>
    </section>

    <!-- ─── NIEUWS-GRID ─── -->
    <NewsGrid :articles="articles" />
  </div>
</template>

<script setup lang="ts">
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
