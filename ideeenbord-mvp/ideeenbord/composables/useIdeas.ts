import { ref } from "vue";
import { apiFetch } from "~/composables/useApi";
import { useResponseDisplay } from "~/composables/useResponseDisplay";
import type { Idea, NewIdeaForm } from "~/types/idea";

export function useIdeas(brandId: number) {
  const ideas = ref<Idea[]>([]);
  const error = ref<string | null>(null);
  const { trigger } = useResponseDisplay();

  async function fetchIdeas() {
    try {
      ideas.value = await apiFetch<Idea[]>(`/brands/${brandId}/ideas`);
    } catch (err: any) {
      error.value = err?.message || "Kon ideeën niet laden.";
      trigger(`Mistake fetching ideas : ${err}`, "error");
    }
  }

  async function submitIdea(title: string, description: string) {
    const payload: NewIdeaForm = {
      brand_id: brandId,
      title,
      description,
    };
    try {
      await apiFetch("/ideas", {
        method: "POST",
        body: payload,
      });
      await fetchIdeas(); // refresh na plaatsen
      trigger(`Succesfully posted your idea!`, "success");
    } catch (err: any) {
      error.value = err?.message || "Idee plaatsen mislukt.";
      trigger(`Mistake posting idea : ${err}`, "error");
    }
  }

  async function likeIdea(id: number) {
    try {
      await apiFetch(`/ideas/${id}/like`, { method: "POST" });
      await fetchIdeas(); // Refresh de ideeën na like
      trigger("Je hebt het idee geliket! ✅", "success");
    } catch (err: any) {
      error.value = err?.message || "Liken mislukt.";
      trigger(`Fout bij liken: ${err}`, "error");
    }
  }

  async function dislikeIdea(id: number) {
    try {
      await apiFetch(`/ideas/${id}/dislike`, { method: "POST" });
      await fetchIdeas(); // Refresh de ideeën na dislike
      trigger("Je hebt het idee gedisliket! ❌", "warning");
    } catch (err: any) {
      error.value = err?.message || "Disliken mislukt.";
      trigger(`Fout bij disliken: ${err}`, "error");
    }
  }
  return { ideas, fetchIdeas, submitIdea, likeIdea, dislikeIdea, error };
}
