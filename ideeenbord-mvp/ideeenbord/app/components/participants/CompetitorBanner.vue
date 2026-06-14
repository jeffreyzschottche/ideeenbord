<template>
  <section class="hero-section relative overflow-hidden">
    <div class="hero-glow" aria-hidden="true"></div>
    <div class="hero-glow-2" aria-hidden="true"></div>

    <div class="container mx-auto px-4 pt-28 pb-20 md:pt-32 md:pb-28 relative">
      <div class="text-center max-w-3xl mx-auto">
        <p ref="eyebrow" class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-gray-200 text-xs font-semibold uppercase tracking-[0.15em] mb-5">
          <span class="w-2 h-2 rounded-full bg-[var(--color-brand)] animate-pulse"></span>
          Deelnemers
        </p>
        <h1 ref="heading" class="text-4xl md:text-5xl lg:text-6xl font-extrabold leading-tight text-white">
          {{ c["banner-title"] || "Ontdek alle deelnemende merken" }}
        </h1>
        <p ref="subline" class="mt-6 text-lg md:text-xl text-gray-300 leading-relaxed">
          {{ c["banner-paragraph"] || "Hier vind je alle bedrijven die actief luisteren naar ideeën van de community. Filter op categorie of zoek direct naar je favoriete merk." }}
        </p>

        <!-- Stats -->
        <div ref="stats" class="mt-10 flex flex-wrap items-center justify-center gap-8">
          <div class="stat-item">
            <div class="stat-value">{{ brandCount }}+</div>
            <div class="stat-label">Merken</div>
          </div>
          <div class="stat-item">
            <div class="stat-value">{{ verifiedCount }}</div>
            <div class="stat-label">Geverifieerd</div>
          </div>
          <div class="stat-item">
            <div class="stat-value">{{ categoryCount }}</div>
            <div class="stat-label">Categorieën</div>
          </div>
        </div>

        <!-- Hero Search Bar -->
        <div ref="searchWrap" class="mt-10 max-w-xl mx-auto">
          <div class="hero-search">
            <i class="fa-solid fa-search text-gray-400"></i>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Zoek naar een merk..."
              class="hero-search-input"
              @input="emitSearch"
            />
            <button v-if="searchQuery" @click="clearSearch" class="clear-btn">
              <i class="fa-solid fa-times"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";
import { useCmsContent } from "~/composables/content/useCmsContent";
import { apiFetch } from "~/composables/adapter/useApi";
import { useGsap } from "~/composables/useGsap";
import type { Brand } from "~/types/brand";

const emit = defineEmits<{
  (e: 'search', query: string): void;
}>();

const { content: c } = useCmsContent("deelnemers");

const eyebrow = ref<HTMLElement | null>(null);
const heading = ref<HTMLElement | null>(null);
const subline = ref<HTMLElement | null>(null);
const stats = ref<HTMLElement | null>(null);
const searchWrap = ref<HTMLElement | null>(null);
const searchQuery = ref("");

const brandCount = ref(0);
const verifiedCount = ref(0);
const categoryCount = ref(0);

function emitSearch() {
  emit('search', searchQuery.value);
}

function clearSearch() {
  searchQuery.value = "";
  emit('search', "");
}

onMounted(async () => {
  const { gsap, prefersReducedMotion } = useGsap();

  // Fetch stats
  try {
    const brands = await apiFetch<Brand[]>("/brands", { params: { accepted: 1 } });
    brandCount.value = brands.length;
    verifiedCount.value = brands.filter(b => Number(b.verified) === 1).length;
    categoryCount.value = new Set(brands.map(b => b.category).filter(Boolean)).size;
  } catch {}

  if (prefersReducedMotion.value || !gsap) return;

  gsap.from([eyebrow.value, heading.value, subline.value], {
    y: 30,
    opacity: 0,
    duration: 0.7,
    stagger: 0.12,
    ease: "power3.out",
  });

  if (stats.value) {
    gsap.from(stats.value.querySelectorAll(".stat-item"), {
      y: 20,
      opacity: 0,
      duration: 0.5,
      stagger: 0.1,
      ease: "power3.out",
      delay: 0.4,
    });
  }

  if (searchWrap.value) {
    gsap.from(searchWrap.value, {
      y: 20,
      opacity: 0,
      duration: 0.5,
      ease: "power3.out",
      delay: 0.6,
    });
  }
});
</script>

<style scoped>
.hero-section {
  background:
    radial-gradient(1100px 520px at 50% -10%, #2b3a52 0%, transparent 60%),
    linear-gradient(180deg, #1f2937 0%, #161e29 100%);
}

.hero-glow {
  position: absolute;
  top: -120px;
  right: 10%;
  width: 520px;
  height: 520px;
  border-radius: 9999px;
  background: radial-gradient(circle, rgba(247, 138, 29, 0.3), transparent 65%);
  filter: blur(40px);
  pointer-events: none;
}

.hero-glow-2 {
  position: absolute;
  bottom: -100px;
  left: 10%;
  width: 400px;
  height: 400px;
  border-radius: 9999px;
  background: radial-gradient(circle, rgba(255, 187, 0, 0.15), transparent 65%);
  filter: blur(50px);
  pointer-events: none;
}

.stat-item {
  text-align: center;
}

.stat-value {
  font-size: 28px;
  font-weight: 800;
  background: linear-gradient(135deg, #f97316 0%, #fbbf24 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.stat-label {
  font-size: 13px;
  color: #9ca3af;
  margin-top: 2px;
}

/* Hero Search */
.hero-search {
  display: flex;
  align-items: center;
  gap: 12px;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 16px;
  padding: 16px 20px;
  transition: all 0.3s ease;
}

.hero-search:focus-within {
  background: rgba(255, 255, 255, 0.15);
  border-color: var(--color-brand);
  box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.15);
}

.hero-search-input {
  flex: 1;
  background: transparent;
  border: none;
  outline: none;
  color: white;
  font-size: 16px;
  font-weight: 500;
}

.hero-search-input::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

.clear-btn {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.1);
  color: rgba(255, 255, 255, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.clear-btn:hover {
  background: rgba(255, 255, 255, 0.2);
  color: white;
}
</style>
