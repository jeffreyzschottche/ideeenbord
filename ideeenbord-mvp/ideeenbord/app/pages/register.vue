<script setup lang="ts">
usePageSeo({
  title: "Registreren",
  description: "Maak een gratis Ideeënbord-account aan en deel je ideeën met merken.",
  noindex: true,
});
import { ref, computed } from "vue";
import type { RegisterForm } from "~/types/auth";
import { useRegister } from "~/composables/user/useAuth";
import { useResponseDisplay } from "~/composables/notifications/useResponseDisplay";
import CityAutocomplete from "~/components/form/CityAutocomplete.vue";
import {
  genderOptions,
  educationLevelOptions,
  relationshipOptions,
  sectorOptions,
} from "~/constants/profileOptions";

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

/* ---------- Stappen ---------- */
const step = ref(1);
const submitting = ref(false);

const step1Valid = computed(
  () =>
    form.value.name.trim() &&
    form.value.username.trim() &&
    /\S+@\S+\.\S+/.test(form.value.email) &&
    form.value.password.length >= 8
);

function goNext() {
  if (!step1Valid.value) {
    trigger("Vul eerst je naam, gebruikersnaam, e-mail en wachtwoord (min. 8 tekens) in.", "warning");
    return;
  }
  step.value = 2;
}

function firstLaravelMessage(raw: unknown): string | null {
  if (!raw) return null;
  if (typeof raw === "string") return raw;
  const obj = raw as { message?: string; errors?: Record<string, string[]> };
  if (obj.errors && Object.keys(obj.errors).length) {
    const firstField = Object.keys(obj.errors)[0];
    const firstMsg = obj.errors[firstField]?.[0];
    if (firstMsg) return firstMsg;
  }
  return obj.message ?? null;
}

async function handleSubmit() {
  submitting.value = true;
  const ok = await register(form.value);
  submitting.value = false;

  if (ok) {
    triggerByKey("register-success");
    return;
  }

  const msg = firstLaravelMessage(error.value);
  if (msg === "profanity-detected") triggerByKey(msg);
  else if (msg) trigger(msg, "error");
  else triggerByKey("register-failed");
}

</script>

