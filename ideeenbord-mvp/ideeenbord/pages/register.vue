<script setup lang="ts">
/*
  This page handles user registration using a comprehensive form.
  It uses `useRegister()` composable to send form data to the backend.
  On success, a 'register-success' response is triggered; otherwise, 'register-failed'.
*/

import { ref } from "vue";
import type { RegisterForm } from "~/types/auth";
import { useRegister } from "~/composables/user/useAuth";
import { useResponseDisplay } from "~/composables/notifications/useResponseDisplay";

const form = ref<RegisterForm>({
  name: "",
  email: "",
  username: "",
  password: "",
  gender: "",
  birthdate: "",
  education_level: "",
  education: "",
  job: "",
  sector: "",
  city: "",
  birth_city: "",
  relationship_status: "",
  postal_code: "",
});

const { register, error } = useRegister();
const { triggerByKey } = useResponseDisplay();

async function handleSubmit() {
  const success = await register(form.value);

  if (success) {
    triggerByKey("register-success");
  } else {
    triggerByKey("register-failed");
  }
}
</script>

<template>
  <form @submit.prevent="handleSubmit">
    <input v-model="form.name" placeholder="Naam" required />
    <input v-model="form.email" type="email" placeholder="Email" required />
    <input v-model="form.username" placeholder="Gebruikersnaam" required />
    <input
      v-model="form.password"
      type="password"
      placeholder="Wachtwoord"
      required
    />
    <input v-model="form.gender" placeholder="Geslacht" />
    <input v-model="form.birthdate" type="date" placeholder="Geboortedatum" />
    <input v-model="form.education_level" placeholder="Opleidingsniveau" />
    <input v-model="form.education" placeholder="Opleiding" />
    <input v-model="form.job" placeholder="Werk" />
    <input v-model="form.sector" placeholder="Sector" />
    <input v-model="form.city" placeholder="Woonplaats" />
    <input v-model="form.birth_city" placeholder="Geboorteplaats" />
    <input v-model="form.relationship_status" placeholder="Relatiestatus" />
    <input v-model="form.postal_code" placeholder="Postcode" />

    <button type="submit">Registreren</button>
  </form>
</template>
