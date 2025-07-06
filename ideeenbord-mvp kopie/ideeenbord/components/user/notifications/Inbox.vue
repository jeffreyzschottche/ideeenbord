<script setup lang="ts">
/*
  Displays the user's inbox containing notifications about quizzes and ideas.
  Ensures the authenticated user is loaded before showing content.
*/

import { useUserAuthStore } from "~/store/useUserAuthStore";
import { onMounted, ref } from "vue";
import QuizNotifier from "~/components/user/notifications/QuizNotifier.vue";
import IdeaNotifier from "~/components/user/notifications/IdeaNotifier.vue";

// Auth store to access current user and token
const auth = useUserAuthStore();
const loaded = ref(false);

// Wait until authentication is initialized before rendering inbox
onMounted(async () => {
  if (!auth.user || !auth.token) {
    await auth.initAuth();
  }
  loaded.value = true;
});
</script>

<template>
  <!-- Inbox view with quiz and idea notifications -->
  <div class="mt-8" v-if="loaded && auth.user">
    <h2 class="text-xl font-bold mb-4">📥 Inbox</h2>
    <QuizNotifier />
    <IdeaNotifier />
  </div>
</template>
