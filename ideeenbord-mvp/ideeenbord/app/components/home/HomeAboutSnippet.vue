<template>
  <section class="py-16 md:py-24">
    <div class="flex flex-col-reverse md:flex-row items-center gap-10 md:gap-16">
      <!-- Tekst -->
      <div class="flex-1">
        <span class="inline-block text-xs font-bold uppercase tracking-[0.2em] text-[var(--color-brand)] mb-3">
          Wat is Ideeënbord?
        </span>
        <h2
          v-if="content['home-title']"
          class="text-3xl md:text-4xl font-extrabold text-[var(--color-nav)] leading-tight"
        >
          {{ content["home-title"] }}
        </h2>
        <p
          v-if="content['home-description']"
          class="mt-5 text-lg text-gray-600 leading-relaxed max-w-xl"
        >
          {{ content["home-description"] }}
        </p>

        <ul class="mt-6 space-y-3">
          <li class="flex items-start gap-3 text-gray-700">
            <i class="fa-solid fa-check mt-1 text-[var(--color-brand)]"></i>
            <span>Plaats ideeën, wensen en verbeterpunten bij jouw favoriete merken.</span>
          </li>
          <li class="flex items-start gap-3 text-gray-700">
            <i class="fa-solid fa-check mt-1 text-[var(--color-brand)]"></i>
            <span>Stem op ideeën van anderen en geef ze meer gewicht.</span>
          </li>
          <li class="flex items-start gap-3 text-gray-700">
            <i class="fa-solid fa-check mt-1 text-[var(--color-brand)]"></i>
            <span>Zie wat merken met de beste ideeën doen.</span>
          </li>
        </ul>

        <NuxtLink to="/about" class="cta inline-flex mt-8 px-6 py-3">
          {{ content["home-cta"] || "Zo werkt het" }}
        </NuxtLink>
      </div>

      <!-- Afbeelding -->
      <div class="flex-1 w-full">
        <div class="relative">
          <div class="absolute -inset-3 rounded-[2rem] bg-[var(--color-brand)]/10 -z-10 rotate-3"></div>
          <img
            v-if="content['home-about-image']"
            :src="content['home-about-image']"
            alt="Ideeënbord"
            class="w-full rounded-3xl shadow-2xl object-cover"
          />
          <div
            v-else
            class="w-full aspect-[4/3] rounded-3xl bg-[var(--color-nav)] flex items-center justify-center"
          >
            <i class="fa-solid fa-lightbulb text-7xl text-[var(--color-brand)] lamp-glow"></i>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { useCmsContent } from "~/composables/content/useCmsContent";
const { content } = useCmsContent("home");
</script>
