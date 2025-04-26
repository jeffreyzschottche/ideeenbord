<script setup lang="ts">
import { ref, onMounted } from "vue";
import { apiFetch } from "~/composables/useApi";

const brands = ref([]);

onMounted(async () => {
  brands.value = await apiFetch("/brands");
});
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

<script lang="ts">
function slugify(text: string) {
  return text
    .toLowerCase()
    .replace(/\s+/g, "-")
    .replace(/[^\w\-]+/g, "");
}
</script>
