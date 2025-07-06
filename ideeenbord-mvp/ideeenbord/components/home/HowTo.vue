<template>
  <section class="w-full py-10 md:py-20">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-10 md:gap-8 items-start">
      <div
        v-for="n in [1, 2, 3]"
        :key="n"
        class="flex flex-col items-center text-center animate-fade-in-up"
      >
        <!-- ORANJE BORDER + BG + HOVER-EFFECT -->
        <div
          class="rounded-2xl border-4 border-brand bg-brand/5 transition-transform duration-300 hover:-translate-y-1 hover:shadow-xl w-full max-w-[400px] aspect-[4/2.5] overflow-hidden"
        >
          <img
            :src="content[`howto-step${n}-image`]"
            :alt="`Stap ${n}`"
            class="w-full h-full object-cover rounded-xl"
          />
        </div>

        <!-- tekst -->
        <h3 class="mt-4 text-lg md:text-xl font-bold dark-text">
          {{ content[`howto-step${n}-title`] }}
        </h3>
        <p class="mt-2 text-base md:text-base dark-text">
          {{ content[`howto-step${n}-description`] }}
        </p>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { useCmsContent } from "~/composables/content/useCmsContent";
const { content } = useCmsContent("home");
</script>
