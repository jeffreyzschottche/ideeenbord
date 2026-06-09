<script setup lang="ts">
usePageSeo({
  title: "Alle merken",
  description:
    "Ontdek alle merken op Ideeënbord en deel jouw ideeën, wensen en feedback met de merken die jij belangrijk vindt.",
});
useJsonLd([
  { "@type": "CollectionPage", name: "Alle merken" },
  breadcrumbLd([
    { name: "Home", path: "/" },
    { name: "Merken", path: "/brands" },
  ]),
]);
import { ref, onMounted } from "vue";
import { apiFetch } from "~/composables/adapter/useApi";
import type { Brand } from "~/types/brand";
import { storageBaseFromApiBase } from "~/utils/apiUrl";

// ── State ──
const brands = ref<Brand[]>([]);

// ── Fetch on mount ──
onMounted(async () => {
  brands.value = (await apiFetch<Brand[]>("/brands?accepted=1")).sort((a, b) =>
    a.title.localeCompare(b.title)
  );
});

// ── Helpers ──
function slugify(text: string): string {
  return text
    .toLowerCase()
    .replace(/\s+/g, "-")
    .replace(/[^\w\-]+/g, "");
}

function correctImageUrl(url: string): string {
  const rawApiBase = useRuntimeConfig().public.apiBaseUrl;
  const apiBase = rawApiBase as string;
  const imageBase = storageBaseFromApiBase(apiBase);
  return imageBase + "/" + url;
}
</script>

<template>
  <div class="container mx-auto px-4 py-12">
    <div class="text-center max-w-2xl mx-auto mb-12">
      <span class="inline-block text-xs font-bold uppercase tracking-[0.2em] text-[var(--color-brand)] mb-3">
        Merken
      </span>
      <h1 class="text-3xl md:text-5xl font-extrabold text-[var(--color-nav)]">
        Ontdek alle merken
      </h1>
      <p class="mt-4 text-lg text-gray-600">
        Kies een merk en deel jouw idee, wens of verbeterpunt.
      </p>
    </div>

    <div
      class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-5"
    >
      <NuxtLink
        v-for="brand in brands"
        :key="brand.id"
        :to="`/brands/${slugify(brand.title)}`"
        class="group flex flex-col items-center p-5 rounded-2xl bg-white border border-gray-100 shadow-[0_6px_20px_rgba(31,41,55,0.05)] hover:shadow-xl hover:-translate-y-1 transition-all duration-300"
      >
        <div class="w-24 h-24 flex items-center justify-center rounded-2xl bg-[var(--color-bg)] mb-4 overflow-hidden">
          <img
            :src="correctImageUrl(brand.logo_path)"
            :alt="brand.title"
            class="w-full h-full object-contain p-2 transition-transform duration-300 group-hover:scale-110"
            loading="lazy"
          />
        </div>
        <span class="text-sm md:text-base text-center font-semibold text-[var(--color-nav)] line-clamp-2">
          {{ brand.title }}
        </span>
        <span v-if="brand.category" class="mt-1 text-xs text-gray-400">{{ brand.category }}</span>
      </NuxtLink>
    </div>

    <p v-if="!brands.length" class="text-center text-gray-500 mt-12">
      Merken worden geladen…
    </p>
  </div>
</template>
