import { brandOwnerApiFetch } from "./useBrandOwnerApi";

export function useBrandUpdater() {
  async function updateBrand(brandId: number, updates: Record<string, any>) {
    return await brandOwnerApiFetch(`/brands/${brandId}`, {
      method: "PATCH",
      body: JSON.stringify(updates),
    });
  }

  return { updateBrand };
}
