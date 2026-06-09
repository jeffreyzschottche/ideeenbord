import { ref } from "vue";
import { authService } from "~/services/api/auth/authService";
import type { RegisterForm, LoginForm } from "~/types/auth";
import { useUserAuthStore } from "~/store/useUserAuthStore";

/**
 * Composable for handling user registration.
 * Stores JWT token in cookies on success.
 */
export function useRegister() {
  const error = ref<string | null>(null);

  async function register(form: RegisterForm) {
    try {
      const response: any = await authService.register(form);
      const token = response?.access_token;

      if (token) {
        // Save token in cookie for persistent authentication
        useCookie("token").value = token;
        return true;
      } else {
        error.value = "No token received.";
        return false;
      }
    } catch (err: any) {
      // Set error message from API or fallback
      error.value = err?.data?.message || "Registration failed.";
      return false;
    }
  }

  return { register, error };
}

/**
 * Composable for handling user login.
 * Sets token and user data in store and cookie on success.
 */
export function useLogin() {
  const error = ref<string | null>(null);
  const authStore = useUserAuthStore();

  async function login(form: LoginForm) {
    try {
      const response: any = await authService.login(form);
      const token = response?.access_token;
      const user = response?.user;

      if (token && user) {
        // Require verified email before logging in
        if (!user.email_verified_at) {
          error.value = "Please verify your email address first.";
          return false;
        }
        // Optional: redirect based on user role
        if (user.role === "admin") {
          navigateTo("/admin/verify");
          // return true;
        }
        // Set token and user in global auth store (also sets cookie)
        authStore.setAuth(token, user);
        navigateTo(`/user/${user.username}`);
        // return true;
      } else {
        error.value = "No token or user received.";
        return false;
      }
    } catch (err: any) {
      // Set error message from API or fallback
      error.value = err?.data?.message || "Login failed.";
      return false;
    }
  }

  return { login, error };
}
