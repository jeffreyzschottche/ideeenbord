// ~/middleware/admin.ts
import { useCookie } from "#app";
import { useUserAuthStore } from "~/store/useUserAuthStore";

export default defineNuxtRouteMiddleware(async () => {
  const token = useCookie<string | null>("token");
  const user = useCookie<any | null>("user"); // eventueel typeren

  // Als we geen token of geen user hebben → redirect
  if (!token.value || !user.value || user.value.role !== "admin") {
    return navigateTo("/login");
  }

  // Bij client-render: store vullen voor latere toegang
  if (process.client) {
    const auth = useUserAuthStore();
    if (!auth.token) {
      auth.token = token.value;
    }
    if (!auth.user) {
      auth.user = user.value;
    }
  }
});
