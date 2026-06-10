<script setup lang="ts">
/**
 * Consistent page hero used across all top-level pages so the header is
 * identical in size and rhythm everywhere.
 */
defineProps<{
  eyebrow?: string;
  title?: string;
  subtitle?: string;
  image?: string;
}>();
</script>

<template>
  <section class="relative overflow-hidden">
    <!-- soft background accent -->
    <div class="absolute inset-x-0 -top-24 h-64 bg-gradient-to-b from-[var(--color-brand)]/8 to-transparent -z-10"></div>

    <div class="container mx-auto px-4 pt-12 md:pt-16 pb-8 md:pb-10 text-center">
      <span
        v-if="eyebrow"
        class="inline-block text-xs font-bold uppercase tracking-[0.2em] text-[var(--color-brand)] mb-3"
      >{{ eyebrow }}</span>

      <h1 class="text-3xl md:text-5xl font-extrabold text-[var(--color-nav)] leading-tight max-w-3xl mx-auto">
        <slot name="title">{{ title }}</slot>
      </h1>

      <p
        v-if="subtitle || $slots.subtitle"
        class="mt-4 text-lg text-gray-600 max-w-2xl mx-auto"
      >
        <slot name="subtitle">{{ subtitle }}</slot>
      </p>

      <div v-if="$slots.cta" class="mt-7 flex flex-wrap items-center justify-center gap-3">
        <slot name="cta" />
      </div>

      <div v-if="image" class="mt-10 max-w-3xl mx-auto">
        <img
          :src="image"
          alt=""
          class="w-full aspect-[16/7] object-cover rounded-3xl shadow-xl"
        />
      </div>
    </div>
  </section>
</template>
