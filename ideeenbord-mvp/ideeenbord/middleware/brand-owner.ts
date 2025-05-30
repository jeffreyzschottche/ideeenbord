// ~/middleware/brand-owner.ts
import { useCookie } from "#app";
import { useBrandOwnerAuthStore } from "~/store/useBrandOwnerAuthStore";

export default defineNuxtRouteMiddleware((to) => {
  const token = useCookie<string | null>("bo_token");
  const owner = useCookie<any | null>("bo_owner");

  if (!token.value || !owner.value) {
    console.log("❌ Geen cookie-token of owner, redirect");
    return navigateTo("/dashboard/");
  }

  // Vul Pinia-store client-side
  if (process.client) {
    const store = useBrandOwnerAuthStore();
    if (!store.token) store.token = token.value;
    if (!store.owner) store.owner = owner.value;
  }

  const routeSlug = to.params.slug;
  const ownerSlug = owner.value?.brand?.slug;

  if (routeSlug !== ownerSlug) {
    console.log(`❌ Slug mismatch: ${routeSlug} vs ${ownerSlug}`);
    return navigateTo(`/dashboard/${ownerSlug}`);
  }

  console.log("✅ Brand-owner toegang toegestaan");
});
