<script setup lang="ts">
import { ref, onMounted } from "vue";
import { apiFetch } from "~/composables/useApi";
import { useRoute } from "vue-router";

const brand = ref(null);
const route = useRoute();

onMounted(async () => {
  brand.value = await apiFetch(`/brands/${route.params.slug}`);
});
</script>

<template>
  <div v-if="brand">
    <h1>{{ brand.title }}</h1>
    <p>{{ brand.intro }}</p>
    <p>Email: {{ brand.email }}</p>
    <a :href="brand.website_url" target="_blank">Website</a>
  </div>
</template>
