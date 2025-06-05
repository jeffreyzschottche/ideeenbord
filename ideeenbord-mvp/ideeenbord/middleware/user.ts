import { useUserAuthStore } from "~/store/useUserAuthStore";
import { useCookie } from "#app";

export default defineNuxtRouteMiddleware((to) => {
  const token = useCookie<string | null>("token");
  const user = useCookie<any | null>("user");

  // No token or user → redirect to login
  if (!token.value || !user.value) {
    return navigateTo("/login");
  }

  const routeUsername = to.params.slug;
  const loggedInUsername = user.value?.username;

  if (routeUsername && loggedInUsername !== routeUsername) {
    return navigateTo("/login");
  }
  // Populate Pinia store (client only)
  if (import.meta.client) {
    const store = useUserAuthStore();
    if (!store.token) store.token = token.value;
    if (!store.user) store.user = user.value;
  }
});
