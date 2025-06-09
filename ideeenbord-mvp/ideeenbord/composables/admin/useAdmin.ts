import { ref } from "vue";
import { useResponseDisplay } from "~/composables/notifications/useResponseDisplay";
import { adminService } from "~/services/api/admin/adminService";
import type { BrandOwner } from "~/types/brand-owner";
import type { Brand } from "~/types/brand";

// Composable providing admin-specific logic for managing brand owner verification
export function useAdmin() {
  // Stores any error message that occurs during admin actions
  const error = ref<string | null>(null);

  // Stores the list of brand owners waiting for verification
  const owners = ref<BrandOwner[]>([]);

  // Stores the list of pending brands waiting to be accepted
  const pendingBrands = ref<Brand[]>([]);

  // UI feedback utility for displaying notifications
  const { triggerByKey } = useResponseDisplay();

  // Fetch all brand owners who are pending verification
  async function fetchPendingOwners() {
    try {
      owners.value = await adminService.fetchPendingOwners();
    } catch (err: any) {
      error.value = err?.message || "Failed to load brand owners.";
      triggerByKey("admin-fetch-failed");
    }
  }

  // Fetch all pending brands
  async function fetchPendingBrands() {
    try {
      pendingBrands.value = await adminService.fetchPendingBrands();
    } catch (err: any) {
      error.value = err?.message || "Failed to load brands.";
      triggerByKey("admin-fetch-failed");
    }
  }

  // Verify a specific brand owner by ID, then refresh the pending list
  async function verifyOwner(id: number) {
    try {
      await adminService.verifyOwner(id);
      triggerByKey("admin-owner-verified");
      await fetchPendingOwners();
    } catch (err: any) {
      error.value = err?.message || "Verification failed.";
      triggerByKey("admin-owner-verification-failed");
    }
  }
  async function acceptBrand(id: number) {
    try {
      await adminService.acceptBrand(id);
      triggerByKey("admin-brand-accepted");
      await fetchPendingBrands();
    } catch (err: any) {
      error.value = err?.message || "Acceptance failed.";
      triggerByKey("admin-brand-acceptance-failed");
    }
  }

  return {
    owners,
    pendingBrands,
    error,
    fetchPendingOwners,
    verifyOwner,
    fetchPendingBrands,
    acceptBrand,
  };
}
