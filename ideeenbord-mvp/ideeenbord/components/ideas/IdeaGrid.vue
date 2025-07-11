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
      <button class="cta" @click="showModal = true">Plaats idee</button>
    </div>

    <div v-else class="mb-6 text-sm text-gray-600">
      Login om je idee te plaatsen!
    </div>

    <!-- Lijst met cards -->
    <div v-for="idea in sortedIdeas" :key="idea.id">
      <IdeaCard :idea="idea" @like="likeIdea" @dislike="dislikeIdea" />
    </div>

    <!-- Modal component -->
    <IdeaSubmitBrandModal
      :open="showModal"
      :brandId="props.brandId"
      @close="showModal = false"
    />
  </div>
</template>
