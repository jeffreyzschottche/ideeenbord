<script setup lang="ts">
/*
  This page displays the public brand profile based on the slug in the route.
  It includes brand information, average rating, and interactive components like:
  - Rating submission (if logged in)
  - IdeaGrid (list of submitted ideas)
  - BrandMainQuestion (main question for feedback)
  - QuizParticipant (join brand-specific quizzes)
*/

import { ref, computed, onMounted } from "vue";
import { useUserAuthStore } from "~/store/useUserAuthStore";
import { apiFetch } from "~/composables/useApi";
import { useRoute } from "vue-router";
import IdeaGrid from "~/components/ideas/IdeaGrid.vue";
import { useResponseDisplay } from "~/composables/useResponseDisplay";
import BrandMainQuestion from "~/components/brand/BrandMainQuestion.vue";
import QuizParticipant from "~/components/quiz/QuizParticipant.vue";
import type { Brand } from "~/types/brand";

const rawApiBase = useRuntimeConfig().public.apiBaseUrl;
const apiBase = rawApiBase as string;
const imageBase = apiBase.replace("/api", "/storage");

const auth = useUserAuthStore();
const route = useRoute();
const { triggerByKey } = useResponseDisplay(); // ✅ use triggerByKey for feedback messages

const brand = ref<Brand>(null);
const rating = ref(5);

// Calculate the average rating
const averageRating = computed(() => {
  if (!brand.value || brand.value.rating_count === 0) return 0;
  return (brand.value.rating_sum / brand.value.rating_count).toFixed(1);
});

// Fetch brand data on mount
onMounted(async () => {
  try {
    brand.value = await apiFetch(`/brands/${route.params.slug}`);
  } catch (err: any) {
    triggerByKey("brand-load-failed");
  }
});

// Check if current user has already rated this brand
const hasRated = computed(() => {
  if (!brand.value || !auth.user) return false;
  return auth.user.ratings_given?.includes(brand.value.id);
});

// Submit new rating for the brand
async function submitRating() {
  if (!brand.value) return;

  if (hasRated.value) {
    triggerByKey("brand-already-rated");
    return;
  }

  try {
    await apiFetch(`/brands/${brand.value.id}/rate`, {
      method: "POST",
      body: { rating: rating.value },
    });
    if (!auth.user.ratings_given) {
      auth.user.ratings_given = [];
    }
    auth.user.ratings_given.push(brand.value.id);
    triggerByKey("brand-rating-saved");
  } catch (err: any) {
    triggerByKey("brand-rating-failed");
  }
}
</script>

<template>
  <div v-if="brand">
    <h1>{{ brand.title }}</h1>
    <img
      v-if="brand.logo_path"
      :src="`${imageBase}/${brand.logo_path}`"
      alt="Logo van merk"
      class="w-48 h-auto mb-4 rounded"
    />

    <p>{{ brand.intro }}</p>
    <p>Email: {{ brand.email }}</p>
    <a :href="brand.website_url" target="_blank">Website</a>

    <div v-if="auth.token">
      <p>
        Fans geven dit merk gemiddeld een:
        <strong>{{ averageRating }}</strong> / 10
      </p>
      <div v-if="!hasRated">
        <h3>Wat vind jij van {{ brand.title }}?</h3>
        <input type="range" min="1" max="10" v-model="rating" />
        <span>{{ rating }}</span>
        <button @click="submitRating">Geef Rating</button>
      </div>
      <div v-else>
        <p>Je hebt al een beoordeling gegeven.</p>
      </div>
    </div>
    <div v-else>
      <p><strong>Login</strong> om een beoordeling te kunnen geven.</p>
    </div>
  </div>
  <div v-else>
    <p>Merk wordt geladen...</p>
  </div>
  <IdeaGrid v-if="brand" :brandId="brand.id" />
  <BrandMainQuestion v-if="brand" :brand="brand" />
  <QuizParticipant v-if="brand" :brand="brand" />
</template>
