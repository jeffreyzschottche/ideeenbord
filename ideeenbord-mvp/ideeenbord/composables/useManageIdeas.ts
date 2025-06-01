import type { Idea } from "~/types/idea";
import { ref } from "vue";
import { useResponseDisplay } from "~/composables/useResponseDisplay";
import { brandOwnerService } from "~/services/api/brandOwnerService";

export function useManageIdeas(brandId: number) {
  type EditableIdea = Idea & { newStatus?: string };
  const ideas = ref<EditableIdea[]>([]);
  const error = ref<string | null>(null);
  const { triggerByKey } = useResponseDisplay();

  async function fetchIdeas() {
    try {
      ideas.value = await brandOwnerService.getIdeas(brandId);
    } catch (err: any) {
      error.value = err?.message || "Kon ideeën niet laden.";
      triggerByKey("manage-ideas-fetch-failed");
    }
  }

  async function updateIdeaStatus(ideaId: number, status: string) {
    try {
      await brandOwnerService.updateIdeaStatus(ideaId, status);
      triggerByKey("idea-status-updated");
      await fetchIdeas();
    } catch (err: any) {
      error.value = err?.message || "Fout bij updaten status.";
      triggerByKey("idea-status-update-failed");
    }
  }

  async function pinIdea(ideaId: number) {
    try {
      await brandOwnerService.pinIdea(ideaId);
      triggerByKey("idea-pinned");
      await fetchIdeas();
    } catch (err: any) {
      error.value = err?.message || "Fout bij vastzetten.";
      triggerByKey("idea-pin-failed");
    }
  }

  async function unpinIdea(ideaId: number) {
    try {
      await brandOwnerService.unpinIdea(ideaId);
      triggerByKey("idea-unpinned");
      await fetchIdeas();
    } catch (err: any) {
      error.value = err?.message || "Fout bij losmaken.";
      triggerByKey("idea-unpin-failed");
    }
  }

  return { ideas, fetchIdeas, updateIdeaStatus, pinIdea, unpinIdea, error };
}
