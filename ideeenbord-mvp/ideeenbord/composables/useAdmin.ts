import { ref } from "vue";
import { useResponseDisplay } from "~/composables/useResponseDisplay";
import { adminService } from "~/services/api/adminService";
import type { BrandOwner } from "~/types/brand-owner";

export function useAdmin() {
  const error = ref<string | null>(null);
  const owners = ref<BrandOwner[]>([]);
  const { trigger } = useResponseDisplay();

  async function fetchPendingOwners() {
    try {
      owners.value = await adminService.fetchPendingOwners();
    } catch (err: any) {
      error.value = err?.message || "Kon eigenaren niet laden";
    }
  }

  async function verifyOwner(id: number) {
    try {
      await adminService.verifyOwner(id);
      trigger("Eigenaar succesvol geverifieerd!", "success");
      await fetchPendingOwners();
    } catch (err: any) {
      error.value = err?.message || "Verifiëren mislukt";
      trigger(error.value, "error");
    }
  }

  return { owners, error, fetchPendingOwners, verifyOwner };
}
