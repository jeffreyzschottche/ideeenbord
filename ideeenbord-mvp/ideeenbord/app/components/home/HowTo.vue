<template>
  <section class="py-16 md:py-24">
    <div class="text-center max-w-2xl mx-auto mb-12">
      <span class="inline-block text-xs font-bold uppercase tracking-[0.2em] text-[var(--color-brand)] mb-3">
        Zo simpel is het
      </span>
      <h2 class="text-3xl md:text-4xl font-extrabold text-[var(--color-nav)]">
        In drie stappen van idee naar actie
      </h2>
      <p class="mt-4 text-lg text-gray-600">
        Geen gedoe, geen wachttijden. Deel je idee en zie wat er gebeurt.
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      <div
        v-for="(step, idx) in steps"
        :key="idx"
        class="group relative rounded-3xl bg-white border border-gray-100 shadow-[0_8px_30px_rgba(31,41,55,0.06)] p-6 transition-transform duration-300 hover:-translate-y-1"
      >
        <span
          class="absolute -top-4 left-6 w-10 h-10 rounded-full bg-[var(--color-brand)] text-white font-bold flex items-center justify-center shadow-lg"
        >{{ idx + 1 }}</span>

        <div class="rounded-2xl overflow-hidden aspect-[4/2.6] bg-[var(--color-bg)] mb-5 flex items-center justify-center">
          <img
            v-if="content[`howto-step${idx + 1}-image`]"
            :src="content[`howto-step${idx + 1}-image`]"
            :alt="step.title"
            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
          />
          <div v-else class="w-full h-full flex items-center justify-center">
            <i :class="['text-4xl text-[var(--color-brand)]', step.icon]"></i>
          </div>
        </div>

        <h3 class="text-lg font-bold text-[var(--color-nav)]">
          {{ content[`howto-step${idx + 1}-title`] || step.title }}
        </h3>
        <p class="mt-2 text-gray-600">
          {{ content[`howto-step${idx + 1}-description`] || step.description }}
        </p>

        <!-- Voorbeeld -->
        <div v-if="step.example" class="mt-4 p-3 rounded-xl bg-[var(--color-bg)] border border-gray-100">
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Voorbeeld</p>
          <p class="text-sm text-[var(--color-nav)] italic">"{{ step.example }}"</p>
        </div>
      </div>
    </div>

    <!-- Extra uitleg -->
    <div class="mt-12 text-center">
      <p class="text-gray-600 mb-4">
        Al <strong class="text-[var(--color-nav)]">127+ ideeën</strong> zijn gerealiseerd door merken op ons platform.
      </p>
      <NuxtLink to="/about" class="inline-flex items-center gap-2 font-semibold text-[var(--color-brand)] hover:underline">
        Lees meer succesverhalen <i class="fa-solid fa-arrow-right text-xs"></i>
      </NuxtLink>
    </div>
  </section>
</template>

<script setup lang="ts">
import { useCmsContent } from "~/composables/content/useCmsContent";
const { content } = useCmsContent("home");

const steps = [
  {
    icon: "fa-solid fa-lightbulb",
    title: "Deel je idee",
    description: "Kies een merk en vertel wat jij anders wilt. Van kleine verbeteringen tot grote innovaties — alles is welkom.",
    example: "Ik zou graag een vegan optie zien in jullie lunchassortiment.",
  },
  {
    icon: "fa-solid fa-users",
    title: "Verzamel stemmen",
    description: "Andere consumenten stemmen op jouw idee. Hoe meer likes, hoe meer aandacht het krijgt van het merk.",
    example: null,
  },
  {
    icon: "fa-solid fa-check-circle",
    title: "Zie de impact",
    description: "Het merk bekijkt populaire ideeën en neemt actie. Je krijgt updates wanneer je idee wordt opgepakt of gerealiseerd.",
    example: null,
  },
];
</script>
