<script setup lang="ts">
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
const { trigger, triggerByKey } = useResponseDisplay();

/* ---------- helper: haal 1e foutboodschap uit Laravel-payload ---------- */
function firstLaravelMessage(raw: unknown): string | null {
  if (!raw) return null;

  // plain string
  if (typeof raw === "string") return raw;

  // { message, errors: { field: [msg,…] } }
  const obj = raw as { message?: string; errors?: Record<string, string[]> };

  if (obj.errors && Object.keys(obj.errors).length) {
    const firstField = Object.keys(obj.errors)[0];
    const firstMsg = obj.errors[firstField]?.[0];
    if (firstMsg) return firstMsg;
  }

  return obj.message ?? null;
}

async function handleSubmit() {
  const ok = await register(form.value);

  if (ok) {
    triggerByKey("register-success");
    return;
  }

  const msg = firstLaravelMessage(error.value);

  if (msg === "profanity-detected") {
    // ProfanityFree-rule faalde
    triggerByKey(msg);
  } else if (msg) {
    // andere validatiefout → toon bericht letterlijk
    trigger(msg, "error");
  } else {
    // geen details → generieke melding
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
