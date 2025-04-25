import { ref } from "vue";
import { brandService } from "~/services/api/brandService";
import type { RequestBrandForm } from "~/types/brand";
import type { ClaimForm } from "~/types/brand";

export function useRequestBrand() {
  const error = ref<string | null>(null);

  async function requestBrand(form: RequestBrandForm) {
    try {
      const response = await brandService.requestBrand(form);
      return response;
    } catch (err: any) {
      error.value =
        err?.data?.message || "Er ging iets mis bij merkregistratie.";
      throw error.value;
    }
  }

  return { requestBrand, error };
}

export function useClaimBrand() {
  const error = ref<string | null>(null);

  async function claimBrand(form: ClaimForm) {
    try {
      const response = await brandService.claimBrand(form);
      return response;
    } catch (err: any) {
      error.value = err?.data?.message || "Claimen mislukt.";
      throw error.value;
    }
  }

  return { claimBrand, error };
}
