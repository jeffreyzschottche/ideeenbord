import { defineStore } from "pinia";

export const useBrandOwnerAuthStore = defineStore("brandOwnerAuth", {
  state: () => ({
    token: null as string | null,
    owner: null as any,
  }),
  actions: {
    setAuth(token: string, owner: any) {
      this.token = token;
      this.owner = owner;
      localStorage.setItem("brand-owner-token", token);
    },
    logout() {
      this.token = null;
      this.owner = null;
      localStorage.removeItem("brand-owner-token");
    },
    async initAuth() {
      if (typeof window !== "undefined") {
        const token = localStorage.getItem("brand-owner-token");

        if (token) {
          this.token = token;

          try {
            const res = await fetch(
              "http://localhost:8000/api/v1/brand-owner/me",
              {
                headers: {
                  "Content-Type": "application/json",
                  Authorization: `Bearer ${token}`,
                },
              }
            );

            if (!res.ok) {
              throw new Error("Kon brand owner info niet ophalen");
            }

            const data = await res.json();
            this.owner = data.owner;
          } catch (e) {
            console.error("Kon brand owner info niet ophalen:", e);
            this.logout();
          }
        }
      }
    },
  },
});
