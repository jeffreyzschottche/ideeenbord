// ~/services/api/mainQuestionService.ts
import { apiFetch } from "~/composables/useApi";
import { brandOwnerApiFetch } from "~/composables/useBrandOwnerApi";
import type { MainQuestion, MainQuestionResponse } from "~/types/main-question";

export const mainQuestionService = {
  async getAll(): Promise<MainQuestion[]> {
    return await brandOwnerApiFetch("/main-questions");
  },

  async getById(id: number | string): Promise<MainQuestion> {
    return await apiFetch(`/main-questions/${id}`);
  },

  async setMainQuestionForBrand(brandId: number, mainQuestionId: number) {
    return await brandOwnerApiFetch(`/brands/${brandId}/main-questions`, {
      method: "PATCH",
      body: JSON.stringify({ main_question_id: mainQuestionId }),
    });
  },

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
