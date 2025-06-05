// This service handles all API calls related to brand owners,
// including account updates, managing ideas, brand updates,
// and assigning main questions.

import { brandOwnerApiFetch } from "~/composables/useBrandOwnerApi";
import type { UpdateBrandOwnerForm, BrandOwner } from "~/types/brand-owner";
import type { Idea } from "~/types/idea";
import type { Quiz, NewQuizForm } from "~/types/quiz";
import type { MainQuestion } from "~/types/main-question";

export const brandOwnerService = {
  // Update brand owner account information
  async updateAccount(data: UpdateBrandOwnerForm) {
    return await brandOwnerApiFetch("/brand-owner/account", {
      method: "PATCH",
      body: JSON.stringify(data),
    });
  },

  // Retrieve ideas linked to a brand
  async getIdeas(brandId: number): Promise<Idea[]> {
    return await brandOwnerApiFetch(`/brands/${brandId}/ideas`);
  },

  // Update the status of an idea
  async updateIdeaStatus(id: number, status: string) {
    return await brandOwnerApiFetch(`/ideas/${id}`, {
      method: "PATCH",
      body: JSON.stringify({ status }),
    });
  },

  // Pin an idea
  async pinIdea(id: number) {
    return await brandOwnerApiFetch(`/ideas/${id}/pin`, { method: "PATCH" });
  },

  // Unpin an idea
  async unpinIdea(id: number) {
    return await brandOwnerApiFetch(`/ideas/${id}/unpin`, { method: "PATCH" });
  },

  // Update brand-specific fields
  async updateBrand(brandId: number, updates: Record<string, any>) {
    return await brandOwnerApiFetch(`/brands/${brandId}`, {
      method: "PATCH",
      body: JSON.stringify(updates),
    });
  },

  // Retrieve list of available main questions
  async getMainQuestions(): Promise<MainQuestion[]> {
    return await brandOwnerApiFetch("/main-questions");
  },

  // Assign a main question to a brand
  async setMainQuestionForBrand(brandId: number, questionId: number) {
    return await brandOwnerApiFetch(`/brands/${brandId}/main-questions`, {
      method: "PATCH",
      body: JSON.stringify({ main_question_id: questionId }),
    });
  },

  // Fetch a specific main question by ID (uses public API)
  async getMainQuestionById(id: number | string): Promise<MainQuestion> {
    return await apiFetch(`/main-questions/${id}`);
  },
};
