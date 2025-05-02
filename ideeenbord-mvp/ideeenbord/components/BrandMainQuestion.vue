<template>
  <div v-if="parsed" class="bg-gray-100 p-4 rounded shadow mb-6">
    <h2 class="text-lg font-semibold mb-2">
      Wat vind jij van {{ brand.title }}?
    </h2>
    <p class="text-gray-800 mb-4">{{ parsed.text }}</p>

    <div v-if="parsed.answers?.length" class="flex flex-wrap gap-2">
      <button
        v-for="(answer, index) in parsed.answers"
        :key="index"
        class="px-4 py-2 bg-blue-100 text-blue-800 rounded hover:bg-blue-200"
      >
        {{ answer }}
      </button>
    </div>

    <div v-else>
      <textarea
        rows="3"
        class="w-full border p-2 rounded mt-2"
        placeholder="Typ hier je antwoord..."
      ></textarea>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from "vue";

const props = defineProps<{
  brand: {
    title: string;
    category: string;
    main_question: string | null;
  };
}>();

const parsed = computed(() => {
  if (!props.brand.main_question) return null;

  let q: any = props.brand.main_question;

  if (typeof q === "string") {
    try {
      q = JSON.parse(q);
    } catch (e) {
      console.warn("Kon main_question niet parsen als JSON:", q);
      return null;
    }
  }

  if (!q?.text) return null;

  return {
    text: q.text
      .replace(/\[merknaam\]/gi, props.brand.title)
      .replace(/\[categorie\]/gi, props.brand.category),
    answers: q.answers,
  };
});
</script>
