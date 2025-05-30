<template>
  <div class="bg-white rounded shadow p-6 mb-10">
    <h2 class="text-2xl font-bold mb-4">Nieuwe Quiz Aanmaken</h2>

    <input
      v-model="title"
      placeholder="Titel van de quiz"
      class="w-full border p-2 mb-4 rounded"
    />

    <textarea
      v-model="description"
      placeholder="Korte beschrijving van de quiz"
      class="w-full border p-2 mb-4 rounded"
      rows="3"
    ></textarea>

    <input
      v-model="prize"
      placeholder="Wat kunnen deelnemers winnen?"
      class="w-full border p-2 mb-4 rounded"
    />

    <div
      v-for="(question, qIndex) in questions"
      :key="qIndex"
      class="mb-6 border p-4 rounded"
    >
      <input
        v-model="question.title"
        placeholder="Vraagtekst"
        class="w-full border p-2 mb-2 rounded"
      />

      <div
        v-for="(answer, aIndex) in question.answers"
        :key="aIndex"
        class="flex items-center gap-2 mb-2"
      >
        <input
          v-model="answer.text"
          placeholder="Antwoordoptie"
          class="flex-1 border p-2 rounded"
        />
        <input
          type="radio"
          :name="'correct-' + qIndex"
          :checked="answer.correct"
          @change="setCorrectAnswer(qIndex, aIndex)"
        />
        <span class="text-sm text-gray-500">Correct</span>
      </div>

      <button @click="addAnswer(qIndex)" class="text-blue-600 text-sm">
        + Antwoord toevoegen
      </button>
    </div>

    <button @click="addQuestion" class="text-blue-700 mb-4">
      + Vraag toevoegen
    </button>
    <button
      @click="submitQuiz"
      class="bg-blue-500 text-white px-4 py-2 rounded"
    >
      Quiz Opslaan
    </button>
  </div>
</template>

<script setup lang="ts">
import { ref } from "vue";
import { useBrandOwnerAuthStore } from "~/store/useBrandOwnerAuthStore";
import { useResponseDisplay } from "~/composables/useResponseDisplay";
import { useQuizBuilder } from "~/composables/useQuizBuilder";
import type { NewQuizForm } from "~/types/quiz";

const { trigger } = useResponseDisplay();
const { createQuiz } = useQuizBuilder();
const brandOwnerAuth = useBrandOwnerAuthStore();

const title = ref("");
const description = ref("");
const prize = ref("");

const questions = ref([
  {
    title: "",
    answers: [
      { text: "", correct: false },
      { text: "", correct: false },
    ],
  },
]);

function addQuestion() {
  questions.value.push({ title: "", answers: [{ text: "", correct: false }] });
}

function addAnswer(qIndex: number) {
  questions.value[qIndex].answers.push({ text: "", correct: false });
}

function setCorrectAnswer(qIndex: number, aIndex: number) {
  questions.value[qIndex].answers.forEach((a, i) => {
    a.correct = i === aIndex;
  });
}

async function submitQuiz() {
  try {
    const brandId = brandOwnerAuth.owner?.brand?.id;
    if (!brandId) return;

    const quizData: NewQuizForm = {
      brand_id: brandId,
      title: title.value,
      description: description.value,
      prize: prize.value,
      questions: questions.value,
    };

    await createQuiz(quizData);

    trigger("Quiz succesvol aangemaakt!", "success");

    title.value = "";
    description.value = "";
    prize.value = "";
    questions.value = [
      {
        title: "",
        answers: [
          { text: "", correct: false },
          { text: "", correct: false },
        ],
      },
    ];
  } catch (err: any) {
    trigger("Fout bij opslaan quiz: " + err.message, "error");
  }
}
</script>

<style scoped>
input[type="radio"] {
  transform: scale(1.2);
}
</style>
