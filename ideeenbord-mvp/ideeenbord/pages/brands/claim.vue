<script setup lang="ts">
import { ref, onMounted } from "vue";
import type { ClaimForm } from "~/types/brand";
import { useClaimBrand } from "~/composables/useBrand";
import { apiFetch } from "~/composables/useApi";

const form = ref<ClaimForm>({
  brandId: "",
  name: "",
  email: "",
  phone: "",
  url: "",
  subscriptionPlan: "Brons",
  password: "",
});

const { claimBrand, error } = useClaimBrand();
const brands = ref<{ id: number; title: string }[]>([]);

onMounted(async () => {
  try {
    const data = await apiFetch<{ id: number; title: string }[]>("/brands", {
      params: { verified: 0 },
    });
    brands.value = data;
  } catch (err: any) {
    error.value = err?.message || "Merken ophalen mislukt.";
  }
});

async function handleSubmit() {
  try {
    await claimBrand(form.value);
    alert("Merkclaim verstuurd!");
  } catch (e) {
    alert(error.value);
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
