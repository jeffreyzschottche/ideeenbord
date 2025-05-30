<script setup lang="ts">
import { ref } from "vue";
import { useBrandOwnerAuthStore } from "~/store/useBrandOwnerAuthStore";
import type { LoginForm } from "~/types/auth";

const brandOwnerAuth = useBrandOwnerAuthStore();
const error = ref<string | null>(null);

const form = ref<LoginForm>({ email: "", password: "" });

async function handleSubmit() {
  try {
    const res = await apiFetch<{ token: string; owner: any }>(
      "/brand-owner/login",
      {
        method: "POST",
        body: form.value,
      }
    );

    brandOwnerAuth.setAuth(res.token, res.owner);
    navigateTo("/dashboard/" + res.owner.brand.slug);
  } catch (err: any) {
    console.error(err);
    error.value = err?.response?._data?.message || "Inloggen mislukt";
  }
}
</script>

<template>
  <div class="max-w-md mx-auto mt-10 p-6 bg-white rounded shadow">
    <h1 class="text-2xl font-bold mb-6 text-center">Brand Owner Login</h1>

    <form @submit.prevent="handleSubmit">
      <div class="mb-4">
        <input
          v-model="form.email"
          type="email"
          placeholder="Email"
          required
          class="w-full px-4 py-2 border rounded"
        />
      </div>

      <div class="mb-6">
        <input
          v-model="form.password"
          type="password"
          placeholder="Wachtwoord"
          required
          class="w-full px-4 py-2 border rounded"
        />
      </div>

      <button
        type="submit"
        class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600 transition"
      >
        Login
      </button>
    </form>

    <p v-if="error" class="text-red-500 mt-4 text-center">{{ error }}</p>
  </div>
</template>
