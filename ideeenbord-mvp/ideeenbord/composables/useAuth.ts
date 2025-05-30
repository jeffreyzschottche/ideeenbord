import { ref } from "vue";
import { authService } from "~/services/api/authService";
import type { RegisterForm, LoginForm } from "~/types/auth";

import { useUserAuthStore } from "~/store/useUserAuthStore";

export function useRegister() {
  const error = ref<string | null>(null);

  async function register(form: RegisterForm) {
    try {
      const response: any = await authService.register(form);
      const token = response?.access_token;

      if (token) {
        useCookie("token").value = token;
        return true;
      } else {
        error.value = "Geen token ontvangen.";
        return false;
      }
    } catch (err: any) {
      error.value = err?.data?.message || "Registratie mislukt.";
      return false;
    }
  }

  return { register, error };
}
export function useLogin() {
  const error = ref<string | null>(null);
  const authStore = useUserAuthStore();

  async function login(form: LoginForm) {
    try {
      const response: any = await authService.login(form);
      const token = response?.access_token;
      const user = response?.user;

      if (token && user) {
        if (!user.email_verified_at) {
          error.value = "Bevestig eerst je e-mailadres.";
          return false;
        }

        authStore.setAuth(token, user); // 🔐 zet ook cookies via store

        // Optioneel: redirect op basis van rol
        if (user.role === "admin") {
          return navigateTo("/admin/verify");
        }

        return true;
      } else {
        error.value = "Geen token of gebruiker ontvangen.";
        return false;
      }
    } catch (err: any) {
      error.value = err?.data?.message || "Login mislukt.";
      return false;
    }
  }

  return { login, error };
}
