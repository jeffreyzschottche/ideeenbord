import { ref } from "vue";
import { useRouter } from "vue-router";
import { apiFetch } from "~/composables/useApi";
import { useResponseDisplay } from "~/composables/useResponseDisplay";
import { useBrandOwnerAuthStore } from "~/store/useBrandOwnerAuthStore";
import type { BrandOwner } from "~/types/brand-owner";
import type { BrandOwnerLoginResponse } from "~/types/brand-owner";

/**
 * Composable for handling Brand Owner authentication logic.
 */
export function useBrandOwnerAuth() {
  const router = useRouter();
  const { triggerByKey } = useResponseDisplay();
  const brandOwnerAuth = useBrandOwnerAuthStore();

  /**
   * Logs in a brand owner using email and password.
   * On success, stores auth data and redirects to the dashboard.
   * @param credentials - Email and password for login
   * @returns boolean indicating success
   */
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

  /**
   * Logs out the current brand owner and navigates back to the homepage.
   */
  async function logout() {
    await apiFetch("/brand-owner/logout", { method: "POST" });
    brandOwnerAuth.logout();
    await router.push("/");
  }

  /**
   * Initializes brand owner authentication from cookies or persisted state.
   */
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
