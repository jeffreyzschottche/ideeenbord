/**
 * Authentication store for regular users.
 * Handles token and user data using cookies and provides session persistence.
 */

import { defineStore } from "pinia";
// import type { User } from "~/types/user"; // Optional: replace `any` with a proper type

export const useUserAuthStore = defineStore("userAuth", {
  state: () => ({
    token: null as string | null,
    user: null as any | null,
  }),

  actions: {
    // Save token and user to store and cookies
    setAuth(token: string, user: any) {
      this.token = token;
      this.user = user;

      useCookie("token").value = token;
      useCookie("user").value = user;
    },

    // Clear auth state and remove cookies
    logout() {
      this.token = null;
      this.user = null;

      useCookie("token").value = null;
      useCookie("user").value = null;
    },

    // Attempt to restore session from cookies or backend
    async initAuth() {
      // Only run on client
      if (typeof window !== "undefined") {
        const token = useCookie<string | null>("token");
        const user = useCookie<any | null>("user");

        // Restore from both token and user cookie
        if (token.value && user.value) {
          this.token = token.value;
          this.user = user.value;
          return;
        }

        // Fetch user from backend if only token is present
        if (token.value) {
          this.token = token.value;

          try {
            const rawApiBase = useRuntimeConfig().public.apiBaseUrl;
            const apiBase = rawApiBase as string;

            const res = await fetch(`${apiBase}/me`, {
              headers: {
                Authorization: `Bearer ${token}`,
                "Content-Type": "application/json",
              },
            });

            const user = await res.json();
            this.user = user;
          } catch (e) {
            this.logout(); // clear session on failure
          }
        }
      }
    },
  },
});
