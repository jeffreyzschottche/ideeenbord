<template>
  <section
    class="w-full flex flex-col-reverse md:flex-row items-center justify-between py-8 md:py-20 gap-8 md:gap-16"
  >
    <!-- Tekstgedeelte -->
    <div class="flex-1 flex flex-col items-start md:items-start gap-4 md:gap-8">
      <!-- Logo uit nav -->
      <NuxtLink to="/" class="relative text-3xl md:text-4xl font-bold mb-2">
        IDEEEN<span class="font-light">BORD</span>
        <i
          class="fa-solid fa-lightbulb ml-2 absolute flex items-center justify-center text-orange-400 lamp-glow"
        ></i>
      </NuxtLink>
      <!-- Titel -->
      <h1
        v-if="content['home-title']"
        class="text-2xl md:text-3xl font-semibold dark-text"
      >
        {{ content["home-title"] }}
      </h1>
      <!-- Korte uitleg -->
      <p
        v-if="content['home-description']"
        class="text-base md:text-lg dark-text max-w-lg"
      >
        {{ content["home-description"] }}
      </p>
      <!-- Call to action knop -->
      <NuxtLink to="/about">
        <button class="cta sm:mt-2 md:mt-4 lg:mt-4 xl:mt-0">
          {{ content["home-cta"] || "Inschrijven" }}
        </button>
      </NuxtLink>
    </div>
    <!-- Afbeelding rechts (optioneel) -->
    <div class="flex-1 flex items-center justify-center">
      <img
        v-if="content['home-about-image']"
        :src="content['home-about-image']"
        alt="Ideeënbord visual"
        class="w-full max-w-xs md:max-w-md rounded-xl shadow-lg object-contain"
      />
    </div>
  </section>
</template>

<script setup lang="ts">
import { useCmsContent } from "~/composables/content/useCmsContent";
const { content, isLoading, error } = useCmsContent("home");
</script>
