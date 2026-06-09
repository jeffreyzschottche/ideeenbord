<template>
  <div v-if="ready" class="py-12 font-default">
    <div class="max-w-3xl mx-auto px-4 md:px-0">
      <h1
        class="text-3xl md:text-4xl font-bold text-center mb-8 text-[var(--color-brand)]"
      >
        {{ content["become-title"] }}
      </h1>

      <ol class="space-y-6">
        <li v-for="step in steps" :key="step.id" class="about-card">
          <h2 class="text-xl font-bold mb-2 text-[var(--color-brand)]">
            {{ step.title }}
          </h2>
          <p class="mb-4">{{ step.description }}</p>
          <NuxtLink v-if="step.link" :to="step.link" class="btn-primary">
            {{ step.linkLabel }}
          </NuxtLink>
        </li>
      </ol>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { useCmsContent } from "~/composables/content/useCmsContent";

const { content, isLoading } = useCmsContent("become-a-brandowner");
const ready = computed(
  () => !isLoading.value && Object.keys(content.value).length > 0
);

const steps = computed(() => {
  const c = content.value;
  return [
    {
      id: 1,
      title: c["step1-title"] ?? "",
      description: c["step1-description"] ?? "",
      link: c["step1-link"] ?? null,
      linkLabel: c["step1-link-label"] ?? null,
    },
    {
      id: 2,
      title: c["step2-title"] ?? "",
      description: c["step2-description"] ?? "",
    },
    {
      id: 3,
      title: c["step3-title"] ?? "",
      description: c["step3-description"] ?? "",
      link: c["step3-link"] ?? null,
      linkLabel: c["step3-link-label"] ?? null,
    },
    {
      id: 4,
      title: c["step4-title"] ?? "",
      description: c["step4-description"] ?? "",
    },
    {
      id: 5,
      title: c["step5-title"] ?? "",
      description: c["step5-description"] ?? "",
    },
  ];
});
</script>
