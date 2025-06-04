<script setup lang="ts">
/*
  This page displays a list of all brands.
  On page mount, it fetches all brands from the API and renders them with links.
*/

import { ref, onMounted } from "vue";
import { apiFetch } from "~/composables/useApi";

const brands = ref([]);

// Fetch all brands on page load
onMounted(async () => {
  brands.value = await apiFetch("/brands");
});
</script>

<script lang="ts">
// Converts a brand title into a URL-friendly slug
function slugify(text: string) {
  return text
    .toLowerCase()
    .replace(/\s+/g, "-")
    .replace(/[^\w\-]+/g, "");
}
</script>

<template>
  <div>
    <h1>Alle Brands</h1>
    <ul>
      <li v-for="brand in brands" :key="brand.id">
        <NuxtLink :to="`/brands/${slugify(brand.title)}`">
          <h2>{{ brand.title }}</h2>
          <p>{{ brand.intro_short }}</p>
        </NuxtLink>
      </li>
    </ul>
  </div>
</template>
