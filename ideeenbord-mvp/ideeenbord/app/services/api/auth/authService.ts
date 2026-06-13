// This service handles authentication-related API calls,
// including user registration and login operations.

import type {
  ForgotPasswordForm,
  LoginForm,
  RegisterForm,
  ResetPasswordForm,
} from "~/types/auth";
import { apiFetch } from "~/composables/adapter/useApi";

export const authService = {
  // Sends a POST request to register a new user
  async register(form: RegisterForm) {
    return await apiFetch("/register", {
      method: "POST",
      body: form,
    });
  },

  // Sends a POST request to log in an existing user
  async login(form: LoginForm) {
    return await apiFetch("/login", {
      method: "POST",
      body: form,
    });
  },

  async forgotPassword(form: ForgotPasswordForm) {
    return await apiFetch("/forgot-password", {
      method: "POST",
      body: form,
    });
  },

  async resetPassword(form: ResetPasswordForm) {
    return await apiFetch("/reset-password", {
      method: "POST",
      body: form,
    });
  },
};
