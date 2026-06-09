<script setup lang="ts">
/*
  Fetches and displays all ideas posted by a specific user.
  The username is taken from the route (slug).
  Shows loading and error states, and triggers a UI message on failure.
*/

import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import { apiFetch } from "~/composables/adapter/useApi";
import { useResponseDisplay } from "~/composables/notifications/useResponseDisplay";
import { ideaService } from "~/services/api/ideas/ideaService";

const route = useRoute();
const username = route.params.slug as string;
const { triggerByKey } = useResponseDisplay();

// Reactive state for fetched ideas and status
const ideas = ref<any[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);

// Fetch posted ideas when the component is mounted
onMounted(async () => {
  try {
    const ideaData: any = await apiFetch(`/users/${username}/ideas`);
    ideas.value = ideaData;
  } catch (e: any) {
    triggerByKey("ideas-fetch-failed");
  } finally {
    loading.value = false;
  }
});

async function deleteIdea(id: number) {
  if (!confirm("Weet je zeker dat je dit idee wilt verwijderen?")) return;

  try {
    await ideaService.deleteIdea(id);
    ideas.value = ideas.value.filter((i) => i.id !== id); // lijst bijwerken
    triggerByKey("idea-deleted");
  } catch (e) {
    triggerByKey("idea-delete-failed");
  }
}
</script>

<template>
  <div class="mt-8">
    <h2 class="text-xl font-bold mb-2">💡 Ideeën die je hebt gepost</h2>

    <!-- Loading state -->
    <p v-if="loading">Laden...</p>

    <!-- Error message -->
    <p v-if="error">{{ error }}</p>

    <!-- Display posted ideas -->
    <ul v-if="ideas.length">
      <li v-for="idea in ideas" :key="idea.id" class="mb-2">
        <NuxtLink
          :to="`/brands/${idea.brand?.slug}?idea=${idea.id}`"
          class="font-bold text-lg text-blue-800 hover:underline"
        >
          {{ idea.title }}
        </NuxtLink>
        <br />
        <i>Status : {{ idea.status }}</i>
        <i>Likes : {{ idea.likes }}</i>
        <i>Dislikes : {{ idea.dislikes }}</i>
        <i>
          Bij merk:
          <NuxtLink
            :to="`/brands/${idea.brand?.slug}`"
            class="text-blue-600 underline"
          >
            {{ idea.brand?.slug }}
          </NuxtLink>
        </i>
        <br />
        <span class="text-sm text-gray-600">{{ idea.description }}</span>
        <button
          class="block text-red-600 text-sm mt-1 hover:underline"
          @click="deleteIdea(idea.id)"
        >
          Verwijder dit idee
        </button>
      </li>
    </ul>

    <!-- Fallback when no ideas are found -->
    <p v-else-if="!loading">Je hebt nog geen ideeën gepost.</p>
  </div>
</template>
