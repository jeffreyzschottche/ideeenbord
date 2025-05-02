import { ref } from "vue";
import { brandOwnerApiFetch } from "~/composables/useBrandOwnerApi";

export function useMainQuestions() {
  const questions = ref<any[]>([]);
  const error = ref<string | null>(null);

  async function fetchMainQuestions() {
    try {
      const res = await brandOwnerApiFetch<any[]>("/main-questions");
      questions.value = res;
    } catch (err: any) {
      error.value = err.message || "Kon vragen niet laden.";
    }
  }

  return { questions, fetchMainQuestions, error };
}
