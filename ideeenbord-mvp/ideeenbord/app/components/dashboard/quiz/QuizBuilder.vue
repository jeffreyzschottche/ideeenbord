<script setup lang="ts">
/*
  Logic for dynamically building and submitting a quiz.
  Includes support for multiple questions and single-correct-answer enforcement per question.
*/

import { ref } from "vue";
import { useBrandOwnerAuthStore } from "~/store/useBrandOwnerAuthStore";
import { useResponseDisplay } from "~/composables/notifications/useResponseDisplay";
import { useQuizBuilder } from "~/composables/quiz/useQuizBuilder";
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
    /* ── HIER: check op profanity ────────────── */
    const rawErrors = err?.validationErrors;
    if (rawErrors) {
      const all = Object.values(rawErrors).flat();
      if (all.includes("profanity-detected")) {
        triggerByKey("profanity-detected");
        return;
      }
    }
    triggerByKey("quiz-create-failed");
  }
}
</script>
<template>
  <div class="register-card" style="margin: 0 0 2.5rem 0">
    <h2 class="title-lg">Nieuwe Quiz Aanmaken</h2>

    <input
      v-model="title"
      placeholder="Titel van de quiz"
      class="input"
      style="margin-bottom: 1rem"
    />

    <textarea
      v-model="description"
      placeholder="Korte beschrijving van de quiz"
      class="textarea-input"
      rows="3"
      style="margin-bottom: 1rem"
    ></textarea>

    <input
      v-model="prize"
      placeholder="Wat kunnen deelnemers winnen?"
      class="input"
      style="margin-bottom: 1rem"
    />

    <div
      v-for="(question, qIndex) in questions"
      :key="qIndex"
      class="card-compact"
      style="margin-bottom: 1.25rem"
    >
      <input
        v-model="question.title"
        placeholder="Vraagtekst"
        class="input"
        style="margin-bottom: 0.5rem"
      />

      <div
        v-for="(answer, aIndex) in question.answers"
        :key="aIndex"
        class="answer-row"
      >
        <input
          v-model="answer.text"
          placeholder="Antwoordoptie"
          class="input"
        />
        <input
          type="radio"
          :name="'correct-' + qIndex"
          :checked="answer.correct"
          @change="setCorrectAnswer(qIndex, aIndex)"
        />
        <span class="muted-text" style="font-size: 0.85rem">Correct</span>
      </div>

      <button @click="addAnswer(qIndex)" class="btn-link">
        + Antwoord toevoegen
      </button>
    </div>

    <button @click="addQuestion" class="btn-link" style="margin-bottom: 1rem">
      + Vraag toevoegen
    </button>

    <button @click="submitQuiz" class="btn">Quiz Opslaan</button>
  </div>
</template>
