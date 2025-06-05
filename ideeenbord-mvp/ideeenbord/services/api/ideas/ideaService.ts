// This service handles all public-facing idea operations,
// such as submitting a new idea and liking/disliking existing ones.

import { apiFetch } from "~/composables/useApi";
import type { Idea, NewIdeaForm } from "~/types/idea";

export const ideaService = {
  // Fetch all ideas for a specific brand
  async fetchIdeas(brandId: number): Promise<Idea[]> {
    return await apiFetch(`/brands/${brandId}/ideas`);
  },

  // Submit a new idea to the server
  async submitIdea(data: NewIdeaForm) {
    return await apiFetch("/ideas", {
      method: "POST",
      body: data,
    });
  },

  // Like an idea by ID
  async likeIdea(id: number) {
    return await apiFetch(`/ideas/${id}/like`, { method: "POST" });
  },

  // Dislike an idea by ID
  async dislikeIdea(id: number) {
    return await apiFetch(`/ideas/${id}/dislike`, { method: "POST" });
  },
};
