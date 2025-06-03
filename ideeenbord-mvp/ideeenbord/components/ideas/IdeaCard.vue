<template>
  <div
    :class="[
      'border p-4 rounded shadow mb-4 relative',
      idea.is_pinned ? 'border-yellow-400' : 'border-gray-300',
    ]"
  >
    <div class="flex items-center justify-between mb-2">
      <h3 class="text-xl font-bold">
        <span v-if="idea.is_pinned" class="mr-1">📌</span>
        <!-- Pin icon for pinned ideas -->
        {{ idea.title }}
      </h3>

      <!-- Status badge -->
      <span
        class="text-xs font-semibold px-2 py-1 rounded"
        :class="statusColor"
      >
        {{ statusLabel }}
      </span>
    </div>

    <!-- Idea description -->
    <p class="text-sm text-gray-600">{{ idea.description }}</p>

    <!-- Like/dislike buttons -->
    <div class="flex gap-2 mt-2">
      <button @click="$emit('like', idea.id)">👍 {{ idea.likes }}</button>
      <button @click="$emit('dislike', idea.id)">👎 {{ idea.dislikes }}</button>
    </div>
  </div>
</template>

<script setup lang="ts">
/*
  Displays a single idea card.
  Shows the title, description, pin status, current status badge,
  and like/dislike counters with interaction.
*/

import type { Idea, IdeaStatus } from "~/types/idea";

const props = defineProps<{
  idea: Idea;
}>();

// Extract status from the idea
const status = computed<IdeaStatus>(() => props.idea.status);

/*
  Map status to specific CSS color classes.
  Used to style the status badge consistently.
*/
const statusColor = computed(() => {
  switch (status.value) {
    case "pending":
      return "bg-orange-200 text-orange-800";
    case "rejected":
      return "bg-red-200 text-red-800";
    case "in_progress":
      return "bg-blue-200 text-blue-800";
    case "completed":
      return "bg-green-200 text-green-800";
    default:
      return "bg-gray-200 text-gray-800";
  }
});

/*
  Convert raw status values into readable Dutch labels for display.
*/
const statusLabel = computed(() => {
  switch (status.value) {
    case "pending":
      return "In afwachting";
    case "rejected":
      return "Afgekeurd";
    case "in_progress":
      return "In behandeling";
    case "completed":
      return "Voltooid";
    default:
      return "Onbekend";
  }
});
</script>
