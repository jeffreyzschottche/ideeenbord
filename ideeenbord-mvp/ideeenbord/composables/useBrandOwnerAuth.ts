import { ref } from "vue";
import { useRouter } from "vue-router";
import { apiFetch } from "~/composables/useApi";
import { useResponseDisplay } from "~/composables/useResponseDisplay";
import { useBrandOwnerAuthStore } from "~/store/useBrandOwnerAuthStore";
import type { BrandOwner } from "~/types/brand-owner";
import type { BrandOwnerLoginResponse } from "~/types/brand-owner";

export function useBrandOwnerAuth() {
  const router = useRouter();
  const { trigger } = useResponseDisplay();
  const brandOwnerAuth = useBrandOwnerAuthStore(); // pinia store

  async function login(credentials: { email: string; password: string }) {
    try {
      const res = await apiFetch<BrandOwnerLoginResponse>(
        "/brand-owner/login",
        {
          method: "POST",
          body: credentials,
        }
      );

      // 🔥🔥 Store goed vullen
      brandOwnerAuth.setAuth(res.token, res.owner);

      trigger("Ingelogd als eigenaar!", "success");

      await router.push(`/dashboard/${res.owner.brand.slug}`); // 🔥 redirecten
      return true;
    } catch (err: any) {
      console.error(err);
      trigger(err?.message || "Login mislukt", "error");
      return false;
    }
  }

  async function logout() {
    await apiFetch("/brand-owner/logout", { method: "POST" });
    brandOwnerAuth.logout();
    await router.push("/");
  }

  async function initAuth() {
    await brandOwnerAuth.initAuth(); // gewoon direct je store initAuth gebruiken
  }

  return {
    owner: brandOwnerAuth.owner,
    login,
    logout,
    initAuth,
  };
}
