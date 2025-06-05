<script setup lang="ts">
/*
  Fetches and displays current and past quiz participations for a user.
  The username is extracted from the route.
  Shows active and closed quiz entries in two separate lists.
*/

import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import { apiFetch } from "~/composables/useApi";

// Extract username (slug) from the route
const route = useRoute();
const username = route.params.slug as string;

// Reactive state for quiz lists and UI states
const currentQuizzes = ref([]);
const pastQuizzes = ref([]);
const loading = ref(true);
const error = ref<string | null>(null);

// Fetch quiz participation data on mount
onMounted(async () => {
  try {
    const data = await apiFetch<{ current: any[]; past: any[] }>(
      `/users/${username}/quiz-submissions`
    );
    currentQuizzes.value = data.current;
    pastQuizzes.value = data.past;
  } catch (e: any) {
    error.value = e.message || "Quizdata ophalen mislukt.";
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <div>
    <h2 class="text-xl font-bold mb-2">📢 Huidige Winacties</h2>

    <!-- List of ongoing quizzes -->
    <ul v-if="currentQuizzes.length">
      <li v-for="quiz in currentQuizzes" :key="quiz.id">
        ✅ <strong>{{ quiz.title }}</strong> bij
        <NuxtLink :to="`/brands/${quiz.brand.title}`">
          {{ quiz.brand.title }}
        </NuxtLink>
      </li>
    </ul>
    <p v-else>Geen actieve winacties op dit moment.</p>

    <h2 class="text-xl font-bold mt-6 mb-2">⛔ Afgelopen Winacties</h2>

    <!-- List of past (closed) quizzes -->
    <ul v-if="pastQuizzes.length">
      <li v-for="quiz in pastQuizzes" :key="quiz.id">
        🕑 <strong>{{ quiz.title }}</strong> bij
        <NuxtLink :to="`/brands/${quiz.brand.title}`">
          {{ quiz.brand.title }}
        </NuxtLink>
        (gesloten)
      </li>
    </ul>
    <p v-else>Geen eerdere deelnames.</p>

    <!-- Loading and error states -->
    <p v-if="loading">Laden...</p>
    <p v-if="error">{{ error }}</p>
  </div>
</template>
