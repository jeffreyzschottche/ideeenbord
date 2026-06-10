<template>
  <section class="py-16 md:py-24 bg-[var(--color-bg)]">
    <div class="container mx-auto px-4">
      <div class="max-w-2xl mx-auto text-center mb-12">
        <span class="inline-block text-xs font-bold uppercase tracking-[0.2em] text-[var(--color-brand)] mb-3">
          Voor wie?
        </span>
        <h2 class="text-3xl md:text-4xl font-extrabold text-[var(--color-nav)]">
          {{ content["options-title"] || "Voor iedereen die invloed wil" }}
        </h2>
        <p v-if="content['options-intro']" class="mt-4 text-lg text-gray-600">
          {{ content["options-intro"] }}
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl mx-auto">
        <div
          v-for="(card, idx) in cards"
          :key="idx"
          class="group relative rounded-3xl bg-white border border-gray-100 shadow-[0_8px_30px_rgba(31,41,55,0.06)] p-7 flex flex-col hover:-translate-y-1 hover:shadow-xl transition-all duration-300"
        >
          <span class="w-12 h-12 rounded-2xl bg-[var(--color-brand)]/10 text-[var(--color-brand)] flex items-center justify-center text-xl mb-4">
            <i :class="icons[idx % icons.length]"></i>
          </span>
          <h3 class="text-xl font-bold text-[var(--color-nav)] mb-2">{{ card.title }}</h3>
          <p class="text-gray-600 mb-6 flex-1">{{ card.text }}</p>
          <NuxtLink
            :to="card.link"
            class="inline-flex items-center gap-1.5 font-semibold text-[var(--color-brand)] mt-auto"
          >
            {{ card.button || "Meer weten" }}
            <i class="fa-solid fa-arrow-right text-xs transition-transform group-hover:translate-x-1"></i>
          </NuxtLink>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { useCmsContent } from "~/composables/content/useCmsContent";

const { content } = useCmsContent("home");

const icons = [
  "fa-solid fa-lightbulb",
  "fa-solid fa-magnifying-glass",
  "fa-solid fa-arrow-up-right-dots",
  "fa-solid fa-gift",
];

interface Card {
  title: string;
  text: string;
  button: string;
  link: string;
}

const cards = computed<Card[]>(() =>
  [1, 2, 3, 4]
    .map((n) => ({
      title: content.value[`option${n}-title`] ?? "",
      text: content.value[`option${n}-text`] ?? "",
      button: content.value[`option${n}-button`] ?? "",
      link: content.value[`option${n}-link`] ?? "#",
    }))
    .filter((c) => c.title)
);
</script>
