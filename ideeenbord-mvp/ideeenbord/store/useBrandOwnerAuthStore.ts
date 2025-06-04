/**
 * Authentication store for brand owners.
 * Stores token and owner data in cookies for persistence,
 * and provides actions for login, logout, and session restoration.
 */

import { defineStore } from "pinia";
import { useCookie } from "#app";
import type { BrandOwner } from "~/types/brand-owner";

export const useBrandOwnerAuthStore = defineStore("brandOwnerAuth", {
  state: () => ({
    token: null as string | null,
    owner: null as BrandOwner | null,
  }),

  actions: {
    // Set token and owner, and store them in cookies
    setAuth(token: string, owner: BrandOwner) {
      this.token = token;
      this.owner = owner;

      useCookie("bo_token").value = token;
      useCookie<BrandOwner>("bo_owner").value = owner;
    },

    // Clear session and cookies
    logout() {
      this.token = null;
      this.owner = null;

      useCookie("bo_token").value = null;
      useCookie("bo_owner").value = null;
    },

    // Try to restore session from cookies or backend
    async initAuth() {
      const token = useCookie<string | null>("bo_token");
      const owner = useCookie<BrandOwner | null>("bo_owner");

      // Restore from cookie if both token and owner are present
      if (token.value && owner.value) {
        this.token = token.value;
        this.owner = owner.value;
        return;
      }

      // Try backend fetch if only token is present
      if (token.value) {
        this.token = token.value;

        try {
          const rawApiBase = useRuntimeConfig().public.apiBaseUrl;
          const apiBase = rawApiBase as string;

          const res = await fetch(apiBase + "/brand-owner/me", {
            headers: {
              Authorization: `Bearer ${token.value}`,
              "Content-Type": "application/json",
            },
          });

          if (!res.ok) throw new Error("Failed to fetch brand owner info");

          const data = await res.json();
          this.owner = data.owner;
          useCookie("bo_owner").value = data.owner;
        } catch (e) {
          this.logout(); // fallback on error
        }
      }
    },
  },
});
