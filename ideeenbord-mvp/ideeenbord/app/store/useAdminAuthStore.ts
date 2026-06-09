/**
 * Admin auth store wrapper that extends the user auth store.
 * Adds an `isAdmin` helper to check if the logged-in user has admin privileges.
 */

import { useUserAuthStore } from "./useUserAuthStore";

export function useAdminAuthStore() {
  const store = useUserAuthStore();

  // Helper to check if current user has admin role
  const isAdmin = () => store.user?.role === "admin";

  return {
    ...store, // Spread existing user auth store
    isAdmin, // Add admin check
  };
}
