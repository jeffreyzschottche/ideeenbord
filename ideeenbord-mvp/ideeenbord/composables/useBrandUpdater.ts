import { brandOwnerService } from "~/services/api/brandOwnerService";

export function useBrandUpdater() {
  async function updateBrand(brandId: number, updates: Record<string, any>) {
    return await brandOwnerService.updateBrand(brandId, updates);
  }

  return { updateBrand };
}
