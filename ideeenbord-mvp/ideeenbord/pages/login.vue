<script setup lang="ts">
import { ref } from "vue";
import type { LoginForm } from "~/types/auth";
import { useLogin } from "~/composables/useAuth";
import { useResponseDisplay } from "~/composables/useResponseDisplay"; // ✨ belangrijk!

const form = ref<LoginForm>({
  email: "",
  password: "",
});

const { login, error } = useLogin();
const { trigger } = useResponseDisplay(); // ✨ ophalen van trigger

async function handleSubmit() {
  const success = await login(form.value);

  if (success) {
    trigger("Succesvol ingelogd!", "success");
  } else if (error.value) {
    trigger(error.value, "error");
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
