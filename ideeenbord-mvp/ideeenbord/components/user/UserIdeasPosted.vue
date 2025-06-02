<script setup lang="ts">
import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import { apiFetch } from "~/composables/useApi";
import { useResponseDisplay } from "~/composables/useResponseDisplay";

const route = useRoute();
const username = route.params.slug as string;
const { triggerByKey } = useResponseDisplay();

const ideas = ref<any[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);

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
</script>

<template>
  <div class="mt-8">
    <h2 class="text-xl font-bold mb-2">💡 Ideeën die je hebt gepost</h2>

    <p v-if="loading">Laden...</p>
    <p v-if="error">{{ error }}</p>

    <ul v-if="ideas.length">
      <li v-for="idea in ideas" :key="idea.id" class="mb-2">
        <NuxtLink
          :to="`/brands/${idea.brand?.slug}`"
          class="font-bold text-lg text-blue-800 hover:underline"
        >
          {{ idea.title }}
        </NuxtLink>
        <br />
        <i> Status : {{ idea.status }}</i>
        <i> Likes : {{ idea.likes }}</i>
        <i> Dislikes : {{ idea.dislikes }}</i>
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
      </li>
    </ul>

    <p v-else-if="!loading">Je hebt nog geen ideeën gepost.</p>
  </div>
</template>
