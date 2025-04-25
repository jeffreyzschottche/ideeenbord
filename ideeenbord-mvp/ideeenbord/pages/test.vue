<template>
  <div>
    <h2>Beveiligd bericht van de API:</h2>
    <p v-if="message">{{ message }}</p>
    <p v-if="error" style="color: red">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";

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
    const response = await fetch("http://localhost:8000/api/bye", {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      },
    });

    if (!response.ok) {
      throw new Error(`Fout ${response.status}: ${response.statusText}`);
    }

    const result = await response.json();
    message.value = result.message;
  } catch (err) {
    error.value = err.message;
  }
});
</script>
