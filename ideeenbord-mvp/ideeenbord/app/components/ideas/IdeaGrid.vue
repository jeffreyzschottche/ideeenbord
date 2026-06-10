<script setup lang="ts">
/*
  Toont alle ideeën voor een merk + like/dislike.
  Ingelogde gebruikers krijgen een knop die de modal opent.
*/

import { ref, onMounted, computed } from "vue";
import IdeaCard from "~/components/ideas/IdeaCard.vue";
import IdeaSubmitBrandModal from "~/components/ideas/IdeaSubmitBrandModal.vue";
import { useIdeas } from "~/composables/ideas/useIdeas";
import { useUserAuthStore } from "~/store/useUserAuthStore";
import type { Idea } from "~/types/idea";

/* ─ props ─ */
const props = defineProps<{ brandId: number }>();

/* ─ auth & modal ─ */
const auth = useUserAuthStore();
const showModal = ref(false);

/* ─ ideas composable ─ */
const { ideas, fetchIdeas, likeIdea, dislikeIdea } = useIdeas(props.brandId);

/* ─ sortering ─ */
const sortedIdeas = computed<readonly Idea[]>(() =>
  [...ideas.value].sort((a, b) => {
    if (a.is_pinned && !b.is_pinned) return -1;
    if (!a.is_pinned && b.is_pinned) return 1;
    return new Date(b.created_at).getTime() - new Date(a.created_at).getTime();
  })
);

/* ─ lifecycle ─ */
onMounted(fetchIdeas);
</script>

<template>
  <div>
    <!-- CTA-knop voor ingelogden -->
    <div class="mb-6" v-if="auth.token">
      <button class="cta px-5 py-2.5" @click="showModal = true">
        <i class="fa-solid fa-plus mr-1"></i> Plaats idee
      </button>
    </div>

    <div v-else class="mb-6 text-sm text-gray-500">
      <NuxtLink to="/login" class="font-semibold text-[var(--color-brand)] hover:underline">Log in</NuxtLink>
      om je idee te plaatsen.
    </div>

    <!-- Grid met IdeaCards (max 2 koloms; IdeaCard is zelf de kaart) -->
    <div
      v-if="sortedIdeas.length"
      class="grid gap-4 md:gap-5 grid-cols-1 sm:grid-cols-2"
    >
      <IdeaCard
        v-for="idea in sortedIdeas"
        :key="idea.id"
        :idea="idea"
        @like="likeIdea"
        @dislike="dislikeIdea"
      />
    </div>
    <div
      v-else
      class="text-center text-gray-400 py-12 border border-dashed border-gray-200 rounded-2xl"
    >
      <p class="text-4xl mb-2">💡</p>
      <p>Nog geen ideeën. Wees de eerste!</p>
    </div>

    <!-- Modal component -->
    <IdeaSubmitBrandModal
      :open="showModal"
      :brandId="props.brandId"
      @close="showModal = false"
    />
  </div>
</template>
