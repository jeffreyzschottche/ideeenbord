<script setup lang="ts">
/*
  This page provides a login form for regular users.
  It uses `useLogin()` composable to authenticate and `triggerByKey()` to display feedback.
  On success, a login-approved message is triggered. Otherwise, login-failed.
*/

import { ref } from "vue";
import type { LoginForm } from "~/types/auth";
import { useLogin } from "~/composables/user/useAuth";
import { useResponseDisplay } from "~/composables/notifications/useResponseDisplay";

const form = ref<LoginForm>({
  email: "",
  password: "",
});

const { login, error } = useLogin();
const { triggerByKey } = useResponseDisplay();

async function handleSubmit() {
  const success = await login(form.value);
  console.log(success);

  if (success || success === undefined) {
    triggerByKey("login-approved");
  } else {
    triggerByKey("login-failed");
  }
}
</script>

<template>
  <section class="register-section">
    <div class="register-container">
      <div class="register-card">
        <header>
          <h1 class="register-title">Inloggen</h1>
          <p class="register-subtitle">
            Welkom terug! Vul je gegevens in om verder te gaan.
          </p>
        </header>

        <form @submit.prevent="handleSubmit">
          <div class="form-row">
            <div class="form-field full-width">
              <label for="email" class="form-label required-dot">E‑mail</label>
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
                placeholder="••••••••"
                required
              />
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn-submit">Inloggen</button>
            <span class="form-note">
              Wachtwoord vergeten? <a href="/forgot-password">Herstel hier</a>.
            </span>
          </div>
        </form>
      </div>
    </div>
  </section>
</template>
