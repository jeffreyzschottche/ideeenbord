<template>
  <!-- 
    UI for creating a new quiz.
    Allows setting a title, description, prize, and dynamically adding questions and answers.
    One answer per question can be marked as correct.
  -->
  <div class="bg-white rounded shadow p-6 mb-10">
    <h2 class="text-2xl font-bold mb-4">Nieuwe Quiz Aanmaken</h2>

    <!-- Quiz title input -->
    <input
      v-model="title"
      placeholder="Titel van de quiz"
      class="w-full border p-2 mb-4 rounded"
    />

    <!-- Quiz description input -->
    <textarea
      v-model="description"
      placeholder="Korte beschrijving van de quiz"
      class="w-full border p-2 mb-4 rounded"
      rows="3"
    ></textarea>

    <!-- Prize input -->
    <input
      v-model="prize"
      placeholder="Wat kunnen deelnemers winnen?"
      class="w-full border p-2 mb-4 rounded"
    />

    <!-- Questions and their answers -->
    <div
      v-for="(question, qIndex) in questions"
      :key="qIndex"
      class="mb-6 border p-4 rounded"
    >
      <!-- Question text input -->
      <input
        v-model="question.title"
        placeholder="Vraagtekst"
        class="w-full border p-2 mb-2 rounded"
      />

      <!-- List of possible answers for this question -->
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
        <!-- Radio button to mark this answer as correct -->
        <input
          type="radio"
          :name="'correct-' + qIndex"
          :checked="answer.correct"
          @change="setCorrectAnswer(qIndex, aIndex)"
        />
        <span class="text-sm text-gray-500">Correct</span>
      </div>

      <!-- Add another answer to the current question -->
      <button @click="addAnswer(qIndex)" class="text-blue-600 text-sm">
        + Antwoord toevoegen
      </button>
    </div>

    <!-- Add a new question to the quiz -->
    <button @click="addQuestion" class="text-blue-700 mb-4">
      + Vraag toevoegen
    </button>

    <!-- Submit the entire quiz -->
    <button
      @click="submitQuiz"
      class="bg-blue-500 text-white px-4 py-2 rounded"
    >
      Quiz Opslaan
    </button>
  </div>
</template>

<script setup lang="ts">
/*
  Logic for dynamically building and submitting a quiz.
  Includes support for multiple questions and single-correct-answer enforcement per question.
*/

import { ref } from "vue";
import { useBrandOwnerAuthStore } from "~/store/useBrandOwnerAuthStore";
import { useResponseDisplay } from "~/composables/useResponseDisplay";
import { useQuizBuilder } from "~/composables/useQuizBuilder";
import type { NewQuizForm } from "~/types/quiz";

const { triggerByKey } = useResponseDisplay();
const { createQuiz } = useQuizBuilder();
const brandOwnerAuth = useBrandOwnerAuthStore();

const title = ref("");
const description = ref("");
const prize = ref("");

// Initial state with one question and two empty answers
const questions = ref([
  {
    title: "",
    answers: [
      { text: "", correct: false },
      { text: "", correct: false },
    ],
  },
]);

// Add a new empty question block
function addQuestion() {
  questions.value.push({ title: "", answers: [{ text: "", correct: false }] });
}

// Add a new empty answer option to a given question
function addAnswer(qIndex: number) {
  questions.value[qIndex].answers.push({ text: "", correct: false });
}

// Mark the selected answer as correct, unchecking all others
function setCorrectAnswer(qIndex: number, aIndex: number) {
  questions.value[qIndex].answers.forEach((a, i) => {
    a.correct = i === aIndex;
  });
}

/*
  Prepare the quiz payload and send it to the backend.
  Resets the form after successful submission.
*/
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
    triggerByKey("quiz-created");

    // Reset form state after creation
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
    triggerByKey("quiz-create-failed");
  }
}
</script>
