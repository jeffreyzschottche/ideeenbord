<template>
  <section class="py-16 md:py-24">
    <div class="text-center max-w-2xl mx-auto mb-12">
      <span class="inline-block text-xs font-bold uppercase tracking-[0.2em] text-[var(--color-brand)] mb-3">
        Zo werkt het
      </span>
      <h2 class="text-3xl md:text-4xl font-extrabold text-[var(--color-nav)]">
        In drie stappen van idee naar impact
      </h2>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      <div
        v-for="n in [1, 2, 3]"
        :key="n"
        class="group relative rounded-3xl bg-white border border-gray-100 shadow-[0_8px_30px_rgba(31,41,55,0.06)] p-6 transition-transform duration-300 hover:-translate-y-1"
      >
        <span
          class="absolute -top-4 left-6 w-10 h-10 rounded-full bg-[var(--color-brand)] text-white font-bold flex items-center justify-center shadow-lg"
        >{{ n }}</span>

        <div class="rounded-2xl overflow-hidden aspect-[4/2.6] bg-[var(--color-bg)] mb-5">
          <img
            v-if="content[`howto-step${n}-image`]"
            :src="content[`howto-step${n}-image`]"
            :alt="`Stap ${n}`"
            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
          />
          <div v-else class="w-full h-full flex items-center justify-center">
            <i class="fa-regular fa-lightbulb text-4xl text-[var(--color-brand)]"></i>
          </div>
        </div>

        <h3 class="text-lg font-bold text-[var(--color-nav)]">
          {{ content[`howto-step${n}-title`] }}
        </h3>
        <p class="mt-2 text-gray-600">
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
