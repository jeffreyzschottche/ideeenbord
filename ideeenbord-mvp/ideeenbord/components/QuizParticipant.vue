<script setup lang="ts">
import { ref, onMounted } from "vue";
import { useResponseDisplay } from "~/composables/useResponseDisplay";
import { apiFetch } from "~/composables/useApi";
import { useUserAuthStore } from "~/store/useUserAuthStore";
import type { Quiz } from "~/types/quiz";

const props = defineProps<{ brand: { id: number; title: string } }>();
const { triggerByKey } = useResponseDisplay();
const user = useUserAuthStore().user;
const quizzes = ref<Quiz[]>([]);
const availableQuizzes = ref<Quiz[]>([]);
const answers = ref<Record<number, Record<number, string>>>({});

function getAnswersForQuestion(quiz: Quiz, questionId: number): string[] {
  const match = quiz.quiz_answers?.find(
    (a: any) => a.idQuestion === questionId
  );
  return match ? Object.keys(match.answers) : [];
}

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

    for (const quiz of availableQuizzes.value) {
      answers.value[quiz.id] = {};
    }
  } catch (e) {
    triggerByKey("quiz-load-failed");
  }
});

async function submitQuiz(quizId: number) {
  try {
    await apiFetch(`/quizzes/${quizId}/submit`, {
      method: "POST",
      body: { answers: answers.value[quizId] },
    });
    triggerByKey("quiz-submitted");
    availableQuizzes.value = availableQuizzes.value.filter(
      (q) => q.id !== quizId
    );
  } catch (err) {
    triggerByKey("quiz-submit-failed");
  }
}
</script>
