import type { NewQuizForm } from "~/types/quiz";
import { brandOwnerService } from "~/services/api/brandOwnerService";

export function useQuizBuilder() {
  async function createQuiz(form: NewQuizForm) {
    return await brandOwnerService.createQuiz(form);
  }

  return { createQuiz };
}
