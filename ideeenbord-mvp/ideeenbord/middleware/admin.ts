import { useAuthStore } from "~/store/auth";

export default defineNuxtRouteMiddleware(async (to, from) => {
  const auth = useAuthStore();

  if (!auth.user && auth.token) {
    // Als user nog niet geladen is, eerst initAuth forceren
    await auth.initAuth();
  }

  if (auth.$state.user?.role !== "admin") {
    navigateTo("/login");
  }
});
