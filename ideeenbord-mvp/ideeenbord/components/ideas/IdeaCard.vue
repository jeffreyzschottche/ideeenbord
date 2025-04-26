<template>
  <div class="border p-4 rounded shadow mb-4">
    <h3 class="text-xl font-bold">{{ idea.title }}</h3>
    <p class="text-sm text-gray-600">{{ idea.description }}</p>

    <div class="flex gap-2 mt-2">
      <button @click="$emit('like', idea.id)">👍 {{ idea.likes }}</button>
      <button @click="$emit('dislike', idea.id)">👎 {{ idea.dislikes }}</button>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  idea: any;
}>();
</script>
