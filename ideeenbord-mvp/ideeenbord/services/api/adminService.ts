import { apiFetch } from "~/composables/useApi";
import type { BrandOwner } from "~/types/brand-owner"; // pas aan naar jouw echte type

export const adminService = {
  async fetchPendingOwners(): Promise<BrandOwner[]> {
    return await apiFetch("/admin/brand-owners");
  },

  async verifyOwner(id: number): Promise<void> {
    await apiFetch(`/admin/brands/owners/${id}/verify`, {
      method: "POST",
    });
  },
};
