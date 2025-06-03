import { ref } from "vue";
import { useResponseDisplay } from "~/composables/useResponseDisplay";
import { adminService } from "~/services/api/adminService";
import type { BrandOwner } from "~/types/brand-owner";

// Composable providing admin-specific logic for managing brand owner verification
export function useAdmin() {
  // Stores any error message that occurs during admin actions
  const error = ref<string | null>(null);

  // Stores the list of brand owners waiting for verification
  const owners = ref<BrandOwner[]>([]);

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

  return { owners, error, fetchPendingOwners, verifyOwner };
}
