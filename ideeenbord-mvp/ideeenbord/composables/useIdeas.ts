import { ref } from "vue";
import { useResponseDisplay } from "~/composables/useResponseDisplay";
import type { Idea, NewIdeaForm } from "~/types/idea";
import { ideaService } from "~/services/api/ideaService";

export function useIdeas(brandId: number) {
  const ideas = ref<Idea[]>([]);
  const error = ref<string | null>(null);
  const { triggerByKey } = useResponseDisplay();

  async function fetchIdeas() {
    try {
      ideas.value = await ideaService.fetchIdeas(brandId);
    } catch (err: any) {
      error.value = err?.message || "Kon ideeën niet laden.";
      triggerByKey("ideas-fetch-failed");
    }
  }

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
      error.value = err?.message || "Idee plaatsen mislukt.";
      triggerByKey("idea-failed");
    }
  }

  async function likeIdea(id: number) {
    try {
      await ideaService.likeIdea(id);
      await fetchIdeas();
      triggerByKey("idea-liked");
    } catch (err: any) {
      error.value = err?.message || "Liken mislukt.";
      triggerByKey("idea-like-failed");
    }
  }

  async function dislikeIdea(id: number) {
    try {
      await ideaService.dislikeIdea(id);
      await fetchIdeas();
      triggerByKey("idea-disliked");
    } catch (err: any) {
      error.value = err?.message || "Disliken mislukt.";
      triggerByKey("idea-dislike-failed");
    }
  }

  return { ideas, fetchIdeas, submitIdea, likeIdea, dislikeIdea, error };
}
