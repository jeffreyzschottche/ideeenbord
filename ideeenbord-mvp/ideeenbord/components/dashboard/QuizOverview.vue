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

      <button
        v-if="quiz.status === 'open'"
        @click="closeQuiz(quiz.id)"
        class="bg-yellow-500 text-white px-3 py-1 rounded mt-2"
      >
        Sluit quiz
      </button>

      <!-- Deelnemers + winnaarselectie -->
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
            <span>User: {{ participant.name }}</span>
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

<script setup lang="ts">
import { ref, onMounted } from "vue";
import { brandOwnerApiFetch } from "~/composables/useBrandOwnerApi";
import { useBrandOwnerAuthStore } from "~/store/brandOwnerAuth";
import { useResponseDisplay } from "~/composables/useResponseDisplay";
import type { Quiz } from "~/types/quiz";

const { trigger } = useResponseDisplay();
const brandId = useBrandOwnerAuthStore().owner?.brand?.id;
const quizzes = ref<any[]>([]);

async function loadQuizzes() {
  if (!brandId) return;
  try {
    const response = await brandOwnerApiFetch(`/brands/${brandId}/quizzes`);
    // Voor elke quiz ook deelnemers ophalen
    const detailed = await Promise.all(
      response.map(async (quiz: any) => {
        const participants =
          quiz.status === "open"
            ? await brandOwnerApiFetch(
                `/quizzes/${quiz.id}/participants`
              ).catch(() => [])
            : [];
        return { ...quiz, participants };
      })
    );
    quizzes.value = detailed;
  } catch (err) {
    trigger("Fout bij laden quizzen", "error");
  }
}

onMounted(loadQuizzes);

async function closeQuiz(quizId: number) {
  try {
    await brandOwnerApiFetch(`/quizzes/${quizId}/close`, { method: "POST" });
    trigger("Quiz gesloten!", "success");
    await loadQuizzes(); // herladen
  } catch (err) {
    trigger("Quiz sluiten mislukt", "error");
  }
}

async function selectWinner(quizId: number, userId: number) {
  try {
    await brandOwnerApiFetch(`/quizzes/${quizId}/select-winner`, {
      method: "POST",
      body: JSON.stringify({ winner_id: userId }),
    });
    trigger("Winnaar gekozen!", "success");
    await loadQuizzes(); // herladen
  } catch (err: any) {
    trigger("Fout bij kiezen winnaar", "error");
  }
}
</script>
