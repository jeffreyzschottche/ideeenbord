<template>
  <NuxtLayout>
    <NuxtPage />
  </NuxtLayout>
  <ResponseDisplay />
</template>
<script setup lang="ts">
import { useUserAuthStore } from "~/store/useUserAuthStore";
import ResponseDisplay from "~/components/ResponseDisplay.vue";
import { useBrandOwnerAuth } from "~/composables/useBrandOwnerAuth";
useBrandOwnerAuth().initAuth();

useUserAuthStore().initAuth();
</script>
