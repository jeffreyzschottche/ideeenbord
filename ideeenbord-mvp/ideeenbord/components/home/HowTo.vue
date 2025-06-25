<template>
  <section class="w-full py-10 md:py-20">
    <div class="flex flex-col items-center text-center mb-10 md:mb-16">
      <h2 class="text-2xl md:text-3xl font-bold dark-text">
        {{ content["howto-title"] }}
      </h2>
      <p class="mt-2 text-base md:text-lg dark-text max-w-xl">
        {{ content["howto-description"] }}
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-10 md:gap-8 items-start">
      <div class="flex flex-col items-center text-center">
        <img
          :src="content['howto-step1-image']"
          alt="Stap 1"
          class="rounded-xl w-full h-auto max-w-[400px] aspect-[4/2.5] object-cover"
        />
        <h3 class="mt-4 text-lg md:text-xl font-bold dark-text">
          {{ content["howto-step1-title"] }}
        </h3>
        <p class="mt-2 text-base md:text-base dark-text">
          {{ content["howto-step1-description"] }}
        </p>
      </div>

      <div class="flex flex-col items-center text-center">
        <img
          :src="content['howto-step2-image']"
          alt="Stap 2"
          class="rounded-xl w-full h-auto max-w-[400px] aspect-[4/2.5] object-cover"
        />
        <h3 class="mt-4 text-lg md:text-xl font-bold dark-text">
          {{ content["howto-step2-title"] }}
        </h3>
        <p class="mt-2 text-base md:text-base dark-text">
          {{ content["howto-step2-description"] }}
        </p>
      </div>

      <div class="flex flex-col items-center text-center">
        <img
          :src="content['howto-step3-image']"
          alt="Stap 3"
          class="rounded-xl w-full h-auto max-w-[400px] aspect-[4/2.5] object-cover"
        />
        <h3 class="mt-4 text-lg md:text-xl font-bold dark-text">
          {{ content["howto-step3-title"] }}
        </h3>
        <p class="mt-2 text-base md:text-base dark-text">
          {{ content["howto-step3-description"] }}
        </p>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { useCmsContent } from "~/composables/content/useCmsContent";
const { content } = useCmsContent("home");
</script>
