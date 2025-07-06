import type { Idea } from "~/types/idea";
import { ref } from "vue";
import { useResponseDisplay } from "~/composables/notifications/useResponseDisplay";
import { brandOwnerService } from "~/services/api/brand/brandOwnerService";

export function useManageIdeas(brandId: number) {
  // Extend Idea type to optionally hold a new status during editing
  type EditableIdea = Idea & { newStatus?: string };

  const ideas = ref<EditableIdea[]>([]);
  const error = ref<string | null>(null);
  const { triggerByKey } = useResponseDisplay();

  /**
   * Fetch all ideas for a given brand
   */
  async function fetchIdeas() {
    try {
      ideas.value = await brandOwnerService.getIdeas(brandId);
    } catch (err: any) {
      error.value = err?.message || "Failed to load ideas.";
      triggerByKey("manage-ideas-fetch-failed");
    }
  }

  /**
   * Update the status of an idea (e.g., approved, rejected)
   */
  async function updateIdeaStatus(ideaId: number, status: string) {
    try {
      await brandOwnerService.updateIdeaStatus(ideaId, status);
      triggerByKey("idea-status-updated");
      await fetchIdeas();
    } catch (err: any) {
      error.value = err?.message || "Error updating status.";
      triggerByKey("idea-status-update-failed");
    }
  }

  /**
   * Pin an idea to highlight it
   */
  async function pinIdea(ideaId: number) {
    try {
      await brandOwnerService.pinIdea(ideaId);
      triggerByKey("idea-pinned");
      await fetchIdeas();
    } catch (err: any) {
      error.value = err?.message || "Error pinning idea.";
      triggerByKey("idea-pin-failed");
    }
  }

  /**
   * Unpin an idea
   */
  async function unpinIdea(ideaId: number) {
    try {
      await brandOwnerService.unpinIdea(ideaId);
      triggerByKey("idea-unpinned");
      await fetchIdeas();
    } catch (err: any) {
      error.value = err?.message || "Error unpinning idea.";
      triggerByKey("idea-unpin-failed");
    }
  }

  return { ideas, fetchIdeas, updateIdeaStatus, pinIdea, unpinIdea, error };
}
