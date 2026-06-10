<script setup lang="ts">
/*
  Handles user profile editing.
  Initializes a reactive form with current user data.
  Submits updates to the API, excluding empty or unchanged fields.
  Also handles optional password change.
*/

import { ref, watch } from "vue";
import { useUserAuthStore } from "~/store/useUserAuthStore";
import { apiFetch } from "~/composables/adapter/useApi";
import { useResponseDisplay } from "~/composables/notifications/useResponseDisplay";

const auth = useUserAuthStore();
const { triggerByKey } = useResponseDisplay();

// Form state and UI flags
const form = ref<Record<string, any>>({});
const saving = ref(false);
const showPassword = ref(false);

/*
  Watch the authenticated user and populate the form fields accordingly.
  Empty strings are used as fallbacks to avoid uncontrolled inputs.
*/
watch(
  () => auth.user,
  (newUser) => {
    if (newUser) {
      form.value = {
        name: newUser.name ?? "",
        email: newUser.email ?? "",
        username: newUser.username ?? "",
        gender: newUser.gender ?? "",
        education_level: newUser.education_level ?? "",
        education: newUser.education ?? "",
        job: newUser.job ?? "",
        sector: newUser.sector ?? "",
        city: newUser.city ?? "",
        relationship_status: newUser.relationship_status ?? "",
        postal_code: newUser.postal_code ?? "",
        password: "",
      };
    }
  },
  { immediate: true }
);

/*
  Submit updated profile data.
  Empty password fields are ignored.
  Fields with null/undefined are excluded from the payload.
*/
async function updateProfile() {
  saving.value = true;

  try {
    const filteredBody = Object.fromEntries(
      Object.entries(form.value).filter(([key, value]) => {
        if (key === "password" && value === "") return false;
        return value !== null && value !== undefined;
      })
    );

    const updated: any = await apiFetch(`/users/${auth.user.username}`, {
      method: "PATCH",
      body: filteredBody,
    });

    auth.user = updated.user; // Update local auth state with new user data
    triggerByKey("profile-updated"); // Trigger success message
  } catch (e: any) {
    // 1. Zoek de Laravel-validation errors op ALLE bekende plekken
    const rawErrors =
      e?.errors ?? //  ← FetchError (jouw geval)
      e?.validationErrors ?? //  ← andere wrapper
      e?.data?.errors ?? //  ← oudere Nuxt-versies
      e?.response?._data?.errors ??
      null;

    // 2. Staat 'profanity-detected' erin?
    if (rawErrors) {
      const all = Object.values(rawErrors).flat();
      if (all.includes("profanity-detected")) {
        triggerByKey("profanity-detected"); // ✅ juiste toast
        return; //  ← blokkéér fallback
      }
    }

    // 3. Generieke fout
    triggerByKey("profile-update-failed");
  } finally {
    saving.value = false;
  }
}
</script>

<template>
  <form @submit.prevent="updateProfile" class="space-y-4 mt-8">
    <h2 class="text-xl font-bold">🛠️ Profiel bijwerken</h2>

    <div
      v-for="(field, label) in {
        name: 'Naam',
        email: 'E-mail',
        username: 'Gebruikersnaam',
        gender: 'Gender',
        education_level: 'Opleidingsniveau',
        education: 'Studie',
        job: 'Beroep',
        sector: 'Sector',
        city: 'Woonplaats',
        relationship_status: 'Relatiestatus',
        postal_code: 'Postcode',
      }"
      :key="label"
    >
      <label :for="label" class="block font-medium mb-1">{{ field }}</label>
      <input v-model="form[label]" :id="label" type="text" class="input" />
    </div>

    <div>
      <label for="password" class="block font-medium mb-1">
        Nieuw wachtwoord (optioneel)
      </label>
      <small class="text-gray-500"
        >Laat leeg als je je wachtwoord niet wil wijzigen</small
      >
      <div class="relative">
        <input
          v-model="form.password"
          :type="showPassword ? 'text' : 'password'"
          id="password"
          class="input pr-10"
        />
      </div>
    </div>

    <button
      type="submit"
      class="cta px-6 py-3 disabled:opacity-60"
      :disabled="saving"
    >
      <span v-if="saving"><i class="fa-solid fa-spinner fa-spin mr-1"></i> Opslaan…</span>
      <span v-else>Opslaan</span>
    </button>
  </form>
</template>

<style scoped>
.input {
  display: block;
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #ddd;
  border-radius: 0.25rem;
}
</style>
