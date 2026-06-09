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
  <div v-if="showPage" class="max-w-6xl mx-auto py-8 px-4 font-default">
    <!-- Header card -->
    <div
      class="relative overflow-hidden rounded-3xl bg-[var(--color-nav)] text-white p-6 md:p-8 flex items-center gap-5"
    >
      <div class="absolute -top-16 -left-16 w-56 h-56 rounded-full bg-[var(--color-brand)]/20 blur-3xl"></div>
      <div
        class="relative w-20 h-20 rounded-full bg-[var(--color-brand)] flex items-center justify-center text-2xl font-extrabold shrink-0"
      >
        {{ (auth.user.name || "?").charAt(0).toUpperCase() }}
      </div>
      <div class="relative">
        <p class="text-sm text-gray-300">Jouw profiel</p>
        <h1 class="text-2xl md:text-3xl font-extrabold leading-tight">
          {{ auth.user.name }}
        </h1>
        <p class="text-gray-300 mt-1">@{{ auth.user.username }}</p>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 mt-6">
      <!-- Tabs -->
      <aside class="md:col-span-4 lg:col-span-3">
        <nav class="card p-2 md:sticky md:top-24">
          <ul class="flex md:flex-col gap-1 overflow-x-auto">
            <li v-for="tab in tabs" :key="tab.key" class="shrink-0 md:shrink">
              <button
                @click="activeTab = tab.key"
                :class="[
                  'w-full text-left px-4 py-3 rounded-xl font-semibold text-sm transition-colors whitespace-nowrap',
                  isActive(tab.key)
                    ? 'bg-[var(--color-nav)] text-white'
                    : 'text-gray-600 hover:bg-[var(--color-bg)]',
                ]"
              >
                {{ tab.label }}
              </button>
            </li>
          </ul>
        </nav>
      </aside>

      <!-- Inhoud -->
      <section class="md:col-span-8 lg:col-span-9 card p-5 md:p-7 min-h-[500px]">
        <UserProfileEdit v-if="isActive('profile')" />
        <UserIdeasPosted v-if="isActive('ideas')" />
        <UserRatingsInsights v-if="isActive('ratings')" />
        <UserQuizzes v-if="isActive('quizzes')" />
        <Inbox v-if="isActive('inbox')" />
      </section>
    </div>
  </div>
</template>
