import { ref } from "vue";
import { apiFetch } from "~/composables/useApi";

export function useAdmin() {
  const error = ref<string | null>(null);
  const owners = ref<any[]>([]);

  async function fetchPendingOwners() {
    try {
      owners.value = await apiFetch("/admin/brand-owners");
    } catch (err: any) {
      error.value = err?.message || "Kon eigenaren niet laden";
    }
  }

  async function verifyOwner(id: number) {
    try {
      await apiFetch(`/admin/brands/owners/${id}/verify`, {
        method: "POST",
      });
      await fetchPendingOwners(); // Refresh list
    } catch (err: any) {
      error.value = err?.message || "Verifiëren mislukt";
    }
  }

  return { owners, error, fetchPendingOwners, verifyOwner };
}
