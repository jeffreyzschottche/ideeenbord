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

<script setup lang="ts">
import { ref, onMounted } from "vue";
import { useResponseDisplay } from "~/composables/useResponseDisplay";
import { apiFetch } from "~/composables/useApi";
import { useAuthStore } from "~/store/auth";

type Quiz = {
  id: number;
  title: string;
  status: string;
  quiz_questions: { id: number; title: string }[];
  quiz_answers: { idQuestion: number; answers: Record<string, boolean> }[];
  participants?: { user_id: number }[];
};

const props = defineProps<{ brand: { id: number; title: string } }>();
const { trigger } = useResponseDisplay();
const user = useAuthStore().user;
const quizzes = ref<Quiz[]>([]);
const answers = ref<Record<number, Record<number, string>>>({});
const availableQuizzes = ref<Quiz[]>([]);

function getAnswersForQuestion(quiz: any, questionId: number): string[] {
  const match = quiz.quiz_answers?.find(
    (a: any) => a.idQuestion === questionId
  );
  return match ? Object.keys(match.answers) : [];
}

onMounted(async () => {
  try {
    const all = await apiFetch<Quiz[]>(`/brands/${props.brand.id}/quizzes`);
    quizzes.value = all;
    availableQuizzes.value = all.filter((quiz: any) => {
      const alreadyParticipated = quiz.participants?.some(
        (p: any) => p.user_id === user?.id
      );
      return quiz.status === "open" && !alreadyParticipated;
    });

    // Init empty answers per quiz
    for (const quiz of availableQuizzes.value) {
      answers.value[quiz.id] = {};
    }
  } catch (e) {
    trigger("Quiz laden mislukt", "error");
  }
});

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
