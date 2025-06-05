<script setup lang="ts">
import { onMounted } from "vue";
import { useManageIdeas } from "~/composables/useManageIdeas";
import type { Idea } from "~/types/idea";

// Extend idea type to include local-only field for UI selection
type EditableIdea = Idea & { newStatus?: string };

// Receives the brand ID to manage ideas for
const props = defineProps<{ brandId: number }>();

// Load state and actions from custom composable
const { ideas, fetchIdeas, updateIdeaStatus, pinIdea, unpinIdea } =
  useManageIdeas(props.brandId);

// Load ideas on component mount
onMounted(fetchIdeas);

/*
  Save updated status for an idea.
  Only executes if a new status is selected.
*/
async function updateStatus(idea: EditableIdea) {
  if (!idea.newStatus) return;
  await updateIdeaStatus(idea.id, idea.newStatus);
}

// Pin the idea to give it visual priority
async function pinIdeaAction(idea: Idea) {
  await pinIdea(idea.id);
}

// Unpin the idea to remove visual priority
async function unpinIdeaAction(idea: Idea) {
  await unpinIdea(idea.id);
}
</script>
<template>
  <div>
    <h2 class="text-2xl font-bold mb-4">Beheer Ideeën</h2>

    <!-- Show message when no ideas are available -->
    <div v-if="ideas.length === 0">
      <p>Er zijn nog geen ideeën.</p>
    </div>

    <!-- Loop through and display each idea block -->
    <div v-for="idea in ideas" :key="idea.id" class="border p-4 mb-4 rounded">
      <h3 class="text-xl font-semibold">{{ idea.title }}</h3>
      <p class="text-gray-600 mb-2">{{ idea.description }}</p>

      <div class="mb-2">
        <strong>Huidige status:</strong> {{ idea.status || "Nog geen status" }}
      </div>

      <div class="mb-2">
        <strong>Is vastgezet:</strong> {{ idea.is_pinned ? "Ja" : "Nee" }}
      </div>

      <!-- Dropdown to select a new status for the idea -->
      <select v-model="idea.newStatus" class="border p-2 rounded mb-2">
        <option disabled value="">Kies nieuwe status</option>
        <option value="rejected">Afgekeurd</option>
        <option value="in_progress">In behandeling genomen</option>
        <option value="completed">Voltooid</option>
        <option value="pending">Tijdelijk gepauzeerd</option>
      </select>

      <!-- Actions: save status, pin or unpin -->
      <div class="flex gap-2">
        <button
          @click="updateStatus(idea)"
          class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded"
        >
          Status opslaan
        </button>

        <button
          v-if="!idea.is_pinned"
          @click="pinIdeaAction(idea)"
          class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded"
        >
          Zet idee vast
        </button>

        <button
          v-else
          @click="unpinIdeaAction(idea)"
          class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded"
        >
          Maak idee los
        </button>
      </div>
    </div>
  </div>
</template>
