<script setup lang="ts">
/*
  Shows an overview of ideas the user has liked or disliked.
  Fetches idea details based on the user's liked_posts and disliked_posts IDs.
*/

import { ref, onMounted } from "vue";
import { useUserAuthStore } from "~/store/useUserAuthStore";
import { apiFetch } from "~/composables/useApi";

// Auth store and state
const auth = useUserAuthStore();
const likedIdeas = ref<any[]>([]);
const dislikedIdeas = ref<any[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);

// Fetch liked and disliked ideas on mount
onMounted(async () => {
  try {
    if (!auth.user) {
      await auth.initAuth();
    }

    const likedIds = auth.user?.liked_posts || [];
    const dislikedIds = auth.user?.disliked_posts || [];

    if (likedIds.length > 0) {
      likedIdeas.value = await apiFetch(`/ideas`, {
        method: "GET",
        params: { ids: likedIds.join(",") },
      });
    }

    if (dislikedIds.length > 0) {
      dislikedIdeas.value = await apiFetch(`/ideas`, {
        method: "GET",
        params: { ids: dislikedIds.join(",") },
      });
    }
  } catch (err: any) {
    error.value = err?.message || "Fout bij laden van ideeën";
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <div>
    <h2>🟢 Liked Ideas</h2>
    <ul v-if="likedIdeas.length">
      <li v-for="idea in likedIdeas" :key="idea.id">
        ✅ <strong>{{ idea.title }}</strong> (status: {{ idea.status }})
        <span v-if="idea.brand">
          – bij
          <NuxtLink :to="`/brands/${idea.brand.title}`">
            {{ idea.brand.title }}
          </NuxtLink>
        </span>
      </li>
    </ul>
    <p v-else>Nog geen ideeën geliked.</p>

    <h2>🔴 Disliked Ideas</h2>
    <ul v-if="dislikedIdeas.length">
      <li v-for="idea in dislikedIdeas" :key="idea.id">
        🚫 <strong>{{ idea.title }}</strong> (status: {{ idea.status }})
        <span v-if="idea.brand">
          – bij
          <NuxtLink :to="`/brands/${idea.brand.title}`">
            {{ idea.brand.title }}
          </NuxtLink>
        </span>
      </li>
    </ul>
    <p v-else>Nog geen ideeën gedisliked.</p>

    <p v-if="loading">Laden...</p>
    <p v-if="error">{{ error }}</p>
  </div>
</template>
