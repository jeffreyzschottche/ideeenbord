<script setup lang="ts">
import { useAdmin } from "~/composables/useAdmin";

definePageMeta({
  middleware: "admin",
});

const { owners, fetchPendingOwners, verifyOwner, error } = useAdmin();

onMounted(() => {
  fetchPendingOwners();
});
</script>

<template>
  <div>
    <h2>Niet-geverifieerde Merkeigenaars</h2>
    <p v-if="error" style="color: red">{{ error }}</p>
    <ul v-if="owners.length">
      <li v-for="owner in owners" :key="owner.id">
        {{ owner.name }} ({{ owner.email }}) wil merk ID
        {{ owner.brand_id }} claimen
        <button @click="verifyOwner(owner.id)">✅ Verifieer</button>
      </li>
    </ul>
    <p v-else>Geen aanvragen om te verifiëren.</p>
  </div>
</template>
