<template>
  <div>
    <h2>Beveiligd bericht van de API:</h2>
    <p v-if="message">{{ message }}</p>
    <p v-if="error" style="color: red">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { apiFetch } from "~/composables/useApi";

const message = ref("");
const error = ref("");

definePageMeta({
  middleware: "auth",
});

onMounted(async () => {
  const token = localStorage.getItem("token");

  if (!token) {
    error.value = "Geen token gevonden. Log eerst in.";
    return;
  }

  try {
    let response = await apiFetch("/bye");
    message.value = response;
  } catch (err) {
    error.value = err?.message || "Beveiligde data ophalen mislukt";
  }
});
</script>
