<script setup lang="ts">
/*
  This page allows a user to claim an unverified brand by submitting a form.
  The available brands are fetched on mount and shown in a dropdown.
  After submission, the brand claim is processed and a success or error message is triggered.
*/

import { ref, onMounted } from "vue";
import type { ClaimForm } from "~/types/brand";
import { useBrand } from "~/composables/brand/useBrand";
import { apiFetch } from "~/composables/adapter/useApi";
import { useResponseDisplay } from "~/composables/notifications/useResponseDisplay";

// Form data for claiming a brand
const form = ref<ClaimForm>({
  brandId: "",
  name: "",
  email: "",
  phone: "",
  url: "",
  subscriptionPlan: "Brons",
  password: "",
});

const { claimBrand, error } = useBrand();
const brands = ref<{ id: number; title: string }[]>([]);
const { triggerByKey } = useResponseDisplay();

// Fetch all unverified brands on mount
onMounted(async () => {
  try {
    const data = await apiFetch<{ id: number; title: string }[]>(
      "/brands?accepted=1",
      {
        params: { verified: 0 },
      }
    );
    brands.value = data;
  } catch (err: any) {
    triggerByKey("claim-load-failed");
  }
});

// Handle form submission to claim a brand
async function handleSubmit() {
  try {
    await claimBrand(form.value);
    triggerByKey("claim-submitted");
  } catch (e) {
    triggerByKey("claim-failed");
  }
}
</script>

<template>
  <form @submit.prevent="handleSubmit">
    <select v-model="form.brandId" required>
      <option value="">Kies een merk</option>
      <option v-for="brand in brands" :value="brand.id" :key="brand.id">
        {{ brand.title }}
      </option>
    </select>

    <input v-model="form.name" placeholder="Naam" required />
    <input v-model="form.email" type="email" placeholder="Email" required />
    <input v-model="form.phone" placeholder="Telefoonnummer" />
    <input v-model="form.url" placeholder="Website" />

    <select v-model="form.subscriptionPlan" required>
      <option>Brons</option>
      <option>Zilver</option>
      <option>Goud</option>
    </select>

    <input
      v-model="form.password"
      type="password"
      placeholder="Wachtwoord"
      required
    />

    <button type="submit">Merk claimen</button>
  </form>
</template>
