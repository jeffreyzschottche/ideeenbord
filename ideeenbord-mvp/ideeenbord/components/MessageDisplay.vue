<template>
  <div>
    <h2>Bericht van de API:</h2>
    <p>{{ message }}</p>
    <p v-if="error" style="color: red">{{ error }}</p>
  </div>
</template>

<script setup lang="ts">
import { useMessage } from "~/composables/useMessage";

const { message, error } = useMessage();
</script>
