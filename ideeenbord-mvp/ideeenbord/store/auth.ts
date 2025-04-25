import { defineStore } from "pinia";

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

        // ✅ Zet token direct zodat middleware het meteen ziet
        if (token) {
          this.token = token;

          // 🔁 User ophalen mag async daarna gebeuren
          try {
            const user = await $fetch("http://localhost:8000/api/me", {
              headers: {
                Authorization: `Bearer ${token}`,
              },
            });
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
