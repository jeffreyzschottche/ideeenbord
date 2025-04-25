<script setup lang="ts">
import { ref } from "vue";
import type { LoginForm } from "~/types/auth";
import { useLogin } from "~/composables/useAuth";

const form = ref<LoginForm>({
  email: "",
  password: "",
});

const { login, error } = useLogin();

async function handleSubmit() {
  const success = await login(form.value);
  if (!success && error.value) {
    alert(error.value);
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
