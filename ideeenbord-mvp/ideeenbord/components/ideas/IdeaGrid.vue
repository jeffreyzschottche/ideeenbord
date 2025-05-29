<template>
  <div>
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

    <div v-else>
      <p>Login om je idee te plaatsen!</p>
    </div>

    <div v-for="idea in sortedIdeas" :key="idea.id">
      <IdeaCard :idea="idea" @like="likeIdea" @dislike="dislikeIdea" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";
import { useAuthStore } from "~/store/auth";
import { useIdeas } from "~/composables/useIdeas";
import IdeaCard from "~/components/ideas/IdeaCard.vue";
import type { Idea } from "~/types/idea";

const sortedIdeas = computed<readonly Idea[]>(() => {
  return [...ideas.value].sort((a, b) => {
    if (a.is_pinned && !b.is_pinned) return -1;
    if (!a.is_pinned && b.is_pinned) return 1;
    return new Date(b.created_at).getTime() - new Date(a.created_at).getTime();
  });
});

const props = defineProps<{ brandId: number }>();
const auth = useAuthStore();
const { ideas, fetchIdeas, submitIdea, likeIdea, dislikeIdea } = useIdeas(
  props.brandId
);

const title = ref("");
const description = ref("");

onMounted(() => {
  fetchIdeas();
});

async function submitNewIdea() {
  if (!title.value) return;
  await submitIdea(title.value, description.value);
  title.value = "";
  description.value = "";
}
</script>
