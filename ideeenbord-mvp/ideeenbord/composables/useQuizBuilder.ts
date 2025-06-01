import type { NewQuizForm } from "~/types/quiz";
import { quizService } from "~/services/api/quizService";

export function useQuizBuilder() {
  async function createQuiz(form: NewQuizForm) {
    return await quizService.createQuiz(form);
  }

  return { createQuiz };
}
