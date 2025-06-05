<script setup lang="ts">
/*
  Fetches a list of main questions and allows the brand owner to select one.
  On submit, it links the selected question to the brand and triggers feedback.
*/

import { ref, computed, onMounted } from "vue";
import { useMainQuestionsFeatures } from "~/composables/useMainQuestionsFeatures";
import { useBrandOwnerAuthStore } from "~/store/useBrandOwnerAuthStore";
import { useResponseDisplay } from "~/composables/useResponseDisplay";
import type { MainQuestion } from "~/types/main-question";

// Store containing current brand owner data
const brandOwnerAuth = useBrandOwnerAuthStore();

// Current brand ID (null if not logged in or not linked to brand)
const brandId = computed(() => brandOwnerAuth.owner?.brand?.id);

// Extracted logic for fetching and setting questions
const { questions, fetchMainQuestions, setMainQuestionForBrand } =
  useMainQuestionsFeatures();

const { triggerByKey } = useResponseDisplay();

// ID of the newly selected question
const selectedId = ref<string>("");

/*
  Computed property to find the brand's currently linked main question, if any.
  Used to display the question as a disabled default option in the select box.
*/
const currentQuestion = computed<MainQuestion | null>(() => {
  const id = brandOwnerAuth.owner?.brand?.main_question_id;
  if (!id) return null;
  return questions.value.find((q) => q.id === id) || null;
});

// Fetch all available questions when component is mounted
onMounted(fetchMainQuestions);

/*
  Submit the selected question to link it with the current brand.
  Shows success or failure message based on API result.
*/
async function submit() {
  if (!selectedId.value) return;

  try {
    await setMainQuestionForBrand(brandId.value, Number(selectedId.value));
    triggerByKey("main-question-set"); // Success toast
  } catch (err: any) {
    triggerByKey("main-question-failed"); // Failure toast
  }
}
</script>

<template>
  <!--   UI for selecting a predefined "main question" for a brand.
    If the brand already has a question, it appears as the disabled default option.
  -->
  <div class="mt-8">
    <h2 class="text-xl font-bold mb-4">Kies een algemene vraag</h2>

    <!-- Dropdown menu with available questions -->
    <select v-model="selectedId" class="w-full border rounded p-2 mb-4">
      <option disabled value="" v-if="currentQuestion?.text">
        {{ currentQuestion.text }}
      </option>
      <option v-for="q in questions" :key="q.id" :value="q.id">
        {{ q.text }}
      </option>
    </select>

    <!-- Button to submit the selected question -->
    <button
      @click="submit"
      :disabled="!selectedId"
      class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
    >
      Vraag instellen
    </button>
  </div>
</template>
