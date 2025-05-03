<template>
  <div v-if="quiz?.quiz_questions?.length" class="bg-white p-6 rounded shadow">
    <h2 class="text-2xl font-bold mb-4">
      Doe mee aan de quiz van {{ brand.title }}
    </h2>

    <form @submit.prevent="submitQuiz">
      <div
        v-for="(question, qIndex) in quiz.quiz_questions"
        :key="qIndex"
        class="mb-6"
      >
        <p class="font-semibold mb-2">{{ question.title }}</p>

        <div
          v-for="(answerText, aIndex) in getAnswersForQuestion(question.id)"
          :key="aIndex"
          class="mb-1"
        >
          <label class="inline-flex items-center">
            <input
              type="radio"
              :name="`question-${qIndex}`"
              :value="answerText"
              v-model="answers[question.id]"
              class="mr-2"
            />
            {{ answerText }}
          </label>
        </div>
      </div>

      <button
        type="submit"
        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
      >
        Verstuur quiz
      </button>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import { apiFetch } from "~/composables/useApi";
import { useResponseDisplay } from "~/composables/useResponseDisplay";

const props = defineProps<{ brand: { id: number; title: string } }>();
const { trigger } = useResponseDisplay();
const quiz = ref<any | null>(null);
const answers = ref<Record<number, string>>({});

function getAnswersForQuestion(questionId: number): string[] {
  const match = quiz.value?.quiz_answers?.find(
    (a: any) => a.idQuestion === questionId
  );
  if (!match || !match.answers) return [];
  return Object.keys(match.answers);
}

onMounted(async () => {
  try {
    quiz.value = await apiFetch(`/brands/${props.brand.id}/quiz`);
  } catch (e: any) {
    trigger("Quiz laden mislukt", "error");
  }
});

async function submitQuiz() {
  const quizId = quiz.value?.id;
  if (!quizId) return;

  try {
    await apiFetch(`/quizzes/${quizId}/submit`, {
      method: "POST",
      body: { answers: answers.value },
    });
    trigger("Quiz succesvol verzonden!", "success");
  } catch (err: any) {
    trigger("Verzenden quiz mislukt", "error");
  }
}
</script>
