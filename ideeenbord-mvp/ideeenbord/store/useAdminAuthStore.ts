import { useUserAuthStore } from "./useUserAuthStore";

export function useAdminAuthStore() {
  const store = useUserAuthStore();

  const isAdmin = () => store.user?.role === "admin";

  return {
    ...store,
    isAdmin,
  };
}
