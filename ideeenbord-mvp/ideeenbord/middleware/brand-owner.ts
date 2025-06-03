import { useCookie } from "#app";
import { useBrandOwnerAuthStore } from "~/store/useBrandOwnerAuthStore";

export default defineNuxtRouteMiddleware((to) => {
  const token = useCookie<string | null>("bo_token");
  const owner = useCookie<any | null>("bo_owner");

  // Redirect if token or owner is missing
  if (!token.value || !owner.value) {
    return navigateTo("/dashboard/");
  }

  // Populate Pinia store on the client side
  if (import.meta.client) {
    const store = useBrandOwnerAuthStore();
    if (!store.token) store.token = token.value;
    if (!store.owner) store.owner = owner.value;
  }

  const routeSlug = to.params.slug;
  const ownerSlug = owner.value?.brand?.slug;

  // Redirect if the route slug does not match the owner's brand slug
  if (routeSlug !== ownerSlug) {
    return navigateTo(`/dashboard/${ownerSlug}`);
  }
});
