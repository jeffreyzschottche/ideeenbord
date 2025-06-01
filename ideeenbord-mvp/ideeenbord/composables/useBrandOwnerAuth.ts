import { ref } from "vue";
import { useRouter } from "vue-router";
import { apiFetch } from "~/composables/useApi";
import { useResponseDisplay } from "~/composables/useResponseDisplay";
import { useBrandOwnerAuthStore } from "~/store/useBrandOwnerAuthStore";
import type { BrandOwner } from "~/types/brand-owner";
import type { BrandOwnerLoginResponse } from "~/types/brand-owner";

export function useBrandOwnerAuth() {
  const router = useRouter();
  const { triggerByKey } = useResponseDisplay();
  const brandOwnerAuth = useBrandOwnerAuthStore();

  async function login(credentials: { email: string; password: string }) {
    try {
      const res = await apiFetch<BrandOwnerLoginResponse>(
        "/brand-owner/login",
        {
          method: "POST",
          body: credentials,
        }
      );

      brandOwnerAuth.setAuth(res.token, res.owner);
      triggerByKey("brand-owner-login-success");

      await router.push(`/dashboard/${res.owner.brand.slug}`);
      return true;
    } catch (err: any) {
      triggerByKey("brand-owner-login-failed");
      return false;
    }
  }

  async function logout() {
    await apiFetch("/brand-owner/logout", { method: "POST" });
    brandOwnerAuth.logout();
    await router.push("/");
  }

  async function initAuth() {
    await brandOwnerAuth.initAuth();
  }

  return {
    owner: brandOwnerAuth.owner,
    login,
    logout,
    initAuth,
  };
}
