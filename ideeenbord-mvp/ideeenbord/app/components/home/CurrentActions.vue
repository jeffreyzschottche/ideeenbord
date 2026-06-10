<template>
  <section class="py-16 md:py-24">
    <div class="container mx-auto px-4">
      <div class="text-center max-w-2xl mx-auto mb-12">
        <span class="inline-block text-xs font-bold uppercase tracking-[0.2em] text-[var(--color-brand)] mb-3">
          Wat er nu speelt
        </span>
        <h2 class="text-3xl md:text-4xl font-extrabold text-[var(--color-nav)]">
          Live op het bord
        </h2>
      </div>

      <div class="grid md:grid-cols-2 gap-6 max-w-5xl mx-auto">
        <!-- Nieuwe merken -->
        <div class="rounded-3xl bg-white border border-gray-100 shadow-[0_8px_30px_rgba(31,41,55,0.06)] overflow-hidden">
          <div class="flex items-center gap-3 px-6 py-4 bg-[var(--color-nav)] text-white">
            <span class="w-9 h-9 rounded-xl bg-[var(--color-brand)] flex items-center justify-center">
              <i class="fa-solid fa-store"></i>
            </span>
            <h3 class="font-bold">
              {{ content["currentactions-left-title"] || "Nieuwe deelnemende merken" }}
            </h3>
          </div>
          <ul class="p-3">
            <li v-for="(name, idx) in recentBrands" :key="idx">
              <NuxtLink
                :to="`/brands/${name}`"
                class="flex items-center gap-3 px-3 py-3 rounded-xl hover:bg-[var(--color-bg)] transition group"
              >
                <span class="w-7 h-7 rounded-lg bg-[var(--color-brand)]/10 text-[var(--color-brand)] text-sm font-bold flex items-center justify-center">
                  {{ idx + 1 }}
                </span>
                <span class="font-semibold text-[var(--color-nav)]">{{ name }}</span>
                <i class="fa-solid fa-arrow-right ml-auto text-gray-300 group-hover:text-[var(--color-brand)] group-hover:translate-x-1 transition-all"></i>
              </NuxtLink>
            </li>
            <li v-if="!recentBrands.length" class="px-4 py-10 text-center text-gray-400">
              <i class="fa-regular fa-face-smile text-2xl mb-2 block"></i>
              Nog geen nieuwe merken.
            </li>
          </ul>
        </div>

        <!-- Quizzes -->
        <div class="rounded-3xl bg-white border border-gray-100 shadow-[0_8px_30px_rgba(31,41,55,0.06)] overflow-hidden">
          <div class="flex items-center gap-3 px-6 py-4 bg-[var(--color-nav)] text-white">
            <span class="w-9 h-9 rounded-xl bg-[var(--color-brand)] flex items-center justify-center">
              <i class="fa-solid fa-gift"></i>
            </span>
            <h3 class="font-bold">
              {{ content["currentactions-right-title"] || "Laatste winacties & quizzes" }}
            </h3>
          </div>
          <ul class="p-3">
            <li v-for="quiz in recentQuizzes" :key="quiz.id">
              <NuxtLink
                :to="`/brands/${quiz.brand.slug}`"
                class="flex items-center gap-3 px-3 py-3 rounded-xl hover:bg-[var(--color-bg)] transition group"
              >
                <span class="w-7 h-7 rounded-lg bg-[var(--color-brand)]/10 text-[var(--color-brand)] flex items-center justify-center">
                  <i class="fa-solid fa-trophy text-xs"></i>
                </span>
                <span class="font-semibold text-[var(--color-nav)]">{{ quiz.title }}</span>
                <i class="fa-solid fa-arrow-right ml-auto text-gray-300 group-hover:text-[var(--color-brand)] group-hover:translate-x-1 transition-all"></i>
              </NuxtLink>
            </li>
            <li v-if="!recentQuizzes.length" class="px-4 py-10 text-center text-gray-400">
              <i class="fa-regular fa-clock text-2xl mb-2 block"></i>
              Momenteel geen acties — kom snel terug!
            </li>
          </ul>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";
import { apiFetch } from "~/composables/adapter/useApi";
import { useCmsContent } from "~/composables/content/useCmsContent";
import type { Brand } from "~/types/brand";

const { content } = useCmsContent("home");

const recentBrands = ref<string[]>([]);

interface QuizPreview {
  id: number;
  title: string;
  brand: { slug: string; title: string };
}
const recentQuizzes = ref<QuizPreview[]>([]);

onMounted(async () => {
  try {
    const brands = await apiFetch<Brand[]>("/brands", {
      params: { accepted: 1, limit: 5, order: "desc" },
    });
    recentBrands.value = brands.map((b) => b.title);

    recentQuizzes.value = await apiFetch<QuizPreview[]>("/quizzes", {
      params: { limit: 5, order: "desc" },
    });
  } catch (e) {
    console.error(e);
  }
});
</script>
