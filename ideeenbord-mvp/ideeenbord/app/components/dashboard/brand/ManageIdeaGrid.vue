<script setup lang="ts">
import { onMounted } from "vue";
import { useManageIdeas } from "~/composables/ideas/useManageIdeas";
import type { Idea } from "~/types/idea";

// Extend idea type to include local-only field for UI selection
type EditableIdea = Idea & { newStatus?: string };

// Receives the brand ID to manage ideas for
const props = defineProps<{ brandId: number }>();

// Load state and actions from custom composable
const { ideas, fetchIdeas, updateIdeaStatus, pinIdea, unpinIdea, deleteIdea } =
  useManageIdeas(props.brandId);

// Load ideas on component mount
onMounted(fetchIdeas);

async function deleteIdeaAction(idea: Idea) {
  if (confirm("Weet je zeker dat je dit idee wilt verwijderen?")) {
    await deleteIdea(idea.id);
  }
}

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
  <div class="block-spacer">
    <h2 class="title-md">Beheer Ideeën</h2>

    <div v-if="ideas.length === 0">
      <p class="muted-text">Er zijn nog geen ideeën.</p>
    </div>

    <div
      v-for="idea in ideas"
      :key="idea.id"
      class="card-compact"
      style="margin-bottom: 1rem"
    >
      <h3 class="title-md" style="margin-bottom: 0.25rem">{{ idea.title }}</h3>
      <p class="muted-text" style="margin-bottom: 0.5rem">
        {{ idea.description }}
      </p>

      <div class="muted-text" style="margin-bottom: 0.25rem">
        <strong>Huidige status:</strong> {{ idea.status || "Nog geen status" }}
      </div>

      <div class="muted-text" style="margin-bottom: 0.5rem">
        <strong>Is vastgezet:</strong> {{ idea.is_pinned ? "Ja" : "Nee" }}
      </div>

      <select
        v-model="idea.newStatus"
        class="select-input"
        style="margin-bottom: 0.5rem"
      >
        <option disabled value="">Kies nieuwe status</option>
        <option value="rejected">Afgekeurd</option>
        <option value="in_progress">In behandeling genomen</option>
        <option value="completed">Voltooid</option>
        <option value="pending">Tijdelijk gepauzeerd</option>
      </select>

      <div style="display: flex; gap: 0.5rem; flex-wrap: wrap">
        <button @click="updateStatus(idea)" class="btn btn--blue btn--sm">
          Status opslaan
        </button>

        <button
          v-if="!idea.is_pinned"
          @click="pinIdeaAction(idea)"
          class="btn btn--success btn--sm"
        >
          Zet idee vast
        </button>

        <button
          v-else
          @click="unpinIdeaAction(idea)"
          class="btn btn--warning btn--sm"
        >
          Maak idee los
        </button>

        <button @click="deleteIdeaAction(idea)" class="btn btn--danger btn--sm">
          Verwijder
        </button>
      </div>
    </div>
  </div>
</template>
