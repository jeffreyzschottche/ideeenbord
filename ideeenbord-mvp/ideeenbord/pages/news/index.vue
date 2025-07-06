<template>
  <div v-if="ready" class="py-12">
    <h1
      class="text-3xl md:text-4xl font-bold text-center mb-12 text-[var(--color-brand)]"
    >
      {{ content["news-title"] }}
    </h1>

    <NewsGrid :articles="articles" />
  </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { useCmsContent } from "~/composables/content/useCmsContent";

const { content, isLoading } = useCmsContent("news");
const ready = computed(
  () => !isLoading.value && Object.keys(content.value).length
);

const articles = computed(() => {
  const c = content.value;
  const list = [];
  let i = 1;
  while (c[`article${i}-title`]) {
    list.push({
      title: c[`article${i}-title`],
      slug: c[`article${i}-slug`],
      excerpt: c[`article${i}-excerpt`],
      image: c[`article${i}-image`],
    });
    i++;
  }
  return list;
});
</script>
