<template>
  <div class="container mx-auto py-12 px-4 font-default">
    <h1
      class="text-3xl md:text-4xl font-bold text-center mb-8 text-[var(--color-brand)]"
    >
      Alle ideeën
    </h1>

    <!-- loader -->
    <p v-if="loading" class="text-center animate-pulse">Laden…</p>

    <!-- error -->
    <p v-else-if="error" class="text-center text-red-600">{{ error }}</p>

    <!-- leeg -->
    <p v-else-if="!ideasWithBrand.length" class="text-center">
      Nog geen ideeën.
    </p>

    <!-- grid -->
    <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
      <IdeaCardGeneral
        v-for="row in ideasWithBrand"
        :key="row.idea.id"
        :idea="row.idea"
        :brand="row.brand"
        @like="likeIdea"
        @dislike="dislikeIdea"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { apiFetch } from "~/composables/adapter/useApi";
import { useUserAuthStore } from "~/store/useUserAuthStore";
import IdeaCardGeneral from "~/components/ideas/IdeaCardGeneral.vue";
import type { Idea } from "~/types/idea";
import type { Brand } from "~/types/brand";

/* ─── state ─── */
const ideas = ref<Idea[]>([]);
const brandLookup = ref<Record<number, Brand>>({});
const loading = ref(true);
const error = ref<string | null>(null);
const auth = useUserAuthStore();

/* ─── fetch feed + brands ─── */
onMounted(async () => {
  try {
    // 1) alle ideeën
    ideas.value = await apiFetch<Idea[]>("/ideas-feed");

    // 2) één call alle brands (bevat id/slug/title/logo_path)
    const allBrands = await apiFetch<Brand[]>("/brands");
    const map: Record<number, Brand> = {};
    allBrands.forEach((b) => {
      map[b.id] = b;
    });
    brandLookup.value = map;
  } catch (e) {
    console.error(e);
    error.value = "Kon ideeën of merken niet laden.";
  } finally {
    loading.value = false;
  }
});

/* ─── samengestelde array ─── */
const ideasWithBrand = computed(() =>
  ideas.value.map((i) => ({
    idea: i,
    brand: brandLookup.value[i.brand_id] || null,
  }))
);

/* ─── like / dislike ─── */
async function likeIdea(id: number) {
  if (!auth.token) return navigateTo("/login");
  await apiFetch(`/ideas/${id}/like`, { method: "POST" });
  const it = ideas.value.find((x) => x.id === id);
  if (it) it.likes++;
}
async function dislikeIdea(id: number) {
  if (!auth.token) return navigateTo("/login");
  await apiFetch(`/ideas/${id}/dislike`, { method: "POST" });
  const it = ideas.value.find((x) => x.id === id);
  if (it) it.dislikes++;
}
</script>
