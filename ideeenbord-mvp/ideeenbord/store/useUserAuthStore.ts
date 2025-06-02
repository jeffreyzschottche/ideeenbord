// ~/stores/auth/useUserAuthStore.ts
import { defineStore } from "pinia";
// import type { User } from "~/types/user"; // of inline definiëren

export const useUserAuthStore = defineStore("userAuth", {
  state: () => ({
    token: null as string | null,
    user: null as any | null,
  }),
  actions: {
    setAuth(token: string, user: any) {
      this.token = token;
      this.user = user;

      useCookie("token").value = token;
      useCookie("user").value = user;
    },
    logout() {
      this.token = null;
      this.user = null;
      useCookie("token").value = null;
      useCookie("user").value = null;
    },
    async initAuth() {
      if (typeof window !== "undefined") {
        const token = useCookie<string | null>("token");
        const user = useCookie<any | null>("user");
        if (token.value && user.value) {
          this.token = token.value;
          this.user = user.value;
          return;
        }
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
            this.logout();
          }
        }
      }
    },
  },
});
