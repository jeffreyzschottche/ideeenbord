import { ref } from "vue";
import { useResponseDisplay } from "~/composables/notifications/useResponseDisplay";
import type { Idea, NewIdeaForm } from "~/types/idea";
import { ideaService } from "~/services/api/ideas/ideaService";

/**
 * Composable for managing ideas related to a specific brand.
 * Handles fetching, submitting, liking, and disliking ideas.
 */
export function useIdeas(brandId: number) {
  const ideas = ref<Idea[]>([]);
  const error = ref<string | null>(null);
  const { triggerByKey } = useResponseDisplay();

  /**
   * Fetches all ideas for the current brand.
   */
  async function fetchIdeas() {
    try {
      ideas.value = await ideaService.fetchIdeas(brandId);
    } catch (err: any) {
      error.value = err?.message || "Failed to load ideas.";
      triggerByKey("ideas-fetch-failed");
    }
  }

  /**
   * Submits a new idea with a given title and description.
   * Automatically refreshes the list after submission.
   * @param title - The title of the new idea
   * @param description - The description/content of the idea
   */
  async function submitIdea(title: string, description: string) {
    const payload: NewIdeaForm = {
      brand_id: brandId,
      title,
      description,
    };
    try {
      await ideaService.submitIdea(payload);
      await fetchIdeas();
      triggerByKey("idea-posted");
    } catch (err: any) {
      error.value = err?.message || "Failed to submit idea.";
      triggerByKey("idea-failed");
    }
  }

  /**
   * Likes an idea by its ID and refreshes the list.
   * @param id - ID of the idea to like
   */
  async function likeIdea(id: number) {
    try {
      await ideaService.likeIdea(id);
      await fetchIdeas();
      triggerByKey("idea-liked");
    } catch (err: any) {
      error.value = err?.message || "Failed to like idea.";
      triggerByKey("idea-like-failed");
    }
  }

  /**
   * Dislikes an idea by its ID and refreshes the list.
   * @param id - ID of the idea to dislike
   */
  async function dislikeIdea(id: number) {
    try {
      await ideaService.dislikeIdea(id);
      await fetchIdeas();
      triggerByKey("idea-disliked");
    } catch (err: any) {
      error.value = err?.message || "Failed to dislike idea.";
      triggerByKey("idea-dislike-failed");
    }
  }

  return { ideas, fetchIdeas, submitIdea, likeIdea, dislikeIdea, error };
}
