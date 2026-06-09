<template>
  <div class="container mx-auto py-12 px-4 font-default">
    <!-- Filters + Grid -->
    <div class="flex flex-col md:flex-row gap-8">
      <!-- LEFT COLUMN: Filters -->
      <aside
        class="md:w-1/4 border border-color-ideas p-6 rounded-lg shadow-lg shadow-[var(--color-brand)] h-fit"
      >
        <input
          v-model="searchQuery"
          type="text"
          :placeholder="c['search-placeholder']"
          class="w-full mb-4 p-2 rounded border border-color-ideas text-dark"
        />

        <h3 class="text-xl font-bold text-[var(--color-brand)] mb-3">
          {{ c["filter-title"] }}
        </h3>
        <div v-for="cat in uniqueCategories" :key="cat" class="mb-1">
          <input
            type="checkbox"
            :id="cat"
            :value="cat"
            v-model="selectedCategories"
            class="mr-2 w-4 h-4 rounded-sm accent-[var(--color-brand)] ring-1 ring-inset ring-[var(--color-brand)] focus:ring-2 focus:ring-[var(--color-brand)] focus:ring-offset-0"
          />
          <label :for="cat" class="text-dark">{{ cat }}</label>
        </div>
      </aside>

      <!-- RIGHT COLUMN: Cards -->
      <section class="md:w-3/4">
        <div
          v-if="filtered.length"
          class="grid gap-6 md:grid-cols-2 lg:grid-cols-3"
        >
          <div
            v-for="brand in filtered"
            :key="brand.id"
            class="w-full max-w-[22rem] mx-auto rounded-2xl border border-gray-200 shadow p-4 flex flex-col gap-3 bg-white relative"
          >
            <!-- merk logo + titel -->
            <div class="flex items-center gap-2 h-10">
              <img
                :src="correctImageUrl(brand.logo_path)"
                :alt="brand.title"
                class="max-w-8 max-h-8 object-contain rounded bg-white"
              />
              <span class="text-sm font-semibold">{{ brand.title }}</span>
            </div>

            <span
              class="self-center text-xs font-semibold px-2 py-1 rounded"
              :class="
                Number(brand.verified) === 1
                  ? 'bg-green-200 text-green-800'
                  : 'bg-orange-200 text-orange-800'
              "
            >
              {{
                Number(brand.verified) === 1
                  ? "✅ Geverifieerd"
                  : "💡 Nog niet geverifieerd"
              }}
            </span>

            <!-- categorie -->
            <p class="text-gray-600 text-sm text-center">
              {{ brand.category || "—" }}
            </p>

            <!-- CTA -->
            <NuxtLink
              :to="`/brands/${brand.slug}`"
              class="cta w-max mx-auto mt-auto mb-2 px-4 py-2 rounded text-white bg-[var(--color-brand)] hover:opacity-90"
            >
              Bezoek
            </NuxtLink>
          </div>
        </div>

        <!-- geen resultaten -->
        <p v-else class="text-center text-gray-500 py-8">
          Geen deelnemers gevonden.
        </p>
      </section>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { apiFetch } from "~/composables/adapter/useApi";
import type { Brand } from "~/types/brand";
import { useCmsContent } from "~/composables/content/useCmsContent";
import { useRuntimeConfig } from "nuxt/app";

const { content: c, isLoading } = useCmsContent("deelnemers");
const ready = computed(() => !isLoading.value && Object.keys(c.value).length);

/* ophalen deelnemers */
const brands = ref<Brand[]>([]);
onMounted(async () => {
  brands.value = await apiFetch<Brand[]>("/brands", {
    params: { accepted: 1 },
  });
});

/* zoek & filter logica */
const searchQuery = ref("");
const selectedCategories = ref<string[]>([]);

const uniqueCategories = computed(() =>
  [...new Set(brands.value.map((b) => b.category).filter(Boolean))].sort()
);

const filtered = computed(() =>
  brands.value.filter((b) => {
    const needle = searchQuery.value.trim().toLowerCase();
    const matchesSearch =
      !needle ||
      b.title.toLowerCase().includes(needle) ||
      (b.category || "").toLowerCase().includes(needle);

    const matchesCategory =
      !selectedCategories.value.length ||
      selectedCategories.value.includes(b.category);

    return matchesSearch && matchesCategory;
  })
);

/* img helper */
function correctImageUrl(p?: string) {
  if (!p) return "/img/placeholder-brand.svg";
  if (p.startsWith("http") || p.startsWith("//") || p.startsWith("/img"))
    return p;
  const base = (useRuntimeConfig().public.apiBaseUrl as string).replace(
    "/api",
    "/storage"
  );
  return `${base}/${p.replace(/^\/+/, "")}`;
}
</script>
