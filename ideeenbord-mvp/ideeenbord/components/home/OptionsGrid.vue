<template>
  <section class="py-16">
    <!-- Titel & intro --------------------------------------------------- -->
    <div class="max-w-5xl mx-auto text-center mb-12 px-4">
      <h2 class="text-3xl md:text-4xl font-bold dark-text mb-3">
        {{ content["options-title"] }}
      </h2>
      <p class="text-base md:text-lg light-text">
        {{ content["options-intro"] }}
      </p>
    </div>

    <!-- Keuzekaarten ---------------------------------------------------- -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-5xl mx-auto px-4">
      <div
        v-for="(card, idx) in cards"
        :key="idx"
        class="relative bg-nav rounded-2xl p-8 glow-box h-full flex flex-col items-center text-center"
      >
        <!-- inhoud -->
        <h3 class="text-2xl font-bold text-white mb-2 z-10">
          {{ card.title }}
        </h3>

        <p class="text-gray-300 mb-6 z-10">
          {{ card.text }}
        </p>

        <!-- knop altijd onderaan -->
        <NuxtLink :to="card.link" class="mt-auto z-10">
          <button class="cta">
            {{ card.button }}
          </button>
        </NuxtLink>

        <!-- glow -->
        <div
          class="absolute -inset-2 rounded-2xl pointer-events-none glow-overlay"
        />
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
  [1, 2, 3, 4].map((n) => ({
    title: content.value[`option${n}-title`] ?? "",
    text: content.value[`option${n}-text`] ?? "",
    button: content.value[`option${n}-button`] ?? "",
    link: content.value[`option${n}-link`] ?? "#",
  }))
);
</script>

<style scoped>
/* kleur-utils (consistent met andere componenten) */
.dark-text {
  color: var(--color-text-dark, #111);
}
.light-text {
  color: var(--color-text-light, #666);
}

/* oranje glow + rand */
.glow-box {
  position: relative;
  box-shadow: 0 0 16px 0 var(--color-brand), 0 0 0 4px var(--color-brand);
}
.glow-overlay {
  background: radial-gradient(
    circle at 50% 50%,
    var(--color-brand) 0%,
    transparent 40%
  );
  opacity: 0.18;
  z-index: -1;
}

/* .cta-knop is project-breed gestyled */
</style>
