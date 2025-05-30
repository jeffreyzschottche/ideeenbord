import { useUserAuthStore } from "~/store/useUserAuthStore";
import { apiFetch } from "~/composables/useApi";
import type { MainQuestionResponse } from "~/types/main-question";

export function useMainQuestionResponse() {
  async function submitMainQuestionResponse(
    brandId: number,
    mainQuestionId: number,
    answer: string
  ): Promise<MainQuestionResponse> {
    return await apiFetch<MainQuestionResponse>(
      `/brands/${brandId}/main-question-response`,
      {
        method: "POST",
        body: JSON.stringify({
          main_question_id: mainQuestionId,
          answer,
        }),
      }
    );
  }

  return { submitMainQuestionResponse };
}
