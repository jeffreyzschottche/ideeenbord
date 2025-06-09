import { apiFetch } from "~/composables/adapter/useApi";
import type { BrandOwner } from "~/types/brand-owner";
import type { Brand } from "~/types/brand";

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
  async fetchPendingBrands(): Promise<Brand[]> {
    return await apiFetch("/admin/brands/pending");
  },

  async acceptBrand(id: number): Promise<void> {
    await apiFetch(`/admin/brands/${id}/accept`, { method: "POST" });
  },
};
