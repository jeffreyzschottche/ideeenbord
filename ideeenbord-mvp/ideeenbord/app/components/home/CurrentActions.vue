<template>
  <section class="py-16 md:py-24">
    <div class="grid md:grid-cols-[1fr_auto_1fr] items-center gap-8 md:gap-6">
      <!-- Nieuwe merken -->
      <div class="rounded-3xl bg-[var(--color-nav)] p-7 h-full">
        <h3 class="text-lg md:text-xl font-bold text-white text-center mb-5">
          {{ content["currentactions-left-title"] || "Nieuwe deelnemende merken" }}
        </h3>
        <ul class="space-y-2">
          <li v-for="(name, idx) in recentBrands" :key="idx">
            <NuxtLink
              :to="`/brands/${name}`"
              class="block px-4 py-2.5 rounded-xl bg-white/5 hover:bg-[var(--color-brand)] text-white text-center transition-colors"
            >{{ name }}</NuxtLink>
          </li>
          <li v-if="!recentBrands.length" class="px-4 py-3 rounded-xl bg-white/5 text-gray-300 text-center">
            Nog geen nieuwe merken.
          </li>
        </ul>
      </div>

      <!-- Lamp -->
      <div class="relative hidden md:flex items-center justify-center w-32 h-32">
        <i class="fa-regular fa-lightbulb absolute text-[6rem] text-[var(--color-nav)] regular-bulb"></i>
        <i class="fa-solid fa-lightbulb absolute text-[6rem] text-[var(--color-brand)] solid-bulb"></i>
      </div>

      <!-- Quizzes -->
      <div class="rounded-3xl bg-[var(--color-nav)] p-7 h-full">
        <h3 class="text-lg md:text-xl font-bold text-white text-center mb-5">
          {{ content["currentactions-right-title"] || "Laatste winacties & quizzes" }}
        </h3>
        <ul class="space-y-2">
          <li v-for="quiz in recentQuizzes" :key="quiz.id">
            <NuxtLink
              :to="`/brands/${quiz.brand.slug}`"
              class="block px-4 py-2.5 rounded-xl bg-white/5 hover:bg-[var(--color-brand)] text-white text-center transition-colors"
            >{{ quiz.title }}</NuxtLink>
          </li>
          <li v-if="!recentQuizzes.length" class="px-4 py-3 rounded-xl bg-white/5 text-gray-300 text-center">
            Momenteel geen acties.
          </li>
        </ul>
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

<style scoped>
@keyframes blink-off {
  0%, 45% { opacity: 1; }
  60%, 90% { opacity: 0; }
  100% { opacity: 1; }
}
@keyframes blink-on {
  0%, 45% { opacity: 0; filter: drop-shadow(0 0 4px var(--color-brand)); }
  60%, 90% { opacity: 1; filter: drop-shadow(0 0 16px var(--color-brand)); }
  100% { opacity: 0; filter: drop-shadow(0 0 4px var(--color-brand)); }
}
.regular-bulb { animation: blink-off 3s ease-in-out infinite; }
.solid-bulb { animation: blink-on 3s ease-in-out infinite; }
</style>
