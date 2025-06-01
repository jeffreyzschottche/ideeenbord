import { ref } from "vue";
import type { MainQuestion } from "~/types/main-question";
import { mainQuestionService } from "~/services/api/mainQuestionService";

const questions = ref<MainQuestion[]>([]);
const error = ref<string | null>(null);

export function useMainQuestions() {
  async function fetchMainQuestions() {
    try {
      questions.value = await mainQuestionService.getAll();
    } catch (err: any) {
      error.value = err.message || "Kon vragen niet laden.";
    }
  }

  async function fetchMainQuestionById(id: number | string) {
    try {
      return await mainQuestionService.getById(id);
    } catch (err: any) {
      return null;
    }
  }

  async function setMainQuestionForBrand(
    brandId: number,
    mainQuestionId: number
  ) {
    return await mainQuestionService.setMainQuestionForBrand(
      brandId,
      mainQuestionId
    );
  }

  return {
    questions,
    error,
    fetchMainQuestions,
    fetchMainQuestionById,
    setMainQuestionForBrand,
  };
}
