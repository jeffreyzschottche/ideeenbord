// ~/store/auth.ts
import { defineStore } from "pinia";
import { apiFetch } from "~/composables/useApi";

export const useAuthStore = defineStore("auth", {
  state: () => ({
    token: null as string | null,
    user: null as any,
  }),
  actions: {
    setAuth(token: string, user: any) {
      this.token = token;
      this.user = user;
      localStorage.setItem("token", token);
    },
    logout() {
      this.token = null;
      this.user = null;
      localStorage.removeItem("token");
    },
    async initAuth() {
      if (typeof window !== "undefined") {
        const token = localStorage.getItem("token");

        if (token) {
          this.token = token;

          try {
            const user = await apiFetch("/me");
            this.user = user;
          } catch (e) {
            console.error("Kon user info niet ophalen:", e);
            this.logout();
          }
        }
      }
    },
  },
});
