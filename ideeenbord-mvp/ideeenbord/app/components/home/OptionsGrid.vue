<template>
  <section class="py-16 md:py-24">
    <div class="max-w-2xl mx-auto text-center mb-12">
      <span class="inline-block text-xs font-bold uppercase tracking-[0.2em] text-[var(--color-brand)] mb-3">
        Voor wie?
      </span>
      <h2 class="text-3xl md:text-4xl font-extrabold text-[var(--color-nav)]">
        {{ content["options-title"] }}
      </h2>
      <p class="mt-4 text-lg text-gray-600">
        {{ content["options-intro"] }}
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-5xl mx-auto">
      <div
        v-for="(card, idx) in cards"
        :key="idx"
        class="group relative overflow-hidden rounded-3xl bg-[var(--color-nav)] p-8 flex flex-col text-center items-center transition-transform duration-300 hover:-translate-y-1"
      >
        <div class="absolute -top-12 -right-12 w-40 h-40 rounded-full bg-[var(--color-brand)]/15 blur-2xl transition-opacity group-hover:opacity-80"></div>
        <h3 class="relative text-2xl font-bold text-white mb-3">{{ card.title }}</h3>
        <p class="relative text-gray-300 mb-7 max-w-sm">{{ card.text }}</p>
        <NuxtLink :to="card.link" class="relative mt-auto cta px-6 py-3">
          {{ card.button || "Meer weten" }}
        </NuxtLink>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { useCmsContent } from "~/composables/content/useCmsContent";

const { content } = useCmsContent("home");

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
