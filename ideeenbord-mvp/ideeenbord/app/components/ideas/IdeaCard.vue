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

// Bouw deel-URL
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

  if (typeof navigator !== "undefined" && navigator.clipboard?.writeText) {
    navigator.clipboard.writeText(url).then(
      () => triggerByKey("link-copied"),
      () => fallbackCopy(url)
    );
    return;
  }
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
  } catch {}
}
</script>

<template>
  <!-- Sterke container zodat niks ‘lekt’ buiten de kaart -->
  <div
    :class="[
      'relative isolate overflow-hidden rounded-2xl border bg-white p-5 shadow-[0_6px_20px_rgba(31,41,55,0.05)] h-full flex flex-col transition-shadow hover:shadow-lg',
      idea.is_pinned ? 'border-[var(--color-brand)]' : 'border-gray-100',
    ]"
  >
    <!-- Header -->
    <div class="flex items-start justify-between gap-3 mb-2">
      <h3 class="text-lg font-bold leading-snug text-[var(--color-nav)]">
        <span v-if="idea.is_pinned" class="mr-1">📌</span>{{ idea.title }}
      </h3>
      <span
        class="text-xs font-semibold px-2.5 py-1 rounded-full whitespace-nowrap shrink-0"
        :class="statusColor"
      >
        {{ statusLabel }}
      </span>
    </div>

    <span class="text-sm text-gray-400 mb-3">@{{ idea.user?.username || "anoniem" }}</span>

    <!-- Body -->
    <p class="text-sm text-gray-600 mb-4 flex-1">{{ idea.description }}</p>

    <!-- Actions -->
    <div class="flex items-center justify-between gap-3 pt-3 border-t border-gray-100">
      <div class="flex items-center gap-2">
        <button
          class="inline-flex items-center gap-1.5 text-sm px-3 py-1.5 rounded-full border border-gray-200 hover:border-green-400 hover:text-green-600 transition"
          @click="$emit('like', idea.id)"
        >
          <i class="fa-solid fa-thumbs-up"></i><span>{{ idea.likes }}</span>
        </button>
        <button
          class="inline-flex items-center gap-1.5 text-sm px-3 py-1.5 rounded-full border border-gray-200 hover:border-red-400 hover:text-red-500 transition"
          @click="$emit('dislike', idea.id)"
        >
          <i class="fa-solid fa-thumbs-down"></i><span>{{ idea.dislikes }}</span>
        </button>
      </div>

      <div class="flex items-center gap-3">
        <button class="text-sm text-gray-500 hover:text-[var(--color-brand)]" @click="copyShareUrl">
          <i class="fa-solid fa-share-nodes mr-1"></i>Deel
        </button>
        <button
          v-if="auth.token"
          class="text-sm text-gray-400 hover:text-red-600"
          @click="onReport"
          title="Rapporteer"
        >
          <i class="fa-solid fa-flag"></i>
        </button>
      </div>
    </div>
  </div>
</template>
