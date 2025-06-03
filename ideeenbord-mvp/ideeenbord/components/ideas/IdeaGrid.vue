<script setup lang="ts">
/*
  Displays a list of user-submitted ideas for a brand.
  Logged-in users can submit new ideas.
  Ideas are sorted with pinned items on top and newest first.
  Each idea can be liked or disliked.
*/

import { ref, onMounted } from "vue";
import { useUserAuthStore } from "~/store/useUserAuthStore";
import { useIdeas } from "~/composables/useIdeas";
import IdeaCard from "~/components/ideas/IdeaCard.vue";
import type { Idea } from "~/types/idea";

// Brand ID is passed as prop to load relevant ideas
const props = defineProps<{ brandId: number }>();

// User authentication store to check login state
const auth = useUserAuthStore();

// Reactive form state for submitting a new idea
const title = ref("");
const description = ref("");

// Load all relevant idea functions for the given brand
const { ideas, fetchIdeas, submitIdea, likeIdea, dislikeIdea } = useIdeas(
  props.brandId
);

/*
  Sorted view of ideas:
  - Pinned ideas appear first
  - Within each group, newest ideas come first
*/
const sortedIdeas = computed<readonly Idea[]>(() => {
  return [...ideas.value].sort((a, b) => {
    if (a.is_pinned && !b.is_pinned) return -1;
    if (!a.is_pinned && b.is_pinned) return 1;
    return new Date(b.created_at).getTime() - new Date(a.created_at).getTime();
  });
});

// Fetch ideas on mount
onMounted(() => {
  fetchIdeas();
});

/*
  Submit a new idea using the form data.
  Resets the form after submission.
*/
async function submitNewIdea() {
  if (!title.value) return;
  await submitIdea(title.value, description.value);
  title.value = "";
  description.value = "";
}
</script>

<template>
  <div>
    <!-- Idea submission form (only visible when logged in) -->
    <form @submit.prevent="submitNewIdea" v-if="auth.token" class="mb-6">
      <input
        v-model="title"
        placeholder="Titel van je idee"
        class="block mb-2 p-2 border"
        required
      />
      <textarea
        v-model="description"
        placeholder="Beschrijving"
        class="block mb-2 p-2 border"
      ></textarea>
      <button type="submit" class="bg-blue-500 text-white px-4 py-2">
        Plaats Idee
      </button>
    </form>

    <!-- Fallback message for unauthenticated users -->
    <div v-else>
      <p>Login om je idee te plaatsen!</p>
    </div>

    <!-- List of idea cards, sorted by pinned status and creation date -->
    <div v-for="idea in sortedIdeas" :key="idea.id">
      <IdeaCard :idea="idea" @like="likeIdea" @dislike="dislikeIdea" />
    </div>
  </div>
</template>
