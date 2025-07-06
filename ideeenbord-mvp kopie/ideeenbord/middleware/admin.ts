import { useCookie } from "#app";
import { useUserAuthStore } from "~/store/useUserAuthStore";

export default defineNuxtRouteMiddleware(async () => {
  const token = useCookie<string | null>("token");
  const user = useCookie<any | null>("user"); // optionally type this more strictly

  // If there's no token or no user, or user is not an admin → redirect to login
  if (!token.value || !user.value || user.value.role !== "admin") {
    return navigateTo("/login");
  }

  // On client-side rendering: populate store for future access
  if (import.meta.client) {
    const auth = useUserAuthStore();
    if (!auth.token) {
      auth.token = token.value;
    }
    if (!auth.user) {
      auth.user = user.value;
    }
  }
});
