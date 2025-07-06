<script setup lang="ts">
/*
  This page displays the logged-in user's profile dashboard.
  It includes personal info, posted ideas, rating insights,
  quizzes participated in, and an inbox for notifications.
  Middleware ensures only the correct logged-in user can access this page.
*/

import { onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import Inbox from "~/components/user/notifications/Inbox.vue";
import UserIdeasPosted from "~/components/user/ideas/UserIdeasPosted.vue";
import UserProfileEdit from "~/components/user/profile/UserProfileEdit.vue";
import UserRatingsInsights from "~/components/user/ideas/UserRatingsInsights.vue";
import { useUserAuthStore } from "~/store/useUserAuthStore";
import UserQuizzes from "~/components/user/quiz/UserQuizzes.vue";

const auth = useUserAuthStore();
const route = useRoute();
const router = useRouter();

const showPage = ref(false);

const routeUsername = route.params.slug;

definePageMeta({
  middleware: "user",
});

onMounted(async () => {
  // ✅ Initialize auth on mount if missing
  if (!auth.user || !auth.token) {
    await auth.initAuth();
  }

  const currentUsername = auth.user?.username;

  // ❌ Redirect if not logged in or accessing another user's profile
  if (!auth.token || !currentUsername || currentUsername !== routeUsername) {
    return router.push("/login");
  }
  showPage.value = true;
});
</script>

<template>
  <div v-if="showPage">
    <h1>Welkom, {{ auth.user.name }} 👋</h1>
    <p>Gebruikersnaam: {{ auth.user.username }}</p>
  </div>
  <UserRatingsInsights />
  <UserQuizzes />
  <UserProfileEdit />
  <UserIdeasPosted />
  <Inbox />
</template>
