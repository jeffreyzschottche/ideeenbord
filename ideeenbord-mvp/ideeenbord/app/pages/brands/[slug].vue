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
import { apiFetch } from "~/composables/adapter/useApi";
import { useRoute } from "vue-router";
import IdeaGrid from "~/components/ideas/IdeaGrid.vue";
import { useResponseDisplay } from "~/composables/notifications/useResponseDisplay";
import BrandMainQuestion from "~/components/brand/BrandMainQuestion.vue";
import QuizParticipant from "~/components/quiz/QuizParticipant.vue";
import type { Brand } from "~/types/brand";
import { storageBaseFromApiBase } from "~/utils/apiUrl";

const rawApiBase = useRuntimeConfig().public.apiBaseUrl;
const apiBase = rawApiBase as string;
const imageBase = storageBaseFromApiBase(apiBase);

const auth = useUserAuthStore();
const route = useRoute();
const { triggerByKey } = useResponseDisplay(); // ✅ use triggerByKey for feedback messages

const brand = ref<Brand>(null);
const rating = ref(5);

// --- SEO: SSR-fetch brand meta so crawlers get real title/description ---
const { data: seoBrand } = await useAsyncData(
  `brand-seo-${route.params.slug}`,
  () =>
    $fetch<Brand>(`${apiBase}/v1/brands/${route.params.slug}`).catch(
      () => null as any
    )
);
{
  const sb: any = seoBrand.value;
  usePageSeo({
    title: sb?.title ?? "Merk",
    description:
      sb?.intro_short ||
      sb?.intro ||
      `Deel jouw ideeën en wensen met ${sb?.title ?? "dit merk"} op Ideeënbord.`,
    image: sb?.logo_path ? `${imageBase}/${sb.logo_path}` : undefined,
  });
  if (sb) {
    useJsonLd([
      {
        "@type": "Brand",
        name: sb.title,
        description: sb.intro_short || sb.intro || undefined,
      },
      breadcrumbLd([
        { name: "Home", path: "/" },
        { name: "Merken", path: "/brands" },
        { name: sb.title, path: `/brands/${sb.slug}` },
      ]),
    ]);
  }
}

// Calculate the average rating
const averageRating = computed(() => {
  if (!brand.value || brand.value.rating_count === 0) return 0;
  return (brand.value.rating_sum / brand.value.rating_count).toFixed(1);
});

// Fetch brand data on mount
onMounted(async () => {
  try {
    brand.value = await apiFetch(`/brands/${route.params.slug}`);
    if (!brand.value.accepted) {
      navigateTo("/brands");
    }
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
  <div class="page-block">
    <!-- Header / Brand Hero -->
    <div v-if="brand" class="flex flex-col gap-4">
      <div class="flex items-start gap-4">
        <img
          v-if="brand.logo_path"
          :src="`${imageBase}/${brand.logo_path}`"
          alt="Logo van merk"
          class="brand-logo"
        />
        <div class="flex-1">
          <h1 class="title-lg mb-2">{{ brand.title }}</h1>
          <p class="muted-text mb-2">{{ brand.intro }}</p>
          <div class="flex flex-wrap items-center gap-3">
            <a
              :href="brand.website_url"
              target="_blank"
              class="btn btn--blue btn--sm"
              >Website</a
            >
            <span class="text-sm text-gray-500">Email: {{ brand.email }}</span>
          </div>
        </div>
      </div>

      <!-- Rating blok -->
      <div class="card-compact">
        <div
          v-if="auth.token"
          class="flex flex-col md:flex-row md:items-center md:justify-between gap-4"
        >
          <p class="main-text">
            Fans geven dit merk gemiddeld een:
            <strong class="text-success">{{ averageRating }}</strong> / 10
          </p>

          <div
            v-if="!hasRated"
            class="flex items-center gap-3 w-full md:w-auto"
          >
            <label class="text-sm text-gray-600">Jouw rating:</label>
            <input
              type="range"
              min="1"
              max="10"
              v-model="rating"
              class="w-full md:w-64 cursor-pointer"
            />
            <span class="font-semibold w-8 text-center">{{ rating }}</span>
            <button @click="submitRating" class="btn btn--sm">
              Geef Rating
            </button>
          </div>

          <div v-else class="text-sm text-gray-600">
            Je hebt al een beoordeling gegeven.
          </div>
        </div>

        <div v-else class="text-sm text-gray-600">
          <strong>Login</strong> om een beoordeling te kunnen geven.
        </div>
      </div>
    </div>

    <div v-else class="muted-text">Merk wordt geladen...</div>

    <!-- Content secties -->
    <div
      v-if="brand"
      class="block-spacer grid grid-cols-1 lg:grid-cols-3 gap-6"
    >
      <!-- Linker kolom: Hoofdvraag + Quiz -->
      <div class="space-y-6">
        <div class="card-compact">
          <h2 class="title-md">Hoofdvraag</h2>
          <BrandMainQuestion :brand="brand" />
        </div>

        <div class="card-compact">
          <h2 class="title-md">Doe mee met de quiz</h2>
          <QuizParticipant :brand="brand" />
        </div>
      </div>

      <!-- Rechter kolom (breed): Ideeën -->
      <div class="lg:col-span-2 card-compact">
        <h2 class="title-md">Ideeën van de community</h2>
        <IdeaGrid :brandId="brand.id" />
      </div>
    </div>
  </div>

  <IdeasIdeaPopup />
</template>
