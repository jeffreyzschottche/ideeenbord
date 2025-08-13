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
  const val = Array.isArray(raw) ? raw[0] : raw;
  return val;
}

async function loadIdeaByQuery(q: Record<string, any>) {
  const id = getIdeaIdFromQuery(q);
  if (!id) return;

  // ✅ Open de modal meteen
  show.value = true;
  lockScroll();
  loading.value = true;
  error.value = null;
  idea.value = null;

  try {
    // optioneel: parse naar nummer: const numericId = Number(id);
    // fetch
    idea.value = await apiFetch(`/ideas/${id}`);
  } catch (e) {
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

// Zorg dat we altijd netjes opruimen
onBeforeUnmount(() => {
  if (typeof window !== "undefined")
    window.removeEventListener("keydown", onKeydown);
  unlockScroll();
});

// ✅ Watch de query *immediate* zodat een reload met ?idea-id= meteen opent
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
        class="fixed inset-0 z-[1000] flex items-center justify-center"
        aria-modal="true"
        role="dialog"
      >
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/60" @click="close" />

        <!-- Modal -->
        <div
          class="relative z-[1001] max-h-[90vh] w-[min(900px,95vw)] overflow-y-auto rounded-2xl bg-white p-4 shadow-xl"
          @click.stop
        >
          <div class="flex items-center justify-between mb-3">
            <h2 class="text-xl font-semibold">Idee</h2>
            <button
              class="rounded px-3 py-1 text-sm hover:bg-gray-100"
              aria-label="Sluiten"
              @click="close"
            >
              ✕
            </button>
          </div>

          <div v-if="loading" class="py-10 text-center text-gray-500">
            Laden…
          </div>
          <div v-else-if="error" class="py-10 text-center text-red-600">
            {{ error }}
          </div>
          <IdeaCard v-else-if="idea" :idea="idea" />
        </div>
      </div>
    </transition>
  </Teleport>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
