<script setup lang="ts">
import { ref, onMounted } from "vue";
import type { ClaimForm } from "~/types/brand";
import { useClaimBrand } from "~/composables/useBrand";

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
    const response = await fetch("http://localhost:8000/api/brands?verified=0");

    if (!response.ok) throw new Error("API Error");

    const data = await response.json();
    brands.value = data;
  } catch (err) {
    console.error(err);
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
