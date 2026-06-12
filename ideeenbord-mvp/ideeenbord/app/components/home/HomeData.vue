<template>
  <section class="py-16 md:py-24">
    <div class="flex flex-col md:flex-row items-center gap-10 md:gap-16">
      <!-- Stats panel -->
      <div class="flex-1 w-full">
        <div class="relative rounded-3xl bg-[var(--color-nav)] p-8 md:p-10 overflow-hidden">
          <!-- subtiele accent-cirkel -->
          <div class="absolute -top-16 -right-16 w-48 h-48 rounded-full bg-[var(--color-brand)]/20 blur-2xl"></div>

          <div class="relative grid grid-cols-2 gap-6 md:gap-8" v-reveal="{ stagger: 0.1, y: 24 }">
            <div
              v-for="stat in stats"
              :key="stat.key"
              class="rounded-2xl bg-white/5 border border-white/10 p-5 text-center"
            >
              <span v-countup class="block text-3xl md:text-4xl font-extrabold text-[var(--color-brand)]">
                +{{ content[stat.key] || "0" }}
              </span>
              <span class="mt-1 block text-sm text-gray-300">
                {{ content[stat.label] }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Motivatie -->
      <div class="flex-1">
        <span class="inline-block text-xs font-bold uppercase tracking-[0.2em] text-[var(--color-brand)] mb-3">
          Waarom Ideeënbord
        </span>
        <h2
          v-if="content['motivation-title']"
          class="text-3xl md:text-4xl font-extrabold text-[var(--color-nav)] leading-tight"
        >
          {{ content["motivation-title"] }}
        </h2>
        <p
          v-if="content['motivation-description']"
          class="mt-5 text-lg text-gray-600 leading-relaxed"
        >
          {{ content["motivation-description"] }}
        </p>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { useCmsContent } from "~/composables/content/useCmsContent";
const { content } = useCmsContent("home");

const stats = [
  { key: "stat-companies", label: "stat-companies-label" },
  { key: "stat-idea-makers", label: "stat-idea-makers-label" },
  { key: "stat-ideas-submitted", label: "stat-ideas-submitted-label" },
  { key: "stat-ideas-implemented", label: "stat-ideas-implemented-label" },
];
</script>
