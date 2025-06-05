import { apiFetch } from "~/composables/adapter/useApi";
import type { BrandOwner } from "~/types/brand-owner"; // replace with correct type if needed

// This service handles admin-related API calls, such as verifying brand owners.

export const adminService = {
  // Fetches all brand owners who are pending verification
  async fetchPendingOwners(): Promise<BrandOwner[]> {
    return await apiFetch("/admin/brand-owners");
  },

  // Verifies a specific brand owner by ID
  async verifyOwner(id: number): Promise<void> {
    await apiFetch(`/admin/brands/owners/${id}/verify`, {
      method: "POST",
    });
  },
};
