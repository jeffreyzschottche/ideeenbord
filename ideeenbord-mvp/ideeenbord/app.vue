<template>
  <NuxtLayout>
    <NuxtPage />
  </NuxtLayout>
  <ResponseDisplay />
</template>
<script setup lang="ts">
import { useUserAuthStore } from "~/store/useUserAuthStore";
import ResponseDisplay from "~/components/notifications/ResponseDisplay.vue";
import { useBrandOwnerAuth } from "~/composables/brand/useBrandOwnerAuth";
useBrandOwnerAuth().initAuth();

useUserAuthStore().initAuth();
</script>
