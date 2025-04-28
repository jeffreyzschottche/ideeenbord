import { useBrandOwnerAuthStore } from "~/store/brandOwnerAuth";

export default defineNuxtRouteMiddleware(async (to) => {
  const auth = useBrandOwnerAuthStore();

  if (process.client) {
    await auth.initAuth();

    if (!auth.token || !auth.owner) {
      console.log(
        "❌ Niet ingelogd of geen owner, redirect naar dashboard login"
      );
      return navigateTo("/dashboard/");
    }

    const routeSlug = to.params.slug;
    const ownerSlug = auth.owner?.brand?.slug;

    if (routeSlug !== ownerSlug) {
      console.log(`❌ Slug mismatch: ${routeSlug} vs ${ownerSlug}`);
      return navigateTo(`/dashboard/${ownerSlug}`);
    }

    console.log("✅ Alles klopt, toegang toegestaan!");
  }
});
