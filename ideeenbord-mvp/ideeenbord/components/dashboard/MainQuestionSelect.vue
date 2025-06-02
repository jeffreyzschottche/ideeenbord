<template>
  <div class="mt-8">
    <h2 class="text-xl font-bold mb-4">Kies een algemene vraag</h2>
    <select v-model="selectedId" class="w-full border rounded p-2 mb-4">
      <option disabled value="" v-if="currentQuestion?.text">
        {{ currentQuestion.text }}
      </option>
      <option v-for="q in questions" :key="q.id" :value="q.id">
        {{ q.text }}
      </option>
    </select>

    <button
      @click="submit"
      :disabled="!selectedId"
      class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
    >
      Vraag instellen
    </button>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { useMainQuestionsFeatures } from "~/composables/useMainQuestionsFeatures";
import { useBrandOwnerAuthStore } from "~/store/useBrandOwnerAuthStore";
import { useResponseDisplay } from "~/composables/useResponseDisplay";
import type { MainQuestion } from "~/types/main-question";

const brandId = computed(() => brandOwnerAuth.owner?.brand?.id);
const { questions, fetchMainQuestions, setMainQuestionForBrand } =
  useMainQuestionsFeatures();

const { triggerByKey } = useResponseDisplay();
const brandOwnerAuth = useBrandOwnerAuthStore();
const selectedId = ref<string>("");

const currentQuestion = computed<MainQuestion | null>(() => {
  const id = brandOwnerAuth.owner?.brand?.main_question_id;
  if (!id) return null;
  return questions.value.find((q) => q.id === id) || null;
});

onMounted(fetchMainQuestions);

async function submit() {
  if (!selectedId.value) return;

  try {
    await setMainQuestionForBrand(brandId.value, Number(selectedId.value));
    triggerByKey("main-question-set");
  } catch (err: any) {
    triggerByKey("main-question-failed");
  }
}
</script>
