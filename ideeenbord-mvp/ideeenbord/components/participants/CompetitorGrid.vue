<template>
  <section class="w-full">
    <div class="container mx-auto px-6">
      <!-- Filters + zoek -->
      <div class="flex flex-col md:flex-row gap-6">
        <!-- categorie-filter -->
        <div
          class="md:w-1/4 bg-nav p-6 rounded-lg shadow-lg shadow-[var(--color-brand)] h-fit"
        >
          <h3 class="text-xl font-bold text-[var(--color-brand)] mb-4">
            {{ c["filter-title"] }}
          </h3>
          <div v-for="cat in uniqueCategories" :key="cat" class="mb-2">
            <input
              type="checkbox"
              :id="cat"
              :value="cat"
              v-model="selectedCategories"
              class="mr-2"
            />
            <label :for="cat" class="text-gray-300">{{ cat }}</label>
          </div>
        </div>

        <!-- zoek + grid -->
        <div class="md:w-3/4">
          <input
            v-model="searchQuery"
            type="text"
            :placeholder="c['search-placeholder']"
            class="w-full p-3 mb-6 rounded-lg border-2 border-[var(--color-brand)] bg-nav text-white p-2"
          />

          <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div
              v-for="brand in filtered"
              :key="brand.id"
              class="relative bg-nav p-4 rounded-lg shadow-lg text-center transition hover:scale-105 hover:shadow-xl"
            >
              <!-- claim-status -->
              <div
                :class="[
                  'absolute top-0 left-0 right-0 text-sm font-bold py-1 rounded-t-lg border-b-2',
                  Number(brand.verified) === 1
                    ? 'bg-green-200 text-green-800 border-green-600'
                    : 'bg-orange-200 text-orange-800 border-orange-500',
                ]"
              >
                {{
                  Number(brand.verified) === 1
                    ? "✅ Geverifieerd"
                    : "💡 Nog niet geverifieerd"
                }}
              </div>

              <img
                :src="correctImageUrl(brand.logo_path)"
                :alt="brand.title"
                class="w-20 h-20 mx-auto mb-4 rounded-lg mt-6 object-contain bg-white"
              />
              <h3 class="text-lg font-bold text-[var(--color-brand)]">
                {{ brand.title }}
              </h3>
              <p class="text-gray-300 text-sm">{{ brand.category || "—" }}</p>

              <NuxtLink
                :to="`/brands/${brand.slug}`"
                class="mt-2 inline-block cta bg-[var(--color-brand)] text-white px-4 py-2 rounded-lg hover:opacity-90"
              >
                Bezoek
              </NuxtLink>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { apiFetch } from "~/composables/adapter/useApi";
import type { Brand } from "~/types/brand";
import { useCmsContent } from "~/composables/content/useCmsContent";
import { useRuntimeConfig } from "nuxt/app";

const { content: c } = useCmsContent("deelnemers");

/* -------- brands ophalen -------- */
const brands = ref<Brand[]>([]);
onMounted(async () => {
  brands.value = await apiFetch<Brand[]>("/brands", {
    params: { accepted: 1 },
  });
});

console.log(brands);

/* -------- zoek & filter ---------- */
const searchQuery = ref("");
const selectedCategories = ref<string[]>([]);

const uniqueCategories = computed(() => [
  ...new Set(brands.value.map((b) => b.category).filter(Boolean)),
]);
console.log(uniqueCategories);

const filtered = computed(() =>
  brands.value.filter((b) => {
    const matchesSearch =
      b.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      (b.category || "")
        .toLowerCase()
        .includes(searchQuery.value.toLowerCase());

    const matchesCategory =
      !selectedCategories.value.length ||
      selectedCategories.value.includes(b.category);

    return matchesSearch && matchesCategory;
  })
);

/* img helper */
function correctImageUrl(p: string) {
  if (!p) return "";
  if (p.startsWith("http") || p.startsWith("//") || p.startsWith("/img"))
    return p;
  const base = (useRuntimeConfig().public.apiBaseUrl as string).replace(
    "/api",
    "/storage"
  );
  return `${base}/${p.replace(/^\/+/, "")}`;
}
</script>
