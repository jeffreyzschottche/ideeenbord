import { useUserAuthStore } from "~/store/useUserAuthStore";
import { useCookie } from "#app";

export default defineNuxtRouteMiddleware(() => {
  const token = useCookie<string | null>("token");
  const user = useCookie<any | null>("user");

  // Geen token of user → redirect
  if (!token.value || !user.value) {
    return navigateTo("/login");
  }

  // Pinia vullen (client only)
  if (process.client) {
    const store = useUserAuthStore();
    if (!store.token) store.token = token.value;
    if (!store.user) store.user = user.value;
  }
});
