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
  <div class="register-card">
    <h2 class="title-md">Jouw Quizzen</h2>

    <div v-if="quizzes.length === 0" class="muted-text">
      Nog geen quizzen aangemaakt.
    </div>

    <div
      v-for="quiz in quizzes"
      :key="quiz.id"
      class="card-compact"
      style="margin-bottom: 1.5rem"
    >
      <h3 class="title-md" style="margin-bottom: 0.25rem">{{ quiz.title }}</h3>
      <p class="muted-text" style="margin-bottom: 0.25rem">
        Status: <strong>{{ quiz.status }}</strong>
      </p>
      <p v-if="quiz.winner_id" class="muted-text" style="margin-bottom: 0.5rem">
        Winnaar ID: {{ quiz.winner_id }}
      </p>

      <button
        v-if="quiz.status === 'open'"
        @click="closeQuiz(quiz.id)"
        class="btn btn--warning btn--sm"
        style="margin-top: 0.25rem"
      >
        Sluit quiz
      </button>

      <div
        v-if="quiz.status === 'open'"
        class="block-spacer"
        style="margin-top: 1rem"
      >
        <h4 class="title-md" style="margin-bottom: 0.5rem">Deelnemers</h4>
        <div v-if="quiz.participants.length === 0" class="muted-text">
          Geen deelnemers gevonden.
        </div>
        <ul v-else class="list">
          <li
            v-for="participant in quiz.participants"
            :key="participant.user_id"
            class="list-item"
            style="margin-bottom: 0.5rem"
          >
            <span>Gebruiker : {{ participant.name }}</span>
            <button
              @click="selectWinner(quiz.id, participant.user_id)"
              class="btn btn--success btn--sm"
            >
              Selecteer als winnaar
            </button>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>
