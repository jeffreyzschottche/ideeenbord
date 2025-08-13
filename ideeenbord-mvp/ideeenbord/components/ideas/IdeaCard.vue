<script setup lang="ts">
/*
  Displays a single idea card.
  Shows the title, description, pin status, current status badge,
  and like/dislike counters with interaction.
*/
import { computed } from "vue";
import { useRoute } from "vue-router";
import type { Idea, IdeaStatus } from "~/types/idea";
import { ideaService } from "~/services/api/ideas/ideaService";
import { useResponseDisplay } from "~/composables/notifications/useResponseDisplay";
import { useUserAuthStore } from "~/store/useUserAuthStore";

const { triggerByKey } = useResponseDisplay();
const auth = useUserAuthStore();
const route = useRoute();

const props = defineProps<{ idea: Idea }>();

async function onReport() {
  if (!confirm("Rapporteer dit idee als ongepast?")) return;
  try {
    await ideaService.reportIdea(props.idea.id);
    triggerByKey("idea-reported");
  } catch {
    triggerByKey("idea-report-failed");
  }
}

// Status helpers
const status = computed<IdeaStatus>(() => props.idea.status);
const statusColor = computed(() => {
  switch (status.value) {
    case "pending":
      return "bg-orange-200 text-orange-800";
    case "rejected":
      return "bg-red-200 text-red-800";
    case "in_progress":
      return "bg-blue-200 text-blue-800";
    case "completed":
      return "bg-green-200 text-green-800";
    default:
      return "bg-gray-200 text-gray-800";
  }
});
const statusLabel = computed(() => {
  switch (status.value) {
    case "pending":
      return "In afwachting";
    case "rejected":
      return "Afgekeurd";
    case "in_progress":
      return "In behandeling";
    case "completed":
      return "Voltooid";
    default:
      return "Onbekend";
  }
});

// Pak brand-slug uit URL
const brandSlugFromRoute = computed(() => {
  const fromParam = route.params?.slug as string | undefined;
  if (fromParam) return fromParam;
  const path = String(route.path || "");
  const m = path.match(/\/brands\/([^/?#]+)/);
  return m ? m[1] : undefined;
});

// Bouw deel-URL (relatief) en absolute URL voor clipboard
const sharePath = computed(() =>
  brandSlugFromRoute.value
    ? `/brands/${brandSlugFromRoute.value}?idea-id=${props.idea.id}`
    : `/?idea-id=${props.idea.id}`
);

function copyShareUrl() {
  const origin =
    typeof window !== "undefined" && window.location?.origin
      ? window.location.origin
      : "";
  const url = origin + sharePath.value;

  // Voorkeur: moderne clipboard API
  if (typeof navigator !== "undefined" && navigator.clipboard?.writeText) {
    navigator.clipboard.writeText(url).then(
      () => triggerByKey("link-copied"),
      () => fallbackCopy(url)
    );
    return;
  }
  // Fallback voor oudere browsers/contexts
  fallbackCopy(url);
}

function fallbackCopy(text: string) {
  try {
    if (typeof document === "undefined") return;
    const ta = document.createElement("textarea");
    ta.value = text;
    ta.setAttribute("readonly", "");
    ta.style.position = "fixed";
    ta.style.top = "-1000px";
    document.body.appendChild(ta);
    ta.select();
    document.execCommand("copy");
    document.body.removeChild(ta);
    triggerByKey("link-copied");
  } catch {
    // geen harde error gooien; desnoods kun je hier een andere melding doen
  }
}
</script>

<template>
  <div
    :class="[
      'border p-4 rounded shadow mb-4 relative',
      idea.is_pinned ? 'border-yellow-400' : 'border-gray-300',
    ]"
  >
    <div class="flex items-center justify-between mb-2">
      <h3 class="text-xl font-bold">
        <span v-if="idea.is_pinned" class="mr-1">📌</span>
        {{ idea.title }}
      </h3>

      <span class="text-grey"
        ><i>@{{ idea.user?.username || "anoniem" }}</i></span
      >

      <span
        class="text-xs font-semibold px-2 py-1 rounded"
        :class="statusColor"
      >
        {{ statusLabel }}
      </span>

      <div class="flex items-center gap-2">
        <button
          v-if="auth.token"
          class="text-red-600 text-sm hover:underline"
          @click="onReport"
        >
          Rapporteer
        </button>
      </div>
    </div>

    <p class="text-sm text-gray-600">{{ idea.description }}</p>

    <!-- Like/dislike -->
    <div class="flex gap-2 mt-2">
      <button @click="$emit('like', idea.id)">👍 {{ idea.likes }}</button>
      <button @click="$emit('dislike', idea.id)">👎 {{ idea.dislikes }}</button>
    </div>

    <!-- Deel (kopieert URL) -->
    <div class="flex gap-3 mt-3">
      <button class="btn-link text-sm" @click="copyShareUrl">Deel</button>
    </div>
  </div>
</template>
