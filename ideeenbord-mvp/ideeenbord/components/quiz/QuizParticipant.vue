<script setup lang="ts">
// Import required modules and stores
import { ref, onMounted } from "vue";
import { useResponseDisplay } from "~/composables/notifications/useResponseDisplay";
import { apiFetch } from "~/composables/adapter/useApi";
import { useUserAuthStore } from "~/store/useUserAuthStore";
import type { Quiz } from "~/types/quiz";

// Define props: current brand (id and title)
const props = defineProps<{ brand: { id: number; title: string } }>();

// State and store bindings
const { trigger } = useResponseDisplay();
const user = useUserAuthStore().user;
const quizzes = ref<Quiz[]>([]);
const availableQuizzes = ref<Quiz[]>([]);
const answers = ref<Record<number, Record<number, string>>>({});

// Return answer options for a specific question within a quiz
function getAnswersForQuestion(quiz: Quiz, questionId: number): string[] {
  const match = quiz.quiz_answers?.find(
    (a: any) => a.idQuestion === questionId
  );
  return match ? Object.keys(match.answers) : [];
}

// Load quizzes and filter for available ones that the user hasn't participated in
onMounted(async () => {
  try {
    const all = await apiFetch<Quiz[]>(`/brands/${props.brand.id}/quizzes`);
    quizzes.value = all;

    availableQuizzes.value = all.filter((quiz) => {
      const alreadyParticipated = quiz.participants?.some(
        (p) => p.user_id === user?.id
      );
      return quiz.status === "open" && !alreadyParticipated;
    });

    // Initialize empty answers object per quiz
    for (const quiz of availableQuizzes.value) {
      answers.value[quiz.id] = {};
    }
  } catch (e) {
    trigger("Quiz laden mislukt", "error");
  }
});

// Submit quiz answers and update quiz list
async function submitQuiz(quizId: number) {
  try {
    await apiFetch(`/quizzes/${quizId}/submit`, {
      method: "POST",
      body: { answers: answers.value[quizId] },
    });
    trigger("Quiz succesvol verzonden!", "success");
    availableQuizzes.value = availableQuizzes.value.filter(
      (q) => q.id !== quizId
    );
  } catch (err) {
    trigger("Verzenden quiz mislukt", "error");
  }
}
</script>

<template>
  <div v-if="availableQuizzes.length" class="space-y-8">
    <div
      v-for="quiz in availableQuizzes"
      :key="quiz.id"
      class="bg-white p-6 rounded shadow"
    >
      <h2 class="text-2xl font-bold mb-4">
        Quiz: {{ quiz.title }} van {{ brand.title }}
      </h2>
      <h3>Description {{ quiz.description }}</h3>
      <h4>Prijs : {{ quiz.prize }}</h4>

      <form @submit.prevent="submitQuiz(quiz.id)">
        <div
          v-for="(question, qIndex) in quiz.quiz_questions"
          :key="qIndex"
          class="mb-6"
        >
          <p class="font-semibold mb-2">{{ question.title }}</p>

          <div
            v-for="(answerText, aIndex) in getAnswersForQuestion(
              quiz,
              question.id
            )"
            :key="aIndex"
            class="mb-1"
          >
            <label class="inline-flex items-center">
              <input
                type="radio"
                :name="`quiz-${quiz.id}-question-${qIndex}`"
                :value="answerText"
                v-model="answers[quiz.id][question.id]"
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
  </div>
  <div v-else class="bg-white p-6 rounded shadow">
    <p>Er is momenteel geen quiz beschikbaar waar je nog aan kunt deelnemen.</p>
  </div>
</template>
