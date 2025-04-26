<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { useAuthStore } from "~/store/auth";
import { apiFetch } from "~/composables/useApi";
import { useRoute } from "vue-router";
import IdeaGrid from "~/components/ideas/IdeaGrid.vue";

const auth = useAuthStore();
const route = useRoute();

const averageRating = computed(() => {
  if (!brand.value || brand.value.rating_count === 0) return 0;
  return (brand.value.rating_sum / brand.value.rating_count).toFixed(1); // 1 decimaal
});

const brand = ref<any>(null);
const rating = ref(5); // Default slider positie

onMounted(async () => {
  try {
    brand.value = await apiFetch(`/brands/${route.params.slug}`);
  } catch (err) {
    console.error("Fout bij ophalen merk:", err);
  }
});

// Alleen checken op id als brand geladen is
const hasRated = computed(() => {
  if (!brand.value || !auth.user) return false;
  return auth.user.ratings_given?.includes(brand.value.id);
});

async function submitRating() {
  if (!brand.value) return;

  if (hasRated.value) {
    alert("Je hebt al een beoordeling gegeven!");
    return;
  }

  try {
    const response = await apiFetch(`/brands/${brand.value.id}/rate`, {
      method: "POST",
      body: { rating: rating.value },
    });
    // alert(response.message);

    // Update lokale state zodat je niet opnieuw kunt stemmen
    auth.user.ratings_given.push(brand.value.id);
  } catch (err: any) {
    alert(err?.message || "Rating mislukt");
  }
}
</script>

<template>
  <div v-if="brand">
    <h1>{{ brand.title }}</h1>
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
</template>
