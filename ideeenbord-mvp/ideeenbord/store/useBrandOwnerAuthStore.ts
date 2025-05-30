// ~/stores/auth/useBrandOwnerAuthStore.ts
import { defineStore } from "pinia";
import { useCookie } from "#app";
import type { BrandOwner } from "~/types/brand-owner";

export const useBrandOwnerAuthStore = defineStore("brandOwnerAuth", {
  state: () => ({
    token: null as string | null,
    owner: null as BrandOwner | null,
  }),
  actions: {
    setAuth(token: string, owner: BrandOwner) {
      this.token = token;
      this.owner = owner;

      useCookie("bo_token").value = token;
      useCookie<BrandOwner>("bo_owner").value = owner;
    },
    logout() {
      this.token = null;
      this.owner = null;

      useCookie("bo_token").value = null;
      useCookie("bo_owner").value = null;
    },
    async initAuth() {
      const token = useCookie<string | null>("bo_token");
      const owner = useCookie<BrandOwner | null>("bo_owner");

      if (token.value && owner.value) {
        this.token = token.value;
        this.owner = owner.value;
        return;
      }

      if (token.value) {
        this.token = token.value;

        try {
          const res = await fetch(
            "http://localhost:8000/api/v1/brand-owner/me",
            {
              headers: {
                Authorization: `Bearer ${token.value}`,
                "Content-Type": "application/json",
              },
            }
          );

          if (!res.ok) throw new Error("Kon brand owner info niet ophalen");

          const data = await res.json();
          this.owner = data.owner;
          useCookie("bo_owner").value = data.owner;
        } catch (e) {
          console.error("Kon brand owner info niet ophalen:", e);
          this.logout();
        }
      }
    },
  },
});
