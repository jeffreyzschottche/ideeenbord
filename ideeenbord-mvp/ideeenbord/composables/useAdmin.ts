import { ref } from "vue";
import { apiFetch } from "~/composables/useApi";
import { useResponseDisplay } from "~/composables/useResponseDisplay";

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
    const { trigger } = useResponseDisplay(); // ← haal trigger op

    try {
      await apiFetch(`/admin/brands/owners/${id}/verify`, {
        method: "POST",
      });
      trigger("Eigenaar succesvol geverifieerd!", "success"); // ✅ melding bij succes
      await fetchPendingOwners(); // Refresh lijst
    } catch (err: any) {
      trigger(err?.message || "Verifiëren mislukt", "error"); // ❌ foutmelding
      error.value = err?.message || "Verifiëren mislukt";
    }
  }

  return { owners, error, fetchPendingOwners, verifyOwner };
}
