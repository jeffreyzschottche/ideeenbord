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

function statusColor(s?: string) {
  return (
    {
      pending: "bg-orange-100 text-orange-700",
      rejected: "bg-red-100 text-red-700",
      in_progress: "bg-blue-100 text-blue-700",
      completed: "bg-green-100 text-green-700",
    }[s ?? ""] ?? "bg-gray-100 text-gray-600"
  );
}
function statusLabel(s?: string) {
  return (
    {
      pending: "In afwachting",
      rejected: "Afgekeurd",
      in_progress: "In behandeling",
      completed: "Voltooid",
    }[s ?? ""] ?? "Onbekend"
  );
}
</script>
<template>
  <div class="space-y-4">
    <div
      v-if="ideas.length === 0"
      class="text-center text-gray-400 py-12 border border-dashed border-gray-200 rounded-2xl"
    >
      <p class="text-4xl mb-2">💡</p>
      <p>Er zijn nog geen ideeën voor dit merk.</p>
    </div>

    <div
      v-for="idea in ideas"
      :key="idea.id"
      class="rounded-2xl border bg-white p-5 shadow-[0_4px_16px_rgba(31,41,55,0.05)]"
      :class="idea.is_pinned ? 'border-[var(--color-brand)]' : 'border-gray-100'"
    >
      <!-- Header -->
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
          <h3 class="text-lg font-bold text-[var(--color-nav)]">
            <span v-if="idea.is_pinned" class="mr-1" title="Vastgezet">📌</span>{{ idea.title }}
          </h3>
          <p class="mt-1 text-sm text-gray-500">@{{ idea.user?.username || "anoniem" }}</p>
        </div>
        <span class="shrink-0 text-xs font-semibold px-2.5 py-1 rounded-full" :class="statusColor(idea.status)">
          {{ statusLabel(idea.status) }}
        </span>
      </div>

      <p class="mt-3 text-sm text-gray-600">{{ idea.description }}</p>

      <div class="mt-3 flex items-center gap-4 text-sm text-gray-500">
        <span class="inline-flex items-center gap-1 text-green-600"><i class="fa-solid fa-thumbs-up"></i>{{ idea.likes ?? 0 }}</span>
        <span class="inline-flex items-center gap-1 text-red-500"><i class="fa-solid fa-thumbs-down"></i>{{ idea.dislikes ?? 0 }}</span>
      </div>

      <!-- Acties -->
      <div class="mt-4 pt-4 border-t border-gray-100 flex flex-wrap items-center gap-2">
        <select v-model="idea.newStatus" class="select-input !w-auto !py-2 text-sm flex-1 min-w-[180px]">
          <option disabled value="">Wijzig status…</option>
          <option value="rejected">Afgekeurd</option>
          <option value="in_progress">In behandeling genomen</option>
          <option value="completed">Voltooid</option>
          <option value="pending">Tijdelijk gepauzeerd</option>
        </select>

        <button @click="updateStatus(idea)" class="btn btn--sm">
          <i class="fa-solid fa-check mr-1"></i> Opslaan
        </button>

        <button
          v-if="!idea.is_pinned"
          @click="pinIdeaAction(idea)"
          class="btn--ghost btn btn--sm"
        >
          <i class="fa-solid fa-thumbtack mr-1"></i> Vastzetten
        </button>
        <button
          v-else
          @click="unpinIdeaAction(idea)"
          class="btn--ghost btn btn--sm"
        >
          <i class="fa-solid fa-thumbtack mr-1"></i> Losmaken
        </button>

        <button
          @click="deleteIdeaAction(idea)"
          class="ml-auto text-gray-400 hover:text-red-600 px-2 py-2"
          title="Verwijder"
        >
          <i class="fa-solid fa-trash"></i>
        </button>
      </div>
    </div>
  </div>
</template>
