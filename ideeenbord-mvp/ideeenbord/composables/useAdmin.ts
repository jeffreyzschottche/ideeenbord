import { ref } from "vue";

export function useAdmin() {
  const error = ref<string | null>(null);
  const owners = ref<any[]>([]);

  async function fetchPendingOwners() {
    try {
      owners.value = await $fetch("http://localhost:8000/api/brand-owners", {
        headers: {
          credentials: "include",
          Authorization: `Bearer ${localStorage.getItem("token")}`,
        },
      });
    } catch (err: any) {
      error.value = err?.data?.message || "Kon eigenaren niet laden";
    }
  }

  async function verifyOwner(id: number) {
    try {
      await $fetch(`http://localhost:8000/api/brands/owners/${id}/verify`, {
        method: "POST",
        headers: {
          Authorization: `Bearer ${localStorage.getItem("token")}`,
        },
      });
      await fetchPendingOwners(); // refresh list
    } catch (err: any) {
      error.value = err?.data?.message || "Verifiëren mislukt";
    }
  }

  return { owners, error, fetchPendingOwners, verifyOwner };
}
