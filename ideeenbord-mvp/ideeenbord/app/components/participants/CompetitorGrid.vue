<template>
  <div class="container mx-auto py-12 px-4 -mt-10 relative">
    <div class="flex flex-col lg:flex-row gap-8">
      <!-- LEFT: Filters -->
      <aside ref="filterCard" class="lg:w-72 shrink-0">
        <div class="filter-card sticky top-24">
          <!-- Search -->
          <div class="relative mb-6">
            <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <input
              v-model="searchQuery"
              type="text"
              :placeholder="c['search-placeholder'] || 'Zoek op merk of categorie...'"
              class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[var(--color-brand)] focus:border-transparent transition"
            />
          </div>

          <!-- Categories -->
          <div>
            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-4">
              {{ c["filter-title"] || "Categorieën" }}
            </h3>
            <div class="space-y-2">
              <label
                v-for="cat in uniqueCategories"
                :key="cat"
                class="category-item"
                :class="{ active: selectedCategories.includes(cat) }"
              >
                <input
                  type="checkbox"
                  :value="cat"
                  v-model="selectedCategories"
                  class="sr-only"
                />
                <span class="checkbox-indicator">
                  <i v-if="selectedCategories.includes(cat)" class="fa-solid fa-check text-white text-xs"></i>
                </span>
                <span class="category-label">{{ cat }}</span>
                <span class="category-count">{{ getCategoryCount(cat) }}</span>
              </label>
            </div>
          </div>

          <!-- Clear filters -->
          <button
            v-if="selectedCategories.length || searchQuery"
            @click="clearFilters"
            class="mt-6 w-full py-2 text-sm font-medium text-gray-500 hover:text-[var(--color-brand)] transition"
          >
            <i class="fa-solid fa-times mr-1"></i>
            Filters wissen
          </button>
        </div>
      </aside>

      <!-- RIGHT: Brand Cards -->
      <section class="flex-1">
        <!-- Results count -->
        <div class="mb-6 flex items-center justify-between">
          <p class="text-gray-600">
            <span class="font-semibold text-gray-900">{{ filtered.length }}</span> merken gevonden
          </p>
        </div>

        <!-- Grid -->
        <div v-if="filtered.length" ref="gridWrap" class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
          <NuxtLink
            v-for="brand in filtered"
            :key="brand.id"
            :to="`/brands/${brand.slug}`"
            class="brand-card group"
          >
            <!-- Logo -->
            <div class="brand-logo-wrap">
              <img
                :src="correctImageUrl(brand.logo_path)"
                :alt="brand.title"
                class="brand-logo"
                loading="lazy"
              />
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
              <div class="flex items-start justify-between gap-2 mb-2">
                <h3 class="font-bold text-gray-900 group-hover:text-[var(--color-brand)] transition truncate">
                  {{ brand.title }}
                </h3>
                <span
                  v-if="Number(brand.verified) === 1"
                  class="verified-badge"
                  title="Geverifieerd merk"
                >
                  <i class="fa-solid fa-check"></i>
                </span>
              </div>

              <p class="text-sm text-gray-500 mb-3">{{ brand.category || "Algemeen" }}</p>

              <!-- Stats -->
              <div class="flex items-center gap-4 text-xs text-gray-400">
                <span class="flex items-center gap-1">
                  <i class="fa-solid fa-lightbulb text-amber-400"></i>
                  {{ brand.ideas_count || 0 }} ideeën
                </span>
              </div>
            </div>

            <!-- Arrow -->
            <div class="arrow-wrap">
              <i class="fa-solid fa-arrow-right"></i>
            </div>
          </NuxtLink>
        </div>

        <!-- No results -->
        <div v-else class="text-center py-16">
          <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
            <i class="fa-solid fa-search text-2xl text-gray-300"></i>
          </div>
          <h3 class="text-lg font-semibold text-gray-900 mb-2">Geen merken gevonden</h3>
          <p class="text-gray-500">Probeer een andere zoekopdracht of pas je filters aan.</p>
        </div>
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
import { useGsap } from "~/composables/useGsap";

const { content: c } = useCmsContent("deelnemers");

const filterCard = ref<HTMLElement | null>(null);
const gridWrap = ref<HTMLElement | null>(null);

const brands = ref<Brand[]>([]);

onMounted(async () => {
  brands.value = await apiFetch<Brand[]>("/brands", {
    params: { accepted: 1 },
  });

  const { gsap, prefersReducedMotion } = useGsap();
  if (prefersReducedMotion.value || !gsap) return;

  if (filterCard.value) {
    gsap.from(filterCard.value, {
      x: -30,
      opacity: 0,
      duration: 0.6,
      ease: "power3.out",
    });
  }

  if (gridWrap.value) {
    gsap.from(gridWrap.value.querySelectorAll(".brand-card"), {
      y: 30,
      opacity: 0,
      duration: 0.5,
      stagger: 0.05,
      ease: "power3.out",
      delay: 0.2,
    });
  }
});

const searchQuery = ref("");
const selectedCategories = ref<string[]>([]);

// Exposed function for hero search
function setSearchFromHero(query: string) {
  searchQuery.value = query;
}

defineExpose({ setSearchFromHero });

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

function getCategoryCount(category: string) {
  return brands.value.filter((b) => b.category === category).length;
}

function clearFilters() {
  searchQuery.value = "";
  selectedCategories.value = [];
}

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

<style scoped>
.filter-card {
  background: white;
  border-radius: 20px;
  padding: 24px;
  box-shadow:
    0 4px 24px rgba(0, 0, 0, 0.06),
    0 1px 2px rgba(0, 0, 0, 0.04);
  border: 1px solid rgba(0, 0, 0, 0.04);
}

.category-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 12px;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.category-item:hover {
  background: #f9fafb;
}

.category-item.active {
  background: linear-gradient(135deg, rgba(249, 115, 22, 0.08) 0%, rgba(251, 191, 36, 0.08) 100%);
}

.checkbox-indicator {
  width: 20px;
  height: 20px;
  border-radius: 6px;
  border: 2px solid #d1d5db;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  flex-shrink: 0;
}

.category-item.active .checkbox-indicator {
  background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
  border-color: transparent;
}

.category-label {
  flex: 1;
  font-size: 14px;
  color: #374151;
}

.category-count {
  font-size: 12px;
  color: #9ca3af;
  background: #f3f4f6;
  padding: 2px 8px;
  border-radius: 10px;
}

/* Brand Cards */
.brand-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 20px;
  background: white;
  border-radius: 16px;
  border: 1px solid rgba(0, 0, 0, 0.04);
  box-shadow:
    0 2px 12px rgba(0, 0, 0, 0.04),
    0 1px 2px rgba(0, 0, 0, 0.02);
  transition: all 0.3s ease;
  text-decoration: none;
}

.brand-card:hover {
  transform: translateY(-4px);
  box-shadow:
    0 12px 32px rgba(0, 0, 0, 0.08),
    0 2px 4px rgba(0, 0, 0, 0.04);
  border-color: var(--color-brand);
}

.brand-logo-wrap {
  width: 56px;
  height: 56px;
  border-radius: 14px;
  background: #f9fafb;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  overflow: hidden;
}

.brand-logo {
  max-width: 40px;
  max-height: 40px;
  object-fit: contain;
}

.verified-badge {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 10px;
  flex-shrink: 0;
}

.arrow-wrap {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #f3f4f6;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #9ca3af;
  transition: all 0.3s ease;
  flex-shrink: 0;
}

.brand-card:hover .arrow-wrap {
  background: var(--color-brand);
  color: white;
  transform: translateX(4px);
}
</style>
