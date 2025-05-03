// ~/composables/useMainQuestions.ts
import { ref } from "vue";
import { apiFetch } from "~/composables/useApi";
import { brandOwnerApiFetch } from "~/composables/useBrandOwnerApi";

const questions = ref<any[]>([]);
const error = ref<string | null>(null);

export function useMainQuestions() {
  // 📥 Alle vragen ophalen (voor dropdown, etc.)
  async function fetchMainQuestions() {
    try {
      const res = await brandOwnerApiFetch("/main-questions");
      questions.value = res;
    } catch (err: any) {
      error.value = err.message || "Kon vragen niet laden.";
    }
  }

  // 📥 Eén vraag ophalen op ID (voor frontend gebruikersweergave)
  async function fetchMainQuestionById(id: number | string) {
    try {
      return await apiFetch(`/main-questions/${id}`);
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
