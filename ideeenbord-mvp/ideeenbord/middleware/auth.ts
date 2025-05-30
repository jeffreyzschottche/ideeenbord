import { useUserAuthStore } from "~/store/useUserAuthStore";

export default defineNuxtRouteMiddleware((to, from) => {
  const auth = useUserAuthStore();

  if (auth.$state.token) {
    return navigateTo("/login");
  }
});
