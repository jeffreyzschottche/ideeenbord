<script setup lang="ts">
import { ref, watch } from "vue";
import { useAuthStore } from "~/store/auth";
import { apiFetch } from "~/composables/useApi";
import { useResponseDisplay } from "~/composables/useResponseDisplay";

const auth = useAuthStore();
const { trigger } = useResponseDisplay();

const form = ref<Record<string, any>>({});
const saving = ref(false);
const showPassword = ref(false);

// Vul form zodra auth.user beschikbaar is
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

async function updateProfile() {
  saving.value = true;

  try {
    // Alleen velden meesturen die ingevuld zijn of niet leeg zijn
    const filteredBody = Object.fromEntries(
      Object.entries(form.value).filter(([key, value]) => {
        // Laat lege wachtwoordvelden weg
        if (key === "password" && value === "") return false;
        return value !== null && value !== undefined;
      })
    );

    const updated: any = await apiFetch(`/users/${auth.user.username}`, {
      method: "PATCH",
      body: filteredBody,
    });

    auth.user = updated.user;
    trigger("Profiel bijgewerkt", "success");
  } catch (e) {
    trigger("Bijwerken mislukt", "error");
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
      class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
      :disabled="saving"
    >
      {{ saving ? "Opslaan..." : "Opslaan" }}
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
