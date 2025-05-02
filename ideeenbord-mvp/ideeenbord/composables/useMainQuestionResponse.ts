// ~/composables/useMainQuestionResponse.ts
import { useAuthStore } from "~/store/auth";
import { apiFetch } from "~/composables/useApi";

export function useMainQuestionResponse() {
  async function submitMainQuestionResponse(
    brandId: number,
    mainQuestionId: number,
    answer: string
  ) {
    return await apiFetch(`/brands/${brandId}/main-question-response`, {
      method: "POST",
      body: JSON.stringify({
        main_question_id: mainQuestionId,
        answer,
      }),
    });
  }

  return { submitMainQuestionResponse };
}
