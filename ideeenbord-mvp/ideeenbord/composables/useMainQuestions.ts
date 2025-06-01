import { ref } from "vue";
import type { MainQuestion } from "~/types/main-question";
import { brandOwnerService } from "~/services/api/brandOwnerService";

const questions = ref<MainQuestion[]>([]);
const error = ref<string | null>(null);

export function useMainQuestions() {
  async function fetchMainQuestions() {
    try {
      questions.value = await brandOwnerService.getMainQuestions();
    } catch (err: any) {
      error.value = err.message || "Kon vragen niet laden.";
    }
  }

  async function fetchMainQuestionById(
    id: number | string
  ): Promise<MainQuestion | null> {
    try {
      return await brandOwnerService.getMainQuestionById(id);
    } catch (err: any) {
      console.error("Kon main question niet laden", err);
      return null;
    }
  }

  async function setMainQuestionForBrand(
    brandId: number,
    mainQuestionId: number
  ) {
    try {
      return await brandOwnerService.setMainQuestionForBrand(
        brandId,
        mainQuestionId
      );
    } catch (err) {
      throw err;
    }
  }

  return {
    questions,
    error,
    fetchMainQuestions,
    fetchMainQuestionById,
    setMainQuestionForBrand,
  };
}
