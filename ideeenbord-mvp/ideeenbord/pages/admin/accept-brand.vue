<!--
  Admin page to accept pending brands before they become public.
-->
<script setup lang="ts">
import { onMounted } from "vue";
import { useAdmin } from "~/composables/admin/useAdmin";

definePageMeta({
  middleware: "admin",
});

const { pendingBrands, fetchPendingBrands, acceptBrand, error } = useAdmin();

onMounted(() => {
  fetchPendingBrands();
});
</script>

<template>
  <div>
    <h2>Pending Brands</h2>
    <p v-if="error" style="color: red">{{ error }}</p>
    <ul v-if="pendingBrands.length">
      <li v-for="brand in pendingBrands" :key="brand.id">
        {{ brand.title }}
        <button @click="acceptBrand(brand.id)">✅ Accept</button>
      </li>
    </ul>
    <p v-else>No pending brands.</p>
  </div>
</template>
