<script setup lang="ts">
import { ref, watch, onMounted, onBeforeUnmount } from "vue";
import { useRoute, useRouter } from "vue-router";
import { apiFetch } from "~/composables/adapter/useApi";
import IdeaCard from "~/components/ideas/IdeaCard.vue";
import type { Idea } from "~/types/idea";

const route = useRoute();
const router = useRouter();

const show = ref(false);
const loading = ref(false);
const idea = ref<Idea | null>(null);
const error = ref<string | null>(null);

function getIdeaIdFromQuery(q: Record<string, any>) {
  const raw = (q["idea-id"] ?? q.ideaId ?? null) as string | string[] | null;
  if (!raw) return null;
  return Array.isArray(raw) ? raw[0] : raw;
}

async function loadIdeaByQuery(q: Record<string, any>) {
  const id = getIdeaIdFromQuery(q);
  if (!id) return;

  show.value = true;
  lockScroll();
  loading.value = true;
  error.value = null;
  idea.value = null;

  try {
    idea.value = await apiFetch(`/ideas/${id}`);
  } catch {
    error.value = "Idee niet gevonden of kon niet geladen worden.";
  } finally {
    loading.value = false;
  }
}

function close() {
  show.value = false;
  idea.value = null;
  error.value = null;
  unlockScroll();
  const newQuery = { ...route.query };
  delete (newQuery as any)["idea-id"];
  delete (newQuery as any).ideaId;
  router.replace({ query: newQuery });
}

function onKeydown(e: KeyboardEvent) {
  if (e.key === "Escape" && show.value) close();
}

function lockScroll() {
  if (typeof document === "undefined") return;
  document.documentElement.classList.add("overflow-hidden");
}
function unlockScroll() {
  if (typeof document === "undefined") return;
  document.documentElement.classList.remove("overflow-hidden");
}

onMounted(() => {
  if (typeof window !== "undefined")
    window.addEventListener("keydown", onKeydown);
});

onBeforeUnmount(() => {
  if (typeof window !== "undefined")
    window.removeEventListener("keydown", onKeydown);
  unlockScroll();
});

watch(
  () => route.query,
  (q) => loadIdeaByQuery(q as Record<string, any>),
  { deep: true, immediate: true }
);
</script>

<template>
  <Teleport to="body">
    <transition name="fade">
      <div
        v-if="show"
        class="fixed inset-0 z-[1000] flex items-end sm:items-center justify-center"
        aria-modal="true"
        role="dialog"
        aria-labelledby="idea-modal-title"
      >
        <!-- Backdrop met zachte blur -->
        <div
          class="absolute inset-0 bg-black/50 backdrop-blur-[2px]"
          @click="close"
        />

        <!-- Modal: sheet op mobiel, card op desktop -->
        <div
          class="relative z-[1001] w-full sm:w-[min(900px,92vw)] bg-white shadow-2xl border border-gray-200 sm:rounded-3xl overflow-hidden sm:max-h-[90vh]"
          @click.stop
        >
          <!-- Header -->
          <div
            class="sticky top-0 flex items-center justify-between gap-3 px-4 py-3 sm:px-6 sm:py-4 bg-gradient-to-b from-white to-gray-50 border-b border-gray-200"
          >
            <div class="flex items-center gap-3">
              <div class="h-8 w-1.5 rounded-full bg-brand" />
              <h2
                id="idea-modal-title"
                class="text-lg sm:text-xl font-semibold main-text"
              >
                Idee
              </h2>
            </div>
            <button
              class="h-9 w-9 inline-flex items-center justify-center rounded-xl border border-gray-200 hover:bg-gray-100 active:translate-y-px transition"
              aria-label="Sluiten"
              @click="close"
            >
              ✕
            </button>
          </div>

          <!-- Body -->
          <div class="p-4 sm:p-6 overflow-y-auto sm:max-h-[calc(90vh-64px)]">
            <!-- Loading -->
            <div v-if="loading" class="py-14 text-center text-gray-500">
              <div
                class="mx-auto mb-3 h-8 w-8 animate-spin rounded-full border-2 border-gray-300"
                :style="{ borderTopColor: 'var(--color-brand)' }"
              />
              Laden…
            </div>

            <!-- Error -->
            <div v-else-if="error" class="py-12 text-center">
              <p class="text-error font-medium mb-4">{{ error }}</p>
              <button class="btn btn--sm" @click="close">Terug</button>
            </div>

            <!-- Content -->
            <div v-else-if="idea">
              <IdeaCard :idea="idea" />
            </div>
          </div>
        </div>
      </div>
    </transition>
  </Teleport>
</template>

<style scoped>
/* Backdrop fade */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.18s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
