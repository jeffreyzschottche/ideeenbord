<template>
  <div v-if="ready" class="py-12 font-default">
    <!-- HERO -->
    <section class="max-w-4xl mx-auto text-center space-y-6 px-4 mb-12">
      <img
        v-if="c['hero-image']"
        :src="imageUrl(c['hero-image'])"
        class="mx-auto w-full max-w-md rounded-xl shadow"
        alt="Privacy"
      />
      <h1 class="text-3xl md:text-4xl font-bold dark-text">
        {{ c["hero-title"] }}
      </h1>
      <p class="font-alt text-lg main-text">
        {{ c["hero-subtitle"] }}
      </p>
      <p v-if="c['updated-date']" class="text-sm text-gray-500">
        Laatst bijgewerkt: {{ c["updated-date"] }}
      </p>
    </section>

    <!-- SECTIES -->
    <section class="max-w-3xl mx-auto px-4 md:px-0 space-y-8">
      <article
        v-for="(sec, i) in sections"
        :key="i"
        class="bg-white rounded-xl shadow p-6 about-card"
      >
        <h2 class="text-xl font-bold mb-3 text-[var(--color-brand)]">
          {{ sec.title }}
        </h2>
        <div class="prose max-w-none" v-html="sec.body"></div>
      </article>

      <!-- CONTACT -->
      <article
        v-if="c['contact-title']"
        class="bg-white rounded-xl shadow p-6 about-card"
      >
        <h2 class="text-xl font-bold mb-3 text-[var(--color-brand)]">
          {{ c["contact-title"] }}
        </h2>
        <div class="prose max-w-none" v-html="c['contact-body']"></div>
      </article>
    </section>
  </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { useRuntimeConfig } from "nuxt/app";
import { useCmsContent } from "~/composables/content/useCmsContent";

const { content: c, isLoading } = useCmsContent("privacy");
const ready = computed(
  () => !isLoading.value && Object.keys(c.value).length > 0
);

/* secties inlezen: section{i}-title / section{i}-body (HTML) */
const sections = computed(() => {
  const list: Array<{ title: string; body: string }> = [];
  const data = c.value as Record<string, string>;
  let i = 1;
  while (data[`section${i}-title`] && data[`section${i}-body`]) {
    list.push({
      title: data[`section${i}-title`],
      body: data[`section${i}-body`],
    });
    i++;
  }
  return list;
});

/* image helper */
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

<style scoped>
.prose :where(a) {
  color: var(--color-brand);
  text-decoration: underline;
}
</style>
