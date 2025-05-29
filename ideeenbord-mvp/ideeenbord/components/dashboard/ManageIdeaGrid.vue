<template>
  <div>
    <h2 class="text-2xl font-bold mb-4">Beheer Ideeën</h2>

    <div v-if="ideas.length === 0">
      <p>Er zijn nog geen ideeën.</p>
    </div>

    <div v-for="idea in ideas" :key="idea.id" class="border p-4 mb-4 rounded">
      <h3 class="text-xl font-semibold">{{ idea.title }}</h3>
      <p class="text-gray-600 mb-2">{{ idea.description }}</p>

      <div class="mb-2">
        <strong>Huidige status:</strong> {{ idea.status || "Nog geen status" }}
      </div>

      <div class="mb-2">
        <strong>Is vastgezet:</strong> {{ idea.is_pinned ? "Ja" : "Nee" }}
      </div>

      <select v-model="idea.newStatus" class="border p-2 rounded mb-2">
        <option disabled value="">Kies nieuwe status</option>
        <option value="rejected">Afgekeurd</option>
        <option value="in_progress">In behandeling genomen</option>
        <option value="completed">Voltooid</option>
        <option value="pending">Tijdelijk gepauzeerd</option>
      </select>

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

<script setup lang="ts">
import { onMounted } from "vue";
import { useManageIdeas } from "~/composables/useManageIdeas";
import type { Idea } from "~/types/idea";
type EditableIdea = Idea & { newStatus?: string };

const props = defineProps<{ brandId: number }>();

const { ideas, fetchIdeas, updateIdeaStatus, pinIdea, unpinIdea } =
  useManageIdeas(props.brandId);

onMounted(fetchIdeas);

async function updateStatus(idea: EditableIdea) {
  if (!idea.newStatus) return;
  await updateIdeaStatus(idea.id, idea.newStatus);
}

async function pinIdeaAction(idea: Idea) {
  await pinIdea(idea.id);
}

async function unpinIdeaAction(idea: Idea) {
  await unpinIdea(idea.id);
}
</script>
