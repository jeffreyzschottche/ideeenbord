/**
 * Service for handling main question logic for both brands and users.
 * Includes fetching available questions, setting a question for a brand,
 * and submitting user responses to a selected main question.
 */

import { apiFetch } from "~/composables/useApi";
import { brandOwnerApiFetch } from "~/composables/useBrandOwnerApi";
import type { MainQuestion, MainQuestionResponse } from "~/types/main-question";

export const mainQuestionService = {
  // Get all main questions (for brand owner selection)
  async getAll(): Promise<MainQuestion[]> {
    return await brandOwnerApiFetch("/main-questions");
  },

  // Get a specific main question by ID
  async getById(id: number | string): Promise<MainQuestion> {
    return await apiFetch(`/main-questions/${id}`);
  },

  // Set a specific main question for a given brand
  async setMainQuestionForBrand(brandId: number, mainQuestionId: number) {
    return await brandOwnerApiFetch(`/brands/${brandId}/main-questions`, {
      method: "PATCH",
      body: JSON.stringify({ main_question_id: mainQuestionId }),
    });
  },

  // Submit a response to a brand's main question
  async submitResponse(
    brandId: number,
    mainQuestionId: number,
    answer: string
  ): Promise<MainQuestionResponse> {
    return await apiFetch(`/brands/${brandId}/main-question-response`, {
      method: "POST",
      body: JSON.stringify({
        main_question_id: mainQuestionId,
        answer,
      }),
    });
  },
};
