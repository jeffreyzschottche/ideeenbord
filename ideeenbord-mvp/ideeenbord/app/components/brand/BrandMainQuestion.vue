<script setup lang="ts">
import { computed, ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import { useUserAuthStore } from "~/store/useUserAuthStore";
import { useResponseDisplay } from "~/composables/notifications/useResponseDisplay";
import { useMainQuestionsFeatures } from "~/composables/mainquestion/useMainQuestionsFeatures";
import type { Brand } from "~/types/brand";
import type { MainQuestion } from "~/types/main-question";

const props = defineProps<{ brand: Brand }>();

const question = ref<MainQuestion | null>(null);
const { fetchMainQuestionById, submitMainQuestionResponse } =
  useMainQuestionsFeatures();
const { triggerByKey } = useResponseDisplay();
const auth = useUserAuthStore();
const route = useRoute();

// Fetch the question for this brand on mount
onMounted(async () => {
  if (props.brand.main_question_id) {
    question.value = await fetchMainQuestionById(props.brand.main_question_id);
  }
});

// Replace placeholders in question text
const parsed = computed(() => {
  if (!question.value) return null;
  return {
    id: question.value.id,
    text: question.value.text
      .replace(/\[merknaam\]/gi, props.brand.title)
      .replace(/\[categorie\]/gi, props.brand.category),
    answers: question.value.answers,
  };
});

// Handle freeform answer on blur
function handleAnswerInput(event: Event) {
  const target = event.target as HTMLTextAreaElement;
  if (target?.value) {
    handleAnswer(target.value);
  }
}

// Submit answer to API
async function handleAnswer(answer: string) {
  if (!auth.token) {
    return triggerByKey("question-login-required");
  }

  try {
    await submitMainQuestionResponse(props.brand.id, parsed.value!.id, answer);
    triggerByKey("question-saved");
  } catch (err: any) {
    if (err?.statusCode === 409) {
      triggerByKey("question-already-answered");
    } else {
      triggerByKey("question-save-failed");
    }
  }
}
</script>

<template>
  <div v-if="parsed" class="bg-gray-100 p-4 rounded shadow mb-6">
    <h2 class="text-lg font-semibold mb-2">
      Wat vind jij van {{ brand.title }}?
    </h2>
    <p class="text-gray-800 mb-4">{{ parsed.text }}</p>

    <div v-if="parsed.answers?.length" class="flex flex-wrap gap-2">
      <button
        v-for="(answer, index) in parsed.answers"
        :key="index"
        class="px-4 py-2 bg-blue-100 text-blue-800 rounded hover:bg-blue-200"
        @click="handleAnswer(answer)"
      >
        {{ answer }}
      </button>
    </div>

    <div v-else>
      <textarea
        rows="3"
        class="w-full border p-2 rounded mt-2"
        placeholder="Typ hier je antwoord..."
        @blur="handleAnswerInput"
      ></textarea>
    </div>
  </div>
</template>
