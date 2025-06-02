import type { RegisterForm, LoginForm } from "~/types/auth";
import { apiFetch } from "~/composables/useApi";

export const authService = {
  async register(form: RegisterForm) {
    return await apiFetch("/register", {
      method: "POST",
      body: form,
    });
  },
  async login(form: LoginForm) {
    return await apiFetch("/login", {
      method: "POST",
      body: form,
    });
  },
};
