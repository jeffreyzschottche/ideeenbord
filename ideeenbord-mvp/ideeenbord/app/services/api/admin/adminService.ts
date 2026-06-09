import { apiFetch } from "~/composables/adapter/useApi";
import type { BrandOwner } from "~/types/brand-owner";
import type { Brand } from "~/types/brand";
import type { CmsPage } from "~/types/cms-page";
import type { CmsField } from "~/types/cms-field";

// This service handles admin-related API calls, such as verifying brand owners.

export const adminService = {
  // Fetches all brand owners who are pending verification
  async fetchPendingOwners(): Promise<BrandOwner[]> {
    return await apiFetch("/admin/brand-owners");
  },

  // Verifies a specific brand owner by ID
  async verifyOwner(id: number): Promise<void> {
    await apiFetch(`/admin/brands/owners/${id}/verify`, {
      method: "POST",
    });
  },
  async fetchPendingBrands(): Promise<Brand[]> {
    return await apiFetch("/admin/brands/pending");
  },

  async acceptBrand(id: number): Promise<void> {
    await apiFetch(`/admin/brands/${id}/accept`, { method: "POST" });
  },
  async fetchCmsPages(): Promise<CmsPage[]> {
    return await apiFetch("/admin/cms/pages");
  },

  async createCmsPage(data: { title: string }): Promise<CmsPage> {
    return await apiFetch("/admin/cms/pages", {
      method: "POST",
      body: data,
    });
  },

  async fetchCmsFields(pageId: number): Promise<CmsField[]> {
    return await apiFetch(`/admin/cms/pages/${pageId}/fields`);
  },

  async updateCmsField(pageId: number, field: CmsField): Promise<CmsField> {
    return await apiFetch(`/admin/cms/pages/${pageId}/fields/${field.id}`, {
      method: "PATCH",
      body: field,
    });
  },

  async createCmsField(pageId: number, field: CmsField): Promise<CmsField> {
    return await apiFetch(`/admin/cms/pages/${pageId}/fields`, {
      method: "POST",
      body: field,
    });
  },
  async uploadCmsImage(file: File): Promise<{ url: string }> {
    const form = new FormData();
    form.append("file", file);

    return await apiFetch("/admin/cms/upload", {
      method: "POST",
      body: form,
    });
  },
  async removeCmsField(pageId: number, fieldId: number): Promise<void> {
    await apiFetch(`/admin/cms/pages/${pageId}/fields/${fieldId}`, {
      method: "DELETE",
    });
  },
};
