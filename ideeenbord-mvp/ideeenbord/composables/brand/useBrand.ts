import { ref } from "vue";
import { brandService } from "~/services/api/brand/brandService";
import { brandOwnerService } from "~/services/api/brand/brandOwnerService";
import type { RequestBrandForm, ClaimForm } from "~/types/brand";

/**
 * Composable for handling brand-related actions:
 * - requesting a new brand
 * - claiming an existing brand
 * - updating brand details (owner only)
 */
export function useBrand() {
  const error = ref<string | null>(null);

  /**
   * Submit a request to register a new brand.
   */
  async function requestBrand(form: RequestBrandForm) {
    try {
      return await brandService.requestBrand(form);
    } catch (err: any) {
      error.value =
        err?.data?.message || "Something went wrong during brand registration.";
      throw error.value;
    }
  }

  /**
   * Submit a claim to take ownership of an existing brand.
   */
  async function claimBrand(form: ClaimForm) {
    try {
      return await brandService.claimBrand(form);
    } catch (err: any) {
      error.value = err?.data?.message || "Brand claim failed.";
      throw error.value;
    }
  }

  /**
   * Update brand data (only allowed for brand owner).
   */
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
