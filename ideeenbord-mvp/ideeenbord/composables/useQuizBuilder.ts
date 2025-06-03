import type { NewQuizForm } from "~/types/quiz";
import { quizService } from "~/services/api/quizService";

export function useQuizBuilder() {
  // Creates a new quiz by sending form data to the API
  async function createQuiz(form: NewQuizForm) {
    return await quizService.createQuiz(form);
  }

  return { createQuiz };
}
