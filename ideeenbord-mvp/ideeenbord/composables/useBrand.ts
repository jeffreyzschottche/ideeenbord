import { ref } from "vue";
import { brandService } from "~/services/api/brandService";
import { brandOwnerService } from "~/services/api/brandOwnerService";
import type { RequestBrandForm, ClaimForm } from "~/types/brand";

export function useBrand() {
  const error = ref<string | null>(null);

  async function requestBrand(form: RequestBrandForm) {
    try {
      return await brandService.requestBrand(form);
    } catch (err: any) {
      error.value =
        err?.data?.message || "Er ging iets mis bij merkregistratie.";
      throw error.value;
    }
  }

  async function claimBrand(form: ClaimForm) {
    try {
      return await brandService.claimBrand(form);
    } catch (err: any) {
      error.value = err?.data?.message || "Claimen mislukt.";
      throw error.value;
    }
  }

  async function updateBrand(brandId: number, updates: Record<string, any>) {
    return await brandOwnerService.updateBrand(brandId, updates);
  }

  return {
    error,
    requestBrand,
    claimBrand,
    updateBrand,
  };
}
