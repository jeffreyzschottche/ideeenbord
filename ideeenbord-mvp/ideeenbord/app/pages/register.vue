<script setup lang="ts">
usePageSeo({
  title: "Registreren",
  description: "Maak een gratis Ideeënbord-account aan en deel je ideeën met merken.",
  noindex: true,
});
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
  <section class="register-section">
    <div class="register-container">
      <div class="register-card">
        <header>
          <h1 class="register-title">Account aanmaken</h1>
          <p class="register-subtitle">
            Leuk dat je meedoet. Vul je gegevens in en start meteen.
          </p>
        </header>

        <form @submit.prevent="handleSubmit">
          <!-- Basisgegevens -->
          <div class="form-row">
            <div class="form-field">
              <label for="name" class="form-label required-dot">Naam</label>
              <input
                id="name"
                v-model="form.name"
                type="text"
                class="form-input"
                placeholder="Bijv. Jane Doe"
                required
              />
            </div>
            <div class="form-field">
              <label for="username" class="form-label required-dot"
                >Gebruikersnaam</label
              >
              <input
                id="username"
                v-model="form.username"
                type="text"
                class="form-input"
                placeholder="Kies een unieke naam"
                required
              />
            </div>
            <div class="form-field full-width">
              <label for="email" class="form-label required-dot">E-mail</label>
              <input
                id="email"
                v-model="form.email"
                type="email"
                class="form-input"
                placeholder="naam@voorbeeld.nl"
                required
              />
            </div>
            <div class="form-field full-width">
              <label for="password" class="form-label required-dot"
                >Wachtwoord</label
              >
              <input
                id="password"
                v-model="form.password"
                type="password"
                class="form-input"
                placeholder="Min. 8 tekens"
                required
              />
              <p class="form-help">
                Gebruik minimaal 8 tekens met letters en cijfers.
              </p>
            </div>
          </div>

          <hr class="form-divider" />

          <!-- Profiel -->
          <div class="form-row">
            <div class="form-field">
              <label for="gender" class="form-label">Geslacht</label>
              <input
                id="gender"
                v-model="form.gender"
                type="text"
                class="form-input"
                placeholder="Bijv. vrouw/man/…"
              />
            </div>
            <div class="form-field">
              <label for="birthdate" class="form-label">Geboortedatum</label>
              <input
                id="birthdate"
                v-model="form.birthdate"
                type="date"
                class="form-input"
              />
            </div>
            <div class="form-field">
              <label for="education_level" class="form-label"
                >Opleidingsniveau</label
              >
              <input
                id="education_level"
                v-model="form.education_level"
                type="text"
                class="form-input"
                placeholder="Bijv. HBO"
              />
            </div>
            <div class="form-field">
              <label for="education" class="form-label">Opleiding</label>
              <input
                id="education"
                v-model="form.education"
                type="text"
                class="form-input"
                placeholder="Bijv. Communicatie"
              />
            </div>
            <div class="form-field">
              <label for="job" class="form-label">Werk</label>
              <input
                id="job"
                v-model="form.job"
                type="text"
                class="form-input"
                placeholder="Functie"
              />
            </div>
            <div class="form-field">
              <label for="sector" class="form-label">Sector</label>
              <input
                id="sector"
                v-model="form.sector"
                type="text"
                class="form-input"
                placeholder="Bijv. IT"
              />
            </div>
            <div class="form-field">
              <label for="city" class="form-label">Woonplaats</label>
              <input
                id="city"
                v-model="form.city"
                type="text"
                class="form-input"
                placeholder="Stad/dorp"
              />
            </div>
            <div class="form-field">
              <label for="birth_city" class="form-label">Geboorteplaats</label>
              <input
                id="birth_city"
                v-model="form.birth_city"
                type="text"
                class="form-input"
                placeholder="Bijv. Haarlem"
              />
            </div>
            <div class="form-field">
              <label for="relationship_status" class="form-label"
                >Relatiestatus</label
              >
              <input
                id="relationship_status"
                v-model="form.relationship_status"
                type="text"
                class="form-input"
                placeholder="Bijv. single"
              />
            </div>
            <div class="form-field">
              <label for="postal_code" class="form-label">Postcode</label>
              <input
                id="postal_code"
                v-model="form.postal_code"
                type="text"
                class="form-input"
                placeholder="1234 AB"
              />
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn-submit">Registreren</button>
            <span class="form-note"
              >Door te registreren ga je akkoord met onze voorwaarden.</span
            >
          </div>
        </form>
      </div>
    </div>
  </section>
</template>
