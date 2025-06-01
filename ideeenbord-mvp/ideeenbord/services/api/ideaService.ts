import { apiFetch } from "~/composables/useApi";
import type { Idea, NewIdeaForm } from "~/types/idea";

export const ideaService = {
  async fetchIdeas(brandId: number): Promise<Idea[]> {
    return await apiFetch(`/brands/${brandId}/ideas`);
  },

  async submitIdea(data: NewIdeaForm) {
    return await apiFetch("/ideas", {
      method: "POST",
      body: data,
    });
  },

  async likeIdea(id: number) {
    return await apiFetch(`/ideas/${id}/like`, { method: "POST" });
  },

  async dislikeIdea(id: number) {
    return await apiFetch(`/ideas/${id}/dislike`, { method: "POST" });
  },
};
