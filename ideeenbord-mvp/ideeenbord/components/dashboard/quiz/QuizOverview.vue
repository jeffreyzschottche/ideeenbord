<script setup lang="ts">
/*
  Displays a list of all quizzes created by the current brand owner.
  For each quiz, it shows:
  - Title and status
  - Winner info (if available)
  - Option to close the quiz
  - Participants and winner selection (only for open quizzes)

  It loads additional participant data dynamically per quiz.
*/

import { ref, onMounted } from "vue";
import { useBrandOwnerAuthStore } from "~/store/useBrandOwnerAuthStore";
import { useResponseDisplay } from "~/composables/notifications/useResponseDisplay";
import type { Quiz, QuizWithParticipants } from "~/types/quiz";
import { brandOwnerService } from "~/services/api/brand/brandOwnerService";
import { quizService } from "~/services/api/quiz/quizService";

const { triggerByKey } = useResponseDisplay();
const brandId = useBrandOwnerAuthStore().owner?.brand?.id;
const quizzes = ref<QuizWithParticipants[]>([]);

/*
  Load all quizzes for the current brand and attach participant data
  if the quiz is still open. Participants are omitted for closed quizzes.
*/
async function loadQuizzes() {
  if (!brandId) return;
  try {
    const baseQuizzes = await quizService.getQuizzes(brandId);
    const detailed: QuizWithParticipants[] = await Promise.all(
      baseQuizzes.map(async (quiz: Quiz) => {
        const participants =
          quiz.status === "open"
            ? await quizService.getParticipants(quiz.id).catch(() => [])
            : [];
        return { ...quiz, participants };
      })
    );

    quizzes.value = detailed;
  } catch (err) {
    triggerByKey("quiz-load-failed");
  }
}

onMounted(loadQuizzes);

/*
  Closes a quiz by ID and refreshes the list after success.
*/
async function closeQuiz(quizId: number) {
  try {
    await quizService.closeQuiz(quizId);
    triggerByKey("quiz-closed");
    await loadQuizzes();
  } catch (err) {
    triggerByKey("quiz-close-failed");
  }
}

/*
  Selects a winner for a quiz and refreshes the list after success.
*/
async function selectWinner(quizId: number, userId: number) {
  try {
    await quizService.selectWinner(quizId, userId);
    triggerByKey("quiz-winner-selected");
    await loadQuizzes();
  } catch (err: any) {
    triggerByKey("quiz-winner-failed");
  }
}
</script>
<template>
  <div class="bg-white rounded shadow p-6">
    <h2 class="text-xl font-bold mb-4">Jouw Quizzen</h2>

    <div v-if="quizzes.length === 0">Nog geen quizzen aangemaakt.</div>

    <div v-for="quiz in quizzes" :key="quiz.id" class="border p-4 mb-6 rounded">
      <h3 class="text-lg font-semibold">{{ quiz.title }}</h3>
      <p>
        Status: <strong>{{ quiz.status }}</strong>
      </p>
      <p v-if="quiz.winner_id">Winnaar ID: {{ quiz.winner_id }}</p>

      <!-- Button to close the quiz -->
      <button
        v-if="quiz.status === 'open'"
        @click="closeQuiz(quiz.id)"
        class="bg-yellow-500 text-white px-3 py-1 rounded mt-2"
      >
        Sluit quiz
      </button>

      <!-- Participants + winner selection -->
      <div v-if="quiz.status === 'open'" class="mt-4">
        <h4 class="text-md font-bold mb-2">Deelnemers</h4>
        <div v-if="quiz.participants.length === 0">
          <p>Geen deelnemers gevonden.</p>
        </div>
        <ul v-else>
          <li
            v-for="participant in quiz.participants"
            :key="participant.user_id"
            class="mb-2 p-2 border rounded flex justify-between items-center"
          >
            <span>Gebruiker : {{ participant.name }}</span>
            <button
              @click="selectWinner(quiz.id, participant.user_id)"
              class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600"
            >
              Selecteer als winnaar
            </button>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>
