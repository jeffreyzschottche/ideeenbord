import { ref } from "vue";
import { getCmsContent } from "~/services/api/content/cmsContentService";

export function useCmsContent(slug: string) {
  const content = ref<Record<string, string>>({});
  const isLoading = ref(true);
  const error = ref<string | null>(null);

  async function fetchContent() {
    try {
      isLoading.value = true;
      content.value = await getCmsContent(slug);
    } catch (err: any) {
      error.value = err.message || "Fout bij ophalen content";
    } finally {
      isLoading.value = false;
    }
  }

  fetchContent();

  return {
    content,
    isLoading,
    error,
  };
}
