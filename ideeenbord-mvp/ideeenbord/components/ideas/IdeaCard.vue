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
        <!-- Prik icoon -->
        {{ idea.title }}
      </h3>

      <span
        class="text-xs font-semibold px-2 py-1 rounded"
        :class="statusColor"
      >
        {{ statusLabel }}
      </span>
    </div>

    <p class="text-sm text-gray-600">{{ idea.description }}</p>

    <div class="flex gap-2 mt-2">
      <button @click="$emit('like', idea.id)">👍 {{ idea.likes }}</button>
      <button @click="$emit('dislike', idea.id)">👎 {{ idea.dislikes }}</button>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Idea, IdeaStatus } from "~/types/idea";
const props = defineProps<{
  idea: Idea;
}>();

const status = computed<IdeaStatus>(() => props.idea.status);

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
