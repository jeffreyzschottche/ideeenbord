<script setup lang="ts">
import { computed, ref } from "vue";
import { authService } from "~/services/api/auth/authService";

usePageSeo({
  title: "Nieuw wachtwoord",
  description: "Stel een nieuw wachtwoord in voor je Ideeënbord-account.",
  noindex: true,
});

const route = useRoute();
const loading = ref(false);
const message = ref("");
const error = ref("");
const password = ref("");
const passwordConfirmation = ref("");

const email = computed(() => String(route.query.email || ""));
const token = computed(() => String(route.query.token || ""));
const canSubmit = computed(
  () => email.value && token.value && password.value && passwordConfirmation.value
);

async function handleSubmit() {
  loading.value = true;
  message.value = "";
  error.value = "";

  try {
    const response = await authService.resetPassword({
      email: email.value,
      token: token.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value,
    });
    message.value =
      (response as { message?: string })?.message ||
      "Je wachtwoord is aangepast. Je kunt nu inloggen.";
    password.value = "";
    passwordConfirmation.value = "";
  } catch (err: any) {
    error.value =
      err?.data?.message ||
      "Deze resetlink is ongeldig of verlopen. Vraag een nieuwe link aan.";
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <section class="register-section">
    <div class="register-container">
      <div class="register-card">
        <header>
          <h1 class="register-title">Nieuw wachtwoord</h1>
          <p class="register-subtitle">
            Kies een nieuw wachtwoord voor {{ email || "je account" }}.
          </p>
        </header>

        <p v-if="!email || !token" class="form-note text-red-600">
          Deze resetlink mist gegevens. Vraag een nieuwe resetlink aan.
        </p>

        <form v-else @submit.prevent="handleSubmit">
          <div class="form-row">
            <div class="form-field full-width">
              <label for="password" class="form-label required-dot">
                Nieuw wachtwoord
              </label>
              <input
                id="password"
                v-model="password"
                type="password"
                class="form-input"
                placeholder="Min. 6 tekens"
                minlength="6"
                required
              />
            </div>

            <div class="form-field full-width">
              <label for="password_confirmation" class="form-label required-dot">
                Herhaal wachtwoord
              </label>
              <input
                id="password_confirmation"
                v-model="passwordConfirmation"
                type="password"
                class="form-input"
                placeholder="Herhaal je wachtwoord"
                minlength="6"
                required
              />
            </div>
          </div>

          <p v-if="message" class="form-note">{{ message }}</p>
          <p v-if="error" class="form-note text-red-600">{{ error }}</p>

          <div class="form-actions">
            <button
              type="submit"
              class="btn-submit"
              :disabled="loading || !canSubmit"
            >
              {{ loading ? "Opslaan..." : "Wachtwoord opslaan" }}
            </button>
            <span class="form-note">
              Nieuwe link nodig?
              <NuxtLink to="/forgot-password">Vraag opnieuw aan</NuxtLink>.
            </span>
          </div>
        </form>
      </div>
    </div>
  </section>
</template>
