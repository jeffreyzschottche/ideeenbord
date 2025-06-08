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
  <form @submit.prevent="handleSubmit">
    <input v-model="form.email" type="email" placeholder="Email" required />
    <input
      v-model="form.password"
      type="password"
      placeholder="Wachtwoord"
      required
    />
    <button type="submit">Login</button>
  </form>
</template>
