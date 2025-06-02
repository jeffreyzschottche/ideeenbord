import { ref } from "vue";
import type { MainQuestion } from "~/types/main-question";
import { mainQuestionService } from "~/services/api/mainQuestionService";

export function useMainQuestionsFeatures() {
  const questions = ref<MainQuestion[]>([]);
  const error = ref<string | null>(null);

  // Alle vragen ophalen (alleen voor admins/brand owners)
  async function fetchMainQuestions() {
    try {
      questions.value = await mainQuestionService.getAll();
    } catch (err: any) {
      error.value = err.message || "Kon vragen niet laden.";
    }
  }

  // Eén vraag ophalen op basis van ID
  async function fetchMainQuestionById(id: number | string) {
    try {
      return await mainQuestionService.getById(id);
    } catch {
      return null;
    }
  }

  // Vraag instellen op merk
  async function setMainQuestionForBrand(
    brandId: number,
    mainQuestionId: number
  ) {
    return await mainQuestionService.setMainQuestionForBrand(
      brandId,
      mainQuestionId
    );
  }

  // Antwoord op main question insturen
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
