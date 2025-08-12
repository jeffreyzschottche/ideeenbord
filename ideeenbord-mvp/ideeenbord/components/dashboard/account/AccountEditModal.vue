<script setup lang="ts">
/* 
  This component allows a logged-in brand owner to edit their account details 
  such as email, phone, subscription plan, and password. 
  It initializes with existing user data and submits updates via the brandOwnerService.
*/

import { ref, onMounted, computed } from "vue";
import { useBrandOwnerAuthStore } from "~/store/useBrandOwnerAuthStore";
import { useResponseDisplay } from "~/composables/notifications/useResponseDisplay";
import type { BrandOwner, UpdateBrandOwnerForm } from "~/types/brand-owner";
import { brandOwnerService } from "~/services/api/brand/brandOwnerService";

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
  <div class="page-block" style="max-width: 40rem">
    <h1 class="title-lg">Accountinstellingen</h1>

    <form @submit.prevent="handleSubmit">
      <div style="display: flex; flex-direction: column; gap: 1rem">
        <div>
          <label class="form-label">Email</label>
          <input v-model="form.email" type="email" class="input" />
        </div>

        <div>
          <label class="form-label">Telefoonnummer</label>
          <input v-model="form.phone" type="text" class="input" />
        </div>

        <div>
          <label class="form-label">Abonnement</label>
          <select v-model="form.subscription_plan" class="select-input">
            <option>Brons</option>
            <option>Zilver</option>
            <option>Goud</option>
          </select>
        </div>

        <div>
          <label class="form-label">Nieuw wachtwoord</label>
          <input v-model="form.password" type="password" class="input" />
        </div>

        <div>
          <label class="form-label">Bevestig wachtwoord</label>
          <input
            v-model="form.password_confirmation"
            type="password"
            class="input"
          />
        </div>

        <button type="submit" class="btn btn--block">Opslaan</button>
      </div>
    </form>
  </div>
</template>
