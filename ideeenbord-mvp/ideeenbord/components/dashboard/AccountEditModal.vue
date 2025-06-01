<script setup lang="ts">
import { ref, onMounted, computed } from "vue";
import { useBrandOwnerAuthStore } from "~/store/useBrandOwnerAuthStore";
import { useResponseDisplay } from "~/composables/useResponseDisplay";
import type { BrandOwner, UpdateBrandOwnerForm } from "~/types/brand-owner";
import { brandOwnerService } from "~/services/api/brandOwnerService";

definePageMeta({
  middleware: "brand-owner",
});

const authStore = useBrandOwnerAuthStore();
const { trigger } = useResponseDisplay();
const owner = computed<BrandOwner | null>(() => authStore.owner);

const form = ref<UpdateBrandOwnerForm>({
  email: "",
  phone: "",
  subscription_plan: "Brons",
  password: "",
  password_confirmation: "",
});

onMounted(() => {
  if (owner.value) {
    form.value.email = owner.value.email;
    form.value.phone = owner.value.phone || "";
    form.value.subscription_plan = owner.value.subscription_plan;
  }
});

async function handleSubmit() {
  try {
    await brandOwnerService.updateAccount(form.value);
    trigger("Gegevens bijgewerkt!", "success");
    await authStore.initAuth(); // opnieuw laden
  } catch (err: any) {
    trigger(err.message || "Fout bij bijwerken", "error");
  }
}
</script>

<template>
  <div class="max-w-xl mx-auto mt-10 bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-4">Accountinstellingen</h1>

    <form @submit.prevent="handleSubmit" class="space-y-4">
      <div>
        <label>Email</label>
        <input v-model="form.email" type="email" class="input" />
      </div>

      <div>
        <label>Telefoonnummer</label>
        <input v-model="form.phone" type="text" class="input" />
      </div>

      <div>
        <label>Abonnement</label>
        <select v-model="form.subscription_plan" class="input">
          <option>Brons</option>
          <option>Zilver</option>
          <option>Goud</option>
        </select>
      </div>

      <div>
        <label>Nieuw wachtwoord</label>
        <input v-model="form.password" type="password" class="input" />
      </div>

      <div>
        <label>Bevestig wachtwoord</label>
        <input
          v-model="form.password_confirmation"
          type="password"
          class="input"
        />
      </div>

      <button type="submit" class="btn btn-primary w-full">Opslaan</button>
    </form>
  </div>
</template>
