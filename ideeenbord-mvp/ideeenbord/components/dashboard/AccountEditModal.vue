<script setup lang="ts">
/* 
  This component allows a logged-in brand owner to edit their account details 
  such as email, phone, subscription plan, and password. 
  It initializes with existing user data and submits updates via the brandOwnerService.
*/

import { ref, onMounted, computed } from "vue";
import { useBrandOwnerAuthStore } from "~/store/useBrandOwnerAuthStore";
import { useResponseDisplay } from "~/composables/useResponseDisplay";
import type { BrandOwner, UpdateBrandOwnerForm } from "~/types/brand-owner";
import { brandOwnerService } from "~/services/api/brandOwnerService";

definePageMeta({
  middleware: "brand-owner", // Protects route: only accessible by authenticated brand owners
});

const authStore = useBrandOwnerAuthStore();
const { triggerByKey } = useResponseDisplay();

// Reactive reference to the currently authenticated brand owner
const owner = computed<BrandOwner | null>(() => authStore.owner);

// Form state initialized with empty or default values
const form = ref<UpdateBrandOwnerForm>({
  email: "",
  phone: "",
  subscription_plan: "Brons",
  password: "",
  password_confirmation: "",
});

onMounted(() => {
  // Populate the form with current user data on component mount
  if (owner.value) {
    form.value.email = owner.value.email;
    form.value.phone = owner.value.phone || "";
    form.value.subscription_plan = owner.value.subscription_plan;
  }
});

/*
  Submit updated account information.
  - Sends the updated form to the API.
  - Refreshes the auth state to reflect changes.
  - Triggers UI messages based on success or failure.
*/
async function handleSubmit() {
  try {
    await brandOwnerService.updateAccount(form.value);
    triggerByKey("account-updated"); // Notify user of success
    await authStore.initAuth(); // Refresh auth state to reflect updates
  } catch (err: any) {
    triggerByKey("account-update-failed"); // Notify user of error
  }
}
</script>
<template>
  <div class="max-w-xl mx-auto mt-10 bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-4">Accountinstellingen</h1>

    <form @submit.prevent="handleSubmit" class="space-y-4">
      <!-- Email input -->
      <div>
        <label>Email</label>
        <input v-model="form.email" type="email" class="input" />
      </div>

      <!-- Phone number input -->
      <div>
        <label>Telefoonnummer</label>
        <input v-model="form.phone" type="text" class="input" />
      </div>

      <!-- Subscription plan selector -->
      <div>
        <label>Abonnement</label>
        <select v-model="form.subscription_plan" class="input">
          <option>Brons</option>
          <option>Zilver</option>
          <option>Goud</option>
        </select>
      </div>

      <!-- New password input -->
      <div>
        <label>Nieuw wachtwoord</label>
        <input v-model="form.password" type="password" class="input" />
      </div>

      <!-- Password confirmation input -->
      <div>
        <label>Bevestig wachtwoord</label>
        <input
          v-model="form.password_confirmation"
          type="password"
          class="input"
        />
      </div>

      <!-- Submit button -->
      <button type="submit" class="btn btn-primary w-full">Opslaan</button>
    </form>
  </div>
</template>
