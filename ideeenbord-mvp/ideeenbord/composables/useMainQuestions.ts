// ~/composables/useMainQuestions.ts
import { ref } from "vue";
import { apiFetch } from "~/composables/useApi";
import { brandOwnerApiFetch } from "~/composables/useBrandOwnerApi";
import type { MainQuestion } from "~/types/main-question";

const questions = ref<MainQuestion[]>([]);
const error = ref<string | null>(null);

export function useMainQuestions() {
  // 📥 Alle vragen ophalen (voor dropdown, etc.)
  async function fetchMainQuestions() {
    try {
      const res = await brandOwnerApiFetch<MainQuestion[]>("/main-questions");
      questions.value = res;
    } catch (err: any) {
      error.value = err.message || "Kon vragen niet laden.";
    }
  }

  // 📥 Eén vraag ophalen op ID (voor frontend gebruikersweergave)
  async function fetchMainQuestionById(
    id: number | string
  ): Promise<MainQuestion | null> {
    try {
      return await apiFetch<MainQuestion>(`/main-questions/${id}`);
    } catch (err: any) {
      console.error("Kon main question niet laden", err);
      return null;
    }
  }

  // ✅ PATCH: vraag instellen op merk (via dashboard)
  async function setMainQuestionForBrand(
    brandId: number,
    mainQuestionId: number
  ) {
    try {
      return await brandOwnerApiFetch(`/brands/${brandId}/main-questions`, {
        method: "PATCH",
        body: JSON.stringify({ main_question_id: mainQuestionId }),
      });
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
