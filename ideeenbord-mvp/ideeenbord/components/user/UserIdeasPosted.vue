<script setup lang="ts">
import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import { apiFetch } from "~/composables/useApi";

const route = useRoute();
const username = route.params.slug as string;

const ideas = ref<any[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);

onMounted(async () => {
  try {
    const ideaData: any = await apiFetch(`/users/${username}/ideas`);
    ideas.value = ideaData;
  } catch (e: any) {
    error.value = e.message || "Fout bij laden van ideeën";
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <div class="mt-8">
    <h2 class="text-xl font-bold mb-2">💡 Ideeën die je hebt gepost</h2>

    <p v-if="loading">Laden...</p>
    <p v-if="error">{{ error }}</p>

    <ul v-if="ideas.length">
      <li v-for="idea in ideas" :key="idea.id" class="mb-2">
        <strong>{{ idea.title }}</strong>
        <br />
        <i> Status : {{ idea.status }}</i>
        <br />
        <span class="text-sm text-gray-600">{{ idea.description }}</span>
      </li>
    </ul>

    <p v-else-if="!loading">Je hebt nog geen ideeën gepost.</p>
  </div>
</template>
