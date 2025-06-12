<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-4">{{ content["home-title"] }}</h1>

    <img
      v-if="content['header-home-image']"
      :src="content['header-home-image']"
      class="max-h-64 rounded shadow"
    />

    <div v-if="isLoading" class="text-gray-500">Laden...</div>
    <div v-if="error" class="text-red-500">Fout: {{ error }}</div>
  </div>
</template>

<script setup lang="ts">
import { useCmsContent } from "~/composables/content/useCmsContent";

const { content, isLoading, error } = useCmsContent("home");
</script>
