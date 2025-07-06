<script setup lang="ts">
/*
  This page handles email verification using a query string.
  It checks for required parameters and attempts to verify the email via API.
  Displays success or error feedback based on the result.
*/

import { useRoute, useRouter } from "vue-router";
import { onMounted, ref } from "vue";
import { useResponseDisplay } from "~/composables/notifications/useResponseDisplay";
import { apiFetch } from "~/composables/adapter/useApi";

const route = useRoute();
const router = useRouter();
const status = ref<"loading" | "success" | "error">("loading");
const message = ref("");

const { trigger } = useResponseDisplay();

onMounted(async () => {
  const id = route.query.id;
  const hash = route.query.hash;

  if (!id || !hash) {
    status.value = "error";
    message.value = "Verificatiegegevens ontbreken.";
    return;
  }

  try {
    await apiFetch(`/verify-email`, {
      method: "GET",
      params: {
        id: route.query.id,
        hash: route.query.hash,
        expires: route.query.expires,
        signature: route.query.signature,
      },
    });
    status.value = "success";
    message.value = "Email succesvol geverifieerd. Je kunt nu inloggen.";
  } catch (err) {
    status.value = "error";
    message.value = "Verificatie mislukt of link is verlopen.";
  }
});
</script>

<template>
  <div v-if="status === 'loading'">Verifiëren...</div>
  <div v-else-if="status === 'success'">
    <h2>{{ message }}</h2>
    <NuxtLink to="/login">➡️ Naar inloggen</NuxtLink>
  </div>
  <div v-else>
    <h2>{{ message }}</h2>
  </div>
</template>
