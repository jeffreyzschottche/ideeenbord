<template>
  <NuxtLayout>
    <NuxtPage />
  </NuxtLayout>
  <ResponseDisplay />
</template>
<script setup lang="ts">
import { useAuthStore } from "~/store/auth";
import ResponseDisplay from "~/components/ResponseDisplay.vue";

useAuthStore().initAuth();
</script>
