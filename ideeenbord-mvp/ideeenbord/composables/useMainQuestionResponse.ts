import { mainQuestionService } from "~/services/api/mainQuestionService";

export function useMainQuestionResponse() {
  async function submitMainQuestionResponse(
    brandId: number,
    mainQuestionId: number,
    answer: string
  ) {
    return await mainQuestionService.submitResponse(
      brandId,
      mainQuestionId,
      answer
    );
  }

  return { submitMainQuestionResponse };
}
