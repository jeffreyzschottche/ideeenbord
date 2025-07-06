<template>
  <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
    <NewsCard v-for="a in articles" :key="a.slug" :article="a" />
  </div>
</template>

<script setup lang="ts">
defineProps<{
  articles: { title: string; slug: string; excerpt: string; image: string }[];
}>();
</script>
