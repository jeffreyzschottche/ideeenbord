<!--
  This page is used by administrators to verify pending brand owners.
  It fetches a list of brand owners who have registered but are not yet verified.
  Admins can see details of each owner and approve them by clicking a verify button.
-->

<script setup lang="ts">
import { onMounted } from "vue";
import { useAdmin } from "~/composables/useAdmin";

// Set the middleware to restrict access to admins only
definePageMeta({
  middleware: "admin",
});

// Destructure state and functions from the admin composable
const { owners, fetchPendingOwners, verifyOwner, error } = useAdmin();

// Fetch the list of pending brand owners when the component is mounted
onMounted(() => {
  fetchPendingOwners();
});
</script>

<template>
  <div>
    <h2>Unverified Brand Owners</h2>
    <!-- Display error message if present -->
    <p v-if="error" style="color: red">{{ error }}</p>

    <!-- Display list of pending owners if available -->
    <ul v-if="owners.length">
      <li v-for="owner in owners" :key="owner.id">
        {{ owner.name }} ({{ owner.email }})
        <span v-if="owner.brand_id">
          wants to claim brand ID {{ owner.brand_id }}
        </span>
        <span v-else>has not yet linked a brand</span>
        <button @click="verifyOwner(owner.id)">✅ Verify</button>
      </li>
    </ul>

    <!-- Fallback message if no pending owners exist -->
    <p v-else>No verification requests.</p>
  </div>
</template>
