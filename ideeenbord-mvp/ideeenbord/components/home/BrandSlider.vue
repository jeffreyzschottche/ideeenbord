<template>
  <section class="w-full py-8 md:py-16">
    <div class="flex items-center justify-center mb-4">
      <h2 class="text-2xl md:text-3xl font-bold dark-text mb-2">
        {{ content["brandslider-title"] }}
      </h2>
    </div>
    <div
      class="relative flex items-center justify-center overflow-hidden w-full max-w-4xl mx-auto"
      @mouseenter="pauseCarousel"
      @mouseleave="resumeCarousel"
    >
      <div class="flex transition-all duration-700 ease-in-out">
        <div
          v-for="(brand, i) in visibleBrands"
          :key="brand.id"
          class="w-1/2 md:w-1/5 flex justify-center p-2 transition-all duration-300"
          :class="{
            'opacity-100 scale-110 z-10': i === centerIndex,
            'opacity-60 scale-95': i !== centerIndex,
          }"
        >
          <!-- Alleen de middelste is klikbaar -->
          <template v-if="i === centerIndex">
            <NuxtLink
              :to="`/brands/${brand.title}`"
              class="block w-full flex flex-col items-center"
            >
              <img
                :src="correctImageUrl(brand.logo_path)"
                :alt="brand.title"
                class="w-20 h-20 md:w-32 md:h-32 object-contain rounded bg-white mb-2 shadow"
                loading="lazy"
              />
              <span
                class="text-xs md:text-base text-center light-text font-medium mt-1 line-clamp-2"
              >
                {{ brand.title }}
              </span>
            </NuxtLink>
          </template>
          <template v-else>
            <div
              class="w-full flex flex-col items-center pointer-events-none select-none"
            >
              <img
                :src="correctImageUrl(brand.logo_path)"
                :alt="brand.title"
                class="w-20 h-20 md:w-32 md:h-32 object-contain rounded mb-2 bg-white"
                loading="lazy"
              />
              <span
                class="text-xs md:text-base text-center light-text font-medium mt-1 line-clamp-2"
              >
                {{ brand.title }}
              </span>
            </div>
          </template>
        </div>
      </div>
      <!-- Mobile pijltjes -->
      <div class="flex md:hidden justify-center mt-4 gap-2">
        <button
          @click="prev"
          class="btn-primary w-8 h-8 flex items-center justify-center rounded-full"
        >
          <i class="fa-solid fa-chevron-left"></i>
        </button>
        <button
          @click="next"
          class="btn-primary w-8 h-8 flex items-center justify-center rounded-full"
        >
          <i class="fa-solid fa-chevron-right"></i>
        </button>
      </div>
    </div>
    <div class="hidden md:flex gap-2 flex justify-center">
      <button
        @click="prev"
        class="btn-primary w-10 h-10 flex items-center justify-center rounded-full"
        aria-label="Vorige"
      >
        <i class="fa-solid fa-chevron-left"></i>
      </button>
      <button
        @click="next"
        class="btn-primary w-10 h-10 flex items-center justify-center rounded-full"
        aria-label="Volgende"
      >
        <i class="fa-solid fa-chevron-right"></i>
      </button>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from "vue";
import { apiFetch } from "~/composables/adapter/useApi";
import type { Brand } from "~/types/brand";
import { useCmsContent } from "~/composables/content/useCmsContent";
const { content } = useCmsContent("home");

// Data
const brands = ref<Brand[]>([]);
const current = ref(0);
const visibleCount = ref(5);
const interval = ref<any>(null);

// Responsive aanpassen van visibleCount
function updateVisibleCount() {
  visibleCount.value = window.innerWidth < 768 ? 2 : 5;
}
onMounted(async () => {
  brands.value = await apiFetch<Brand[]>("/brands", {
    params: { accepted: 1 },
  });
  updateVisibleCount();
  window.addEventListener("resize", updateVisibleCount);
  startCarousel();
});
onUnmounted(() => {
  window.removeEventListener("resize", updateVisibleCount);
  clearInterval(interval.value);
});

// Carousel logica
const centerIndex = computed(() => Math.floor(visibleCount.value / 2));

const visibleBrands = computed(() => {
  if (!brands.value.length) return [];
  const total = brands.value.length;
  let items = [];
  for (let offset = -centerIndex.value; offset <= centerIndex.value; offset++) {
    // Circular!
    const index = (current.value + offset + total) % total;
    items.push(brands.value[index]);
  }
  return items;
});

function correctImageUrl(url: string): string {
  const rawApiBase = useRuntimeConfig().public.apiBaseUrl;
  const apiBase = rawApiBase as string;
  const imageBase = apiBase.replace("/api", "/storage");
  return imageBase + "/" + url;
}

function prev() {
  if (!brands.value.length) return;
  current.value =
    (current.value - 1 + brands.value.length) % brands.value.length;
}
function next() {
  if (!brands.value.length) return;
  current.value = (current.value + 1) % brands.value.length;
}

function startCarousel() {
  interval.value = setInterval(next, 2500);
}
function pauseCarousel() {
  clearInterval(interval.value);
}
function resumeCarousel() {
  startCarousel();
}
</script>

<style scoped>
/* Eventueel extra styling kun je hier toevoegen */
</style>
