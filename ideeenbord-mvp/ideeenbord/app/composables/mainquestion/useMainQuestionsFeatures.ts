import { ref } from "vue";
import type { MainQuestion } from "~/types/main-question";
import { mainQuestionService } from "~/services/api/mainquestion/mainQuestionService";

/**
 * Composable for handling main questions logic for brands.
 */
export function useMainQuestionsFeatures() {
  const questions = ref<MainQuestion[]>([]);
  const error = ref<string | null>(null);

  /**
   * Fetches all main questions (admin/brand owner only).
   */
  async function fetchMainQuestions() {
    try {
      questions.value = await mainQuestionService.getAll();
    } catch (err: any) {
      error.value = err.message || "Failed to load main questions.";
    }
  }

  /**
   * Fetches a single main question by ID.
   * @param id - ID of the question to fetch
   * @returns The main question or null if not found
   */
  async function fetchMainQuestionById(id: number | string) {
    try {
      return await mainQuestionService.getById(id);
    } catch {
      return null;
    }
  }

  /**
   * Assigns a main question to a brand.
   * @param brandId - ID of the brand
   * @param mainQuestionId - ID of the main question
   */
  async function setMainQuestionForBrand(
    brandId: number,
    mainQuestionId: number
  ) {
    return await mainQuestionService.setMainQuestionForBrand(
      brandId,
      mainQuestionId
    );
  }

  /**
   * Submits a user's answer to a brand's main question.
   * @param brandId - ID of the brand
   * @param mainQuestionId - ID of the main question
   * @param answer - User's answer
   */
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

  return {
    questions,
    error,
    fetchMainQuestions,
    fetchMainQuestionById,
    setMainQuestionForBrand,
    submitMainQuestionResponse,
  };
}
