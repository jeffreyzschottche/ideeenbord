<script setup lang="ts">
import { ref } from "vue";
import { authService } from "~/services/api/auth/authService";

usePageSeo({
  title: "Wachtwoord vergeten",
  description: "Vraag een link aan om je Ideeënbord-wachtwoord opnieuw in te stellen.",
  noindex: true,
});

const email = ref("");
const loading = ref(false);
const message = ref("");
const error = ref("");

async function handleSubmit() {
  loading.value = true;
  message.value = "";
  error.value = "";

  try {
    const response = await authService.forgotPassword({ email: email.value });
    message.value =
      (response as { message?: string })?.message ||
      "Als dit e-mailadres bestaat, sturen we een resetlink.";
  } catch (err: any) {
    error.value =
      err?.data?.message ||
      "We konden geen resetlink versturen. Probeer het later opnieuw.";
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
          <h1 class="register-title">Wachtwoord vergeten</h1>
          <p class="register-subtitle">
            Vul je e-mailadres in. Je ontvangt een link om een nieuw wachtwoord
            te kiezen.
          </p>
        </header>

        <form @submit.prevent="handleSubmit">
          <div class="form-row">
            <div class="form-field full-width">
              <label for="email" class="form-label required-dot">E-mail</label>
              <input
                id="email"
                v-model="email"
                type="email"
                class="form-input"
                placeholder="naam@voorbeeld.nl"
                required
              />
            </div>
          </div>

          <p v-if="message" class="form-note">{{ message }}</p>
          <p v-if="error" class="form-note text-red-600">{{ error }}</p>

          <div class="form-actions">
            <button type="submit" class="btn-submit" :disabled="loading">
              {{ loading ? "Versturen..." : "Resetlink versturen" }}
            </button>
            <span class="form-note">
              Toch inloggen? <NuxtLink to="/login">Ga terug</NuxtLink>.
            </span>
          </div>
        </form>
      </div>
    </div>
  </section>
</template>
