<script setup lang="ts">
import { useUserAuthStore } from "~/store/useUserAuthStore";
import { onMounted, ref } from "vue";
import QuizNotifier from "~/components/user/QuizNotifier.vue";
import IdeaNotifier from "~/components/user/IdeaNotifier.vue";

const auth = useUserAuthStore();
const loaded = ref(false);

onMounted(async () => {
  if (!auth.user || !auth.token) {
    await auth.initAuth();
  }
  loaded.value = true;
});
</script>

<template>
  <div class="mt-8" v-if="loaded && auth.user">
    <h2 class="text-xl font-bold mb-4">📥 Inbox</h2>
    <QuizNotifier />
    <IdeaNotifier />
  </div>
</template>
