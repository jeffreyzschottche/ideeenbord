import { ref } from "vue";
import { adminService } from "~/services/api/admin/adminService";
import type { CmsPage } from "~/types/cms-page";
import type { CmsField } from "~/types/cms-field";

export function useCms() {
  const pages = ref<CmsPage[]>([]);
  const fields = ref<CmsField[]>([]);
  const error = ref<string | null>(null);

  async function fetchPages() {
    try {
      pages.value = await adminService.fetchCmsPages();
    } catch (err: any) {
      error.value = err?.message || "Kan CMS-pagina’s niet ophalen.";
    }
  }

  async function fetchFields(pageId: number) {
    try {
      fields.value = await adminService.fetchCmsFields(pageId);
    } catch (err: any) {
      error.value = err?.message || "Kan velden niet ophalen.";
    }
  }

  async function createPage(title: string) {
    return await adminService.createCmsPage({ title });
  }

  async function updateField(pageId: number, field: CmsField) {
    return await adminService.updateCmsField(pageId, field);
  }

  async function createField(field: CmsField) {
    return await adminService.createCmsField(field.page_id, field);
  }
  async function uploadImage(file: File): Promise<string> {
    const result = await adminService.uploadCmsImage(file);
    return result.url;
  }
  async function removeField(pageId: number, fieldId: number): Promise<void> {
    await adminService.removeCmsField(pageId, fieldId);
  }

  return {
    pages,
    fields,
    error,
    fetchPages,
    fetchFields,
    createPage,
    updateField,
    createField,
    uploadImage,
    removeField,
  };
}