<template>
  <section class="min-h-[80vh] flex items-center justify-center py-12 px-4 bg-[var(--color-bg)]">
    <div class="w-full max-w-xl">
      <!-- Logo + intro -->
      <div class="text-center mb-6">
        <h1 class="text-2xl md:text-3xl font-extrabold text-[var(--color-nav)]">
          Maak een gratis account
        </h1>
        <p class="text-gray-500 mt-1">Deel je ideeën met merken in een paar stappen.</p>
      </div>

      <!-- Stappen-indicator -->
      <div class="flex items-center justify-center gap-3 mb-6">
        <div class="flex items-center gap-2">
          <span
            class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold"
            :class="step >= 1 ? 'bg-[var(--color-brand)] text-white' : 'bg-gray-200 text-gray-500'"
          >1</span>
          <span class="text-sm font-semibold" :class="step >= 1 ? 'text-[var(--color-nav)]' : 'text-gray-400'">Account</span>
        </div>
        <div class="w-10 h-0.5" :class="step >= 2 ? 'bg-[var(--color-brand)]' : 'bg-gray-200'"></div>
        <div class="flex items-center gap-2">
          <span
            class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold"
            :class="step >= 2 ? 'bg-[var(--color-brand)] text-white' : 'bg-gray-200 text-gray-500'"
          >2</span>
          <span class="text-sm font-semibold" :class="step >= 2 ? 'text-[var(--color-nav)]' : 'text-gray-400'">Over jou</span>
        </div>
      </div>

      <div class="card p-6 md:p-8">
        <!-- STAP 1 -->
        <form v-if="step === 1" @submit.prevent="goNext" class="space-y-4">
          <div>
            <label class="form-label required-dot">Naam</label>
            <input v-model="form.name" type="text" class="input" placeholder="Jane Doe" required />
          </div>
          <div>
            <label class="form-label required-dot">Gebruikersnaam</label>
            <input v-model="form.username" type="text" class="input" placeholder="Kies een unieke naam" required />
          </div>
          <div>
            <label class="form-label required-dot">E-mail</label>
            <input v-model="form.email" type="email" class="input" placeholder="naam@voorbeeld.nl" required />
          </div>
          <div>
            <label class="form-label required-dot">Wachtwoord</label>
            <input v-model="form.password" type="password" class="input" placeholder="Min. 8 tekens" required />
            <p class="form-help">Gebruik minimaal 8 tekens.</p>
          </div>

          <button type="submit" class="cta w-full py-3 mt-2">Volgende →</button>
          <p class="text-center text-sm text-gray-500">
            Al een account? <NuxtLink to="/login" class="font-semibold text-[var(--color-brand)] hover:underline">Inloggen</NuxtLink>
          </p>
        </form>

        <!-- STAP 2 -->
        <form v-else @submit.prevent="handleSubmit" class="space-y-5">
          <p class="text-sm text-gray-500">
            Deze gegevens zijn <strong>optioneel</strong> en helpen merken hun doelgroep beter te begrijpen. Je kunt ze overslaan.
          </p>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="form-label">Geslacht</label>
              <select v-model="form.gender" class="select-input">
                <option value="">Kies…</option>
                <option v-for="o in genderOptions" :key="o" :value="o">{{ o }}</option>
              </select>
            </div>
            <div>
              <label class="form-label">Geboortedatum</label>
              <input v-model="form.birthdate" type="date" class="input" />
            </div>
            <div>
              <label class="form-label">Woonplaats</label>
              <CityAutocomplete v-model="form.city" placeholder="Bijv. Haarlem" />
            </div>
            <div>
              <label class="form-label">Postcode</label>
              <input v-model="form.postal_code" type="text" class="input" placeholder="1234 AB" />
            </div>
            <div>
              <label class="form-label">Opleidingsniveau</label>
              <select v-model="form.education_level" class="select-input">
                <option value="">Kies…</option>
                <option v-for="o in educationLevelOptions" :key="o" :value="o">{{ o }}</option>
              </select>
            </div>
            <div>
              <label class="form-label">Opleiding</label>
              <input v-model="form.education" type="text" class="input" placeholder="Bijv. Communicatie" />
            </div>
            <div>
              <label class="form-label">Functie</label>
              <input v-model="form.job" type="text" class="input" placeholder="Bijv. marketeer" />
            </div>
            <div>
              <label class="form-label">Sector</label>
              <select v-model="form.sector" class="select-input">
                <option value="">Kies…</option>
                <option v-for="o in sectorOptions" :key="o" :value="o">{{ o }}</option>
              </select>
            </div>
            <div>
              <label class="form-label">Relatiestatus</label>
              <select v-model="form.relationship_status" class="select-input">
                <option value="">Kies…</option>
                <option v-for="o in relationshipOptions" :key="o" :value="o">{{ o }}</option>
              </select>
            </div>
            <div>
              <label class="form-label">Geboorteplaats</label>
              <CityAutocomplete v-model="form.birth_city" placeholder="Bijv. Haarlem" />
            </div>
          </div>

          <div class="flex flex-col sm:flex-row gap-3 pt-2">
            <button type="button" class="btn--ghost btn px-5 py-3" @click="step = 1">← Terug</button>
            <button type="submit" :disabled="submitting" class="cta flex-1 py-3 disabled:opacity-60">
              <span v-if="submitting"><i class="fa-solid fa-spinner fa-spin mr-1"></i> Bezig…</span>
              <span v-else>Account aanmaken</span>
            </button>
          </div>
          <button type="submit" :disabled="submitting" class="w-full text-sm text-gray-500 hover:text-[var(--color-brand)]">
            Profiel overslaan en account aanmaken
          </button>
        </form>
      </div>

      <p class="text-center text-xs text-gray-400 mt-4">
        Door te registreren ga je akkoord met onze voorwaarden.
      </p>
    </div>
  </section>
</template>
