<script setup lang="ts">
import { onMounted, ref, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import Inbox from "~/components/user/notifications/Inbox.vue";
import UserIdeasPosted from "~/components/user/ideas/UserIdeasPosted.vue";
import UserProfileEdit from "~/components/user/profile/UserProfileEdit.vue";
import UserRatingsInsights from "~/components/user/ideas/UserRatingsInsights.vue";
import UserQuizzes from "~/components/user/quiz/UserQuizzes.vue";
import { useUserAuthStore } from "~/store/useUserAuthStore";

const auth = useUserAuthStore();
const route = useRoute();
const router = useRouter();

const showPage = ref(false);
const routeUsername = route.params.slug;

definePageMeta({
  middleware: "user",
});

onMounted(async () => {
  if (!auth.user || !auth.token) await auth.initAuth();

  const currentUsername = auth.user?.username;
  if (!auth.token || !currentUsername || currentUsername !== routeUsername) {
    return router.push("/login");
  }
  showPage.value = true;
});

/* Tab Logica */
const tabs = [
  { key: "profile", label: "Profiel" },
  { key: "ideas", label: "Ideeën" },
  { key: "ratings", label: "Ratings" },
  { key: "quizzes", label: "Quizzes" },
  { key: "inbox", label: "Inbox" },
];
const activeTab = ref("profile");
const isActive = (key: string) => activeTab.value === key;
</script>

<template>
  <div v-if="showPage" class="container mx-auto py-12 px-6 font-default">
    <h1 class="text-3xl font-bold mb-6">Welkom, {{ auth.user.name }} 👋</h1>

    <div class="flex flex-col md:flex-row gap-8">
      <!-- LINKS: tabs -->
      <aside class="md:w-1/4 border p-4 rounded shadow h-fit">
        <h2 class="text-xl font-semibold mb-4">Mijn dashboard</h2>
        <ul class="space-y-2">
          <li
            v-for="tab in tabs"
            :key="tab.key"
            @click="activeTab = tab.key"
            :class="[
              'cursor-pointer px-4 py-2 rounded',
              isActive(tab.key)
                ? 'bg-[var(--color-brand)] text-white'
                : 'bg-gray-100 hover:bg-gray-200 text-gray-700',
            ]"
          >
            {{ tab.label }}
          </li>
        </ul>
      </aside>

      <!-- RECHTS: inhoud -->
      <section class="md:w-3/4 min-h-[500px] transition-all duration-300">
        <UserProfileEdit v-if="isActive('profile')" />
        <UserIdeasPosted v-if="isActive('ideas')" />
        <UserRatingsInsights v-if="isActive('ratings')" />
        <UserQuizzes v-if="isActive('quizzes')" />
        <Inbox v-if="isActive('inbox')" />
      </section>
    </div>
  </div>
</template>
