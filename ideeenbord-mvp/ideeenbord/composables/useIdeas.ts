// ~/composables/useIdeas.ts
import { ref } from "vue";
import { apiFetch } from "~/composables/useApi";

export function useIdeas(brandId: number) {
  const ideas = ref<any[]>([]);
  const error = ref<string | null>(null);

  async function fetchIdeas() {
    try {
      ideas.value = await apiFetch(`/brands/${brandId}/ideas`);
    } catch (err: any) {
      error.value = err?.message || "Kon ideeën niet laden.";
    }
  }

  async function submitIdea(title: string, description: string) {
    try {
      await apiFetch("/ideas", {
        method: "POST",
        body: {
          brand_id: brandId,
          title,
          description,
        },
      });
      await fetchIdeas(); // refresh na plaatsen
    } catch (err: any) {
      error.value = err?.message || "Idee plaatsen mislukt.";
    }
  }

  async function likeIdea(id: number) {
    await apiFetch(`/ideas/${id}/like`, { method: "POST" });
    await fetchIdeas();
  }

  async function dislikeIdea(id: number) {
    await apiFetch(`/ideas/${id}/dislike`, { method: "POST" });
    await fetchIdeas();
  }

  return { ideas, fetchIdeas, submitIdea, likeIdea, dislikeIdea, error };
}
