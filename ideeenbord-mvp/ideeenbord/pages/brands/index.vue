<script setup lang="ts">
import { ref, onMounted } from "vue";
import { apiFetch } from "~/composables/adapter/useApi";
import type { Brand } from "~/types/brand";

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
  const imageBase = apiBase.replace("/api", "/storage");
  return imageBase + "/" + url;
}
</script>

<template>
  <div class="container mx-auto px-4 py-12">
    <h1 class="text-3xl md:text-4xl font-bold text-center mb-12">
      Alle Brands
    </h1>

    <div
      class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6"
    >
      <NuxtLink
        v-for="brand in brands"
        :key="brand.id"
        :to="`/brands/${slugify(brand.title)}`"
        class="flex flex-col items-center p-4 rounded shadow hover:shadow-lg transition group bg-white"
      >
        <img
          :src="correctImageUrl(brand.logo_path)"
          :alt="brand.title"
          class="w-20 h-20 md:w-28 md:h-28 object-contain bg-white rounded mb-3 shadow-sm"
          loading="lazy"
        />
        <span
          class="text-sm md:text-base text-center font-medium group-hover:underline"
        >
          {{ brand.title }}
        </span>
      </NuxtLink>
    </div>
  </div>
</template>
