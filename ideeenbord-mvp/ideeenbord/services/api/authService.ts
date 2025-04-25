import type { RegisterForm, LoginForm } from "~/types/auth";

const BASE = "http://localhost:8000/api";

export const authService = {
  async register(form: RegisterForm) {
    return await $fetch(`${BASE}/register`, {
      method: "POST",
      body: form,
    });
  },
  async login(form: LoginForm) {
    return await $fetch(`${BASE}/login`, {
      method: "POST",
      body: form,
    });
  },
};
