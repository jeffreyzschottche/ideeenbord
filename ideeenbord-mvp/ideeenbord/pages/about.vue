<template>
  <div v-if="ready" class="font-default">
    <h1
      class="text-3xl md:text-4xl font-bold text-center mb-8 text-[var(--color-brand)]"
    >
      {{ content["hero-title"] }}
    </h1>
    <p class="text-lg text-center mb-8 text-dark">
      {{ content["hero-subtitle"] }}
    </p>

    <!-- BRAND -->
    <section class="py-8">
      <AboutBrandSection :data="brandData" />
    </section>

    <!-- FANS -->
    <section class="py-8">
      <AboutFanSection :data="fanData" />
    </section>
  </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { useCmsContent } from "~/composables/content/useCmsContent";

const { content, isLoading } = useCmsContent("uitleg");
const ready = computed(
  () => !isLoading.value && Object.keys(content.value).length > 0
);

const brandData = computed(() => {
  const c = content.value;
  return {
    title: c["brand-title"] ?? "",
    description: c["brand-description"] ?? "",
    steps: [
      {
        id: 1,
        title: c["brand-step1-title"] ?? "",
        description: c["brand-step1-description"] ?? "",
      },
      {
        id: 2,
        title: c["brand-step2-title"] ?? "",
        description: c["brand-step2-description"] ?? "",
      },
    ],
    cta: { label: c["brand-cta-label"] ?? "", to: c["brand-cta-link"] ?? "#" },
    image: c["brand-image"] ?? "",
  };
});

const fanData = computed(() => {
  const c = content.value;
  return {
    title: c["fan-title"] ?? "",
    description: c["fan-description"] ?? "",
    cta: { label: c["fan-cta-label"] ?? "", to: c["fan-cta-link"] ?? "#" },
    image: c["fan-image"] ?? "",
  };
});
</script>
