<script setup lang="ts">
import { ref, computed } from "vue";
import type { Idea } from "~/types/idea";
import type { Brand } from "~/types/brand";
import { useRuntimeConfig } from "nuxt/app";
import { useUserAuthStore } from "~/store/useUserAuthStore";
import { useIdeas } from "~/composables/ideas/useIdeas";
import { useResponseDisplay } from "~/composables/notifications/useResponseDisplay";

/* props */
const props = defineProps<{ idea: Idea; brand: Brand | null }>();

/* auth + toast helpers */
const auth = useUserAuthStore();
const { triggerByKey } = useResponseDisplay();

/* like / dislike helpers */
const { likeIdea, dislikeIdea } = useIdeas(props.idea.brand_id);
const vote = ref<"like" | "dislike" | null>(null);

/* badge helpers */
const statusColor = computed(
  () =>
    ({
      pending: "bg-orange-200 text-orange-800",
      rejected: "bg-red-200 text-red-800",
      in_progress: "bg-blue-200 text-blue-800",
      completed: "bg-green-200 text-green-800",
    }[props.idea.status] ?? "bg-gray-200 text-gray-800")
);

const statusLabel = computed(
  () =>
    ({
      pending: "In afwachting",
      rejected: "Afgewezen",
      in_progress: "In behandeling",
      completed: "Voltooid",
    }[props.idea.status] ?? "Onbekend")
);

/* logo helper */
function logoUrl(p?: string | null) {
  if (!p) return "/img/placeholder-brand.svg";
  if (p.startsWith("http") || p.startsWith("//") || p.startsWith("/img"))
    return p;
  const base = (useRuntimeConfig().public.apiBaseUrl as string).replace(
    "/api",
    "/storage"
  );
  return `${base}/${p.replace(/^\/+/, "")}`;
}

/* click-handlers */
async function onLike() {
  if (!auth.token) return triggerByKey("idea-login-required");
  if (vote.value === "like") return triggerByKey("idea-like-failed");
  vote.value = "like";
  await likeIdea(props.idea.id);
}

async function onDislike() {
  if (!auth.token) return triggerByKey("idea-login-required");
  if (vote.value === "dislike") return triggerByKey("idea-dislike-failed");
  vote.value = "dislike";
  await dislikeIdea(props.idea.id);
}
</script>

<template>
  <div
    :class="[
      'w-full max-w-[22rem] mx-auto rounded-2xl border shadow-sm p-4 flex flex-col gap-3 bg-white',
      idea.is_pinned ? 'border-yellow-400' : 'border-gray-200',
    ]"
  >
    <!-- merk -->
    <NuxtLink
      v-if="brand"
      :to="`/brands/${brand.slug}`"
      class="flex items-center gap-2 hover:underline"
    >
      <img
        :src="logoUrl(brand.logo_path)"
        :alt="brand.title"
        class="w-6 h-6 object-contain rounded bg-white"
      />
      <span class="text-sm font-semibold">{{ brand.title }}</span>
    </NuxtLink>

    <!-- status badge -->
    <span
      class="self-center text-xs font-semibold px-2 py-1 rounded"
      :class="statusColor"
    >
      {{ statusLabel }}
    </span>

    <!-- titel / beschrijving -->
    <h3 class="text-lg font-bold text-center">
      <span v-if="idea.is_pinned" class="mr-1">📌</span>{{ idea.title }}
    </h3>
    <p class="text-sm text-gray-700 text-center line-clamp-4">
      {{ idea.description }}
    </p>

    <!-- CTA -->
    <NuxtLink
      v-if="brand"
      :to="`/brands/${brand.slug}?idea=${idea.id}`"
      class="cta w-max mx-auto mt-auto mb-3 px-4 py-2 rounded text-white bg-[var(--color-brand)] hover:opacity-90"
    >
      Bekijk idee
    </NuxtLink>
  </div>
</template>
